<?php

namespace App\Services;

use App\Enums\StockMovementReason;
use App\Models\CustomizationRate;
use App\Models\Order;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Texture;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Every stock movement an order causes, in one place.
 *
 * Product stock moves at checkout (down) and at cancellation (up). Raw
 * materials and textures move in two steps, because approving an order and
 * making the thing are different events:
 *
 *   - **Approval reserves.** The material comes off the shelf so a second
 *     order can't be promised the same stock, but nothing has been made, so
 *     the report's Consumed column stays put.
 *   - **Production consumes.** Staff moving the order to processing turns each
 *     reservation into consumption. Stock doesn't move again — it already
 *     left — but `units_consumed` and the materials report finally say it was
 *     used.
 *   - **Cancellation reverses** whichever of the two the order reached.
 *
 * What an order needs is two bills of materials, not one. The product's own
 * BOM covers the blank item; `customization_rate_materials` covers what the
 * customer added to it in the studio, and the finish's `raw_material_id`
 * covers what it was coloured or printed with. Without the second, a design
 * with twelve lines of text and internal lighting was charged for ink and an
 * LED strip that no order ever deducted.
 *
 * Quantities are aggregated per material and per texture before being applied:
 * two lines of different products can draw on the same material, and the
 * shortage check is only meaningful against the combined figure.
 *
 * Raw materials go through RawMaterialStockService so an approval lands in the
 * same ledger — and the same counters — as usage recorded by hand. Textures
 * have no ledger of their own yet and still move directly.
 */
class OrderStockService
{
    public function __construct(private RawMaterialStockService $materialStock)
    {
    }

    /**
     * What this order draws on, aggregated.
     *
     * The bills of materials can only estimate what a design costs in ink: a
     * fixed split has no idea whether the artwork is a thin red outline or a
     * dense photograph. So an admin reviewing an order can correct the figures
     * against the design in front of them, and `$overrides` is what they set —
     * `raw_material_id => quantity`, replacing the calculated figure.
     *
     * Only materials the order already reaches can be overridden. An override
     * naming anything else is ignored rather than honoured, so a crafted form
     * post can't invent a draw against a material this order has nothing to do
     * with. Zero is a valid answer — "this artwork uses no cyan at all" — and
     * drops the line entirely.
     *
     * @param  array<int|string, mixed>  $overrides
     * @return array{materials: array<int, array{model: RawMaterial, quantity: float, adjusted: bool}>, textures: array<int, array{model: Texture, quantity: float}>}
     */
    public function requirements(Order $order, array $overrides = []): array
    {
        $order->loadMissing(['orderItems.product.rawMaterials', 'orderItems.customDesign']);

        // Accumulate against material ids first and resolve the models in one
        // query at the end — the same material can be reached three different
        // ways (product BOM, a customization option, the finish) and looking it
        // up on each route would be a query per route.
        $quantities = [];
        $textures = [];

        $add = function (?int $materialId, float $quantity) use (&$quantities) {
            if ($materialId === null || $quantity <= 0) {
                return;
            }

            $quantities[$materialId] = ($quantities[$materialId] ?? 0) + $quantity;
        };

        foreach ($order->orderItems as $item) {
            if (! $item->product) {
                continue;
            }

            $quantity = (int) $item->quantity;
            $design = $item->customDesign;

            // 1. The blank item's own bill of materials.
            //
            //    Lines flagged requires_design are the consumables that
            //    decorate it rather than part of what it is, so a plain order
            //    skips them: the shop hands over an undecorated mug and no
            //    transfer paper or ink is spent on a print nobody asked for.
            //    The blank itself still leaves product stock either way,
            //    because the blank *is* the product.
            foreach ($item->product->rawMaterials as $material) {
                if ($material->pivot->requires_design && ! $design) {
                    continue;
                }

                $add($material->raw_material_id, (float) $material->pivot->quantity_required * $quantity);
            }

            if (! $design) {
                continue;
            }

            // 2. What the customer added in the studio. customizationUnits() is
            //    the same tally the price breakdown charges for, so the material
            //    draw and the fee can't end up describing different designs.
            foreach ($design->customizationUnits() as $rateKey => $units) {
                foreach (CustomizationRate::materialsFor($rateKey) as $materialId => $perUnit) {
                    $add($materialId, $perUnit * $units * $quantity);
                }
            }

            // 3. The finish. Either/or, and the texture wins if a hand-edited
            //    recipe names both — the same rule CustomDesign::finishLine()
            //    prices by, so an item can't be charged for one finish and
            //    costed against the other.
            $texture = $design->texture();
            if ($texture) {
                $id = $texture->texture_id;
                $textures[$id]['model'] ??= $texture;
                $textures[$id]['quantity'] = ($textures[$id]['quantity'] ?? 0) + $quantity;

                $add($texture->raw_material_id, (float) $texture->material_quantity * $quantity);

                continue;
            }

            if ($color = $design->color()) {
                $add($color->raw_material_id, (float) $color->material_quantity * $quantity);
            }
        }

        return [
            'materials' => $this->applyOverrides($this->resolveMaterials($quantities), $overrides),
            'textures' => $textures,
        ];
    }

    /**
     * Anything this order needs more of than the shop holds, described for a
     * human. An empty array means approval is safe.
     *
     * Takes the same overrides as requirements(), because the check has to run
     * against the figures actually being reserved: an admin raising a line has
     * to be told it no longer fits, and one lowering a line should not be
     * blocked by an estimate they have already corrected.
     *
     * @param  array<int|string, mixed>  $overrides
     * @return array<int, string>
     */
    public function shortages(Order $order, array $overrides = []): array
    {
        $requirements = $this->requirements($order, $overrides);
        $shortages = [];

        foreach ($requirements['materials'] as $entry) {
            $available = (float) $entry['model']->stock_quantity;
            if ($entry['quantity'] > $available) {
                $shortages[] = sprintf(
                    '%s (needs %s %s, %s in stock)',
                    $entry['model']->name,
                    $this->number($entry['quantity']),
                    $entry['model']->unit,
                    $this->number($available)
                );
            }
        }

        foreach ($requirements['textures'] as $entry) {
            $available = (float) $entry['model']->stock_quantity;
            if ($entry['quantity'] > $available) {
                $shortages[] = sprintf(
                    '%s (needs %s, %s in stock)',
                    $entry['model']->name,
                    $this->number($entry['quantity']),
                    $this->number($available)
                );
            }
        }

        return $shortages;
    }

    /**
     * What this order's next stock step will move, described for a screen.
     *
     * Deliberately not just `requirements()`. Which question a screen is
     * asking depends on how far the order has got, and the two answers can
     * differ:
     *
     *   - Before approval, the honest answer is a *forecast* — what the bills
     *     of materials say the order will take. Nothing is committed yet.
     *   - After approval, it is a *fact* — the quantities actually reserved,
     *     read back off the ledger. A BOM edited in between would make a fresh
     *     calculation disagree with what the job at the bench will consume,
     *     and the bench is right.
     *
     * @return array{stage: string, note: string, lines: array<int, array<string, mixed>>, shortages: array<int, string>}
     */
    public function plannedDraw(Order $order): array
    {
        // Nothing has been committed at these two statuses, so there is a
        // forecast to give and a shortage worth warning about.
        if (in_array($order->status, ['pending', 'awaiting_pr'], true)) {
            $requirements = $this->requirements($order);

            $lines = [];
            foreach ($requirements['materials'] as $id => $entry) {
                // Only raw materials are correctable. A texture is one sheet per
                // item — a count, not a judgement about coverage — so there is
                // nothing for a reviewer to weigh up.
                $lines[] = $this->line($entry['model']->name, $entry['model']->unit, $entry['quantity'], (float) $entry['model']->stock_quantity)
                    + ['id' => $id, 'editable' => true];
            }

            foreach ($requirements['textures'] as $entry) {
                $lines[] = $this->line($entry['model']->name, 'pcs', $entry['quantity'], (float) $entry['model']->stock_quantity)
                    + ['id' => null, 'editable' => false];
            }

            return [
                'stage' => 'reserve',
                'note' => 'Approving reserves these off the shelf so another order cannot be promised them. They are not counted as used until staff start production.',
                'lines' => $lines,
                'shortages' => $this->shortages($order),
            ];
        }

        $movements = RawMaterialMovement::where('order_id', $order->order_id)
            ->whereDoesntHave('reversal')
            ->where('reason', '!=', StockMovementReason::Reversal)
            ->with('rawMaterial')
            ->get();

        $reservations = $movements->where('reason', StockMovementReason::Reserved);

        if ($reservations->isNotEmpty()) {
            return [
                'stage' => 'consume',
                'note' => 'These left the shelf when the order was approved. Starting production marks them used — stock does not move again.',
                'lines' => $reservations
                    ->filter(fn (RawMaterialMovement $m) => $m->rawMaterial !== null)
                    ->map(fn (RawMaterialMovement $m) => $this->line(
                        $m->rawMaterial->name,
                        $m->rawMaterial->unit,
                        (float) $m->quantity,
                        (float) $m->rawMaterial->stock_quantity,
                        // Already off the shelf, so there is no further
                        // shortage to warn about here.
                        checkStock: false,
                    ))
                    ->values()
                    ->all(),
                'shortages' => [],
            ];
        }

        $consumed = $movements->where('reason', StockMovementReason::Consumed);

        return [
            'stage' => $consumed->isNotEmpty() ? 'consumed' : 'none',
            'note' => $consumed->isNotEmpty()
                ? 'This order has already drawn its materials.'
                : 'This order has no materials recorded against it.',
            'lines' => $consumed
                ->filter(fn (RawMaterialMovement $m) => $m->rawMaterial !== null)
                ->map(fn (RawMaterialMovement $m) => $this->line(
                    $m->rawMaterial->name,
                    $m->rawMaterial->unit,
                    (float) $m->quantity,
                    (float) $m->rawMaterial->stock_quantity,
                    checkStock: false,
                ))
                ->values()
                ->all(),
            'shortages' => [],
        ];
    }

    /**
     * One row of plannedDraw(), formatted for display.
     *
     * @return array<string, mixed>
     */
    private function line(string $name, ?string $unit, float $quantity, float $stock, bool $checkStock = true): array
    {
        return [
            'id' => null,
            'editable' => false,
            'name' => $name,
            'unit' => $unit ?? '',
            'quantity' => $this->number($quantity),
            'stock' => $this->number($stock),
            // What the shelf reads once this step is applied. Only meaningful
            // while the stock has yet to move.
            'remaining' => $checkStock ? $this->number(max(0, $stock - $quantity)) : null,
            'short' => $checkStock && $quantity > $stock,
        ];
    }

    /**
     * Set aside what this order will need. Call this on approval only.
     *
     * Stock drops now, so the next order to be approved sees a shelf that no
     * longer counts this one's materials. Nothing is marked consumed — see
     * startProduction() for that half.
     *
     * @param  array<int|string, mixed>  $overrides  quantities the reviewer corrected
     */
    public function reserve(Order $order, array $overrides = []): void
    {
        $requirements = $this->requirements($order, $overrides);

        foreach ($requirements['materials'] as $entry) {
            $this->materialStock->record($entry['model'], StockMovementReason::Reserved, $entry['quantity'], [
                'user_id' => Auth::id(),
                'order_id' => $order->order_id,
                // The note says where the figure came from. A quantity a person
                // judged against the artwork and one a formula produced are
                // different kinds of number, and the usage log should not
                // present them as the same.
                'note' => $entry['adjusted']
                    ? "Reserved for approved order {$order->order_number} — quantity set by reviewer"
                    : "Reserved for approved order {$order->order_number}",
            ]);
        }

        foreach ($requirements['textures'] as $entry) {
            $entry['model']->decrement('stock_quantity', $entry['quantity']);
        }
    }

    /**
     * Turn this order's reservations into consumption. Call this when the
     * order enters production.
     *
     * Worth being precise about what moves here: the material left the shelf
     * at approval, so stock is unchanged overall. What changes is that it now
     * counts as *used* — `units_consumed` and the materials report move for
     * the first time.
     *
     * That is done by reversing each reservation and recording consumption
     * against the quantity it held, rather than re-reading the bills of
     * materials: a product's BOM, an option's BOM and a finish's material can
     * all be edited between approval and production, and consuming a figure
     * the order never reserved would invent stock. It also leaves a ledger
     * that reads as what actually happened — reserved, released, consumed —
     * instead of a consumption row appearing from nowhere.
     *
     * Orders approved before reservations existed hold `Consumed` rows
     * already, so they find nothing to convert and are left alone.
     */
    public function startProduction(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $reservations = RawMaterialMovement::where('order_id', $order->order_id)
                ->where('reason', StockMovementReason::Reserved)
                ->whereDoesntHave('reversal')
                ->with('rawMaterial')
                ->get();

            foreach ($reservations as $reservation) {
                if (! $reservation->rawMaterial) {
                    continue;
                }

                $context = [
                    'user_id' => Auth::id(),
                    'order_id' => $order->order_id,
                    'note' => "Production started on order {$order->order_number}",
                ];

                $this->materialStock->reverse($reservation, $context);

                // record() re-reads the row under a lock, so it sees the stock
                // the reversal just put back rather than the stale figure on
                // $reservation->rawMaterial.
                $this->materialStock->record(
                    $reservation->rawMaterial,
                    StockMovementReason::Consumed,
                    (float) $reservation->quantity,
                    $context,
                );
            }
        });
    }

    /**
     * Put materials and textures back. Call this only for an order that was
     * approved — a rejected order never took them.
     *
     * Materials are returned by reversing the ledger rows the order wrote, not
     * by re-reading the bills of materials, for the same reason production
     * doesn't: they can be edited in between, and giving back a quantity the
     * order never took would invent stock. Whether those rows are reservations
     * or consumption depends on how far the order got, and reverseForOrder
     * handles either without being told which.
     */
    public function restore(Order $order): void
    {
        $this->materialStock->reverseForOrder($order->order_id, [
            'user_id' => Auth::id(),
            'note' => "Cancelled order {$order->order_number}",
        ]);

        foreach ($this->requirements($order)['textures'] as $entry) {
            $entry['model']->increment('stock_quantity', $entry['quantity']);
        }
    }

    /**
     * Return the finished-goods stock that checkout took.
     */
    public function returnProducts(Order $order): void
    {
        $order->loadMissing('orderItems.product');

        foreach ($order->orderItems as $item) {
            $item->product?->increment('stock', $item->quantity);
        }
    }

    /**
     * Replace calculated quantities with the ones a reviewer set.
     *
     * Keyed on the material rather than positionally, so a line appearing or
     * disappearing between the form being rendered and submitted can't shift
     * an override onto the wrong material. Anything the order doesn't already
     * draw is dropped — this corrects an estimate, it does not add materials.
     *
     * @param  array<int, array{model: RawMaterial, quantity: float}>  $materials
     * @param  array<int|string, mixed>  $overrides
     * @return array<int, array{model: RawMaterial, quantity: float, adjusted: bool}>
     */
    private function applyOverrides(array $materials, array $overrides): array
    {
        $applied = [];

        foreach ($materials as $id => $entry) {
            $entry['adjusted'] = false;

            if (array_key_exists($id, $overrides) && is_numeric($overrides[$id])) {
                $quantity = round(max(0, (float) $overrides[$id]), 2);

                // Only count it as adjusted if it actually differs. Posting the
                // figure back unchanged is what an untouched form does, and
                // that should read as the formula's number in the ledger.
                $entry['adjusted'] = abs($quantity - $entry['quantity']) > 0.0001;
                $entry['quantity'] = $quantity;
            }

            // Zero means the reviewer decided this artwork uses none of it.
            if ($entry['quantity'] > 0) {
                $applied[$id] = $entry;
            }
        }

        return $applied;
    }

    /**
     * Attach a model to each accumulated quantity.
     *
     * A material that has since been retired drops out here rather than
     * blocking the order — the same thing already happened to product BOM
     * lines, because a soft-deleted material never came back through the
     * relation in the first place.
     *
     * @param  array<int, float>  $quantities
     * @return array<int, array{model: RawMaterial, quantity: float}>
     */
    private function resolveMaterials(array $quantities): array
    {
        if ($quantities === []) {
            return [];
        }

        return RawMaterial::whereIn('raw_material_id', array_keys($quantities))
            ->get()
            ->mapWithKeys(fn (RawMaterial $material) => [
                $material->raw_material_id => [
                    'model' => $material,
                    // Two decimals, because that is what the ledger stores. A
                    // requirement that rounds away to nothing is dropped rather
                    // than written as a zero-quantity movement, which record()
                    // would refuse anyway.
                    'quantity' => round($quantities[$material->raw_material_id], 2),
                ],
            ])
            ->filter(fn (array $entry) => $entry['quantity'] > 0)
            ->all();
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
