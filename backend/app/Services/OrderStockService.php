<?php

namespace App\Services;

use App\Enums\StockMovementReason;
use App\Models\CustomizationRate;
use App\Models\InkChannel;
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
 * Ink and transfer paper are the exceptions to "bill of materials". A BOM
 * can only guess at a picture, so where the product's printable area is
 * known and the printer's channels are linked to bottles, a design's ink is
 * *measured* instead — see InkEstimator — and where the transfer sheet is
 * linked to stock, so is the paper: the piece cut around each panel's
 * artwork, as a fraction of a sheet. Any ink or paper the BOMs would have
 * drawn for the designed item is skipped, so a bottle or a sheet is never
 * drawn twice for one print. A product with no print area, or an install
 * with nothing linked, falls back to the BOMs as before.
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
    public function __construct(
        private RawMaterialStockService $materialStock,
        private InkEstimator $ink,
    ) {
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
     * Measured ink comes with its working. `notes` is `raw_material_id => [string]`,
     * one line per item that drew on the bottle, saying what coverage, what
     * area and what rate produced the figure, so the reviewer can see why it
     * is what it is before deciding whether to correct it. `prints` are the
     * flat prints those figures were measured from, for the same reason.
     *
     * @param  array<int|string, mixed>  $overrides
     * @return array{materials: array<int, array{model: RawMaterial, quantity: float, adjusted: bool}>, textures: array<int, array{model: Texture, quantity: float}>, notes: array<int, array<int, string>>, prints: array<int, array{url: string, label: string, zones: array<int, array<string, mixed>>}>, warnings: array<int, string>}
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
        $notes = [];
        $prints = [];
        $warnings = [];

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

            // Whether this item's ink and paper are measured rather than
            // taken from the bills of materials, and if so which materials
            // the measurements reach — those are skipped in the BOMs below,
            // wherever a line only applies to a designed item, so one print
            // never draws a bottle or a sheet twice.
            $measured = $design && $this->ink->applies($design, $item->product);
            $paper = $design ? $this->ink->paper($design, $item->product) : null;

            $replaced = $measured ? array_values(InkChannel::linkedMaterials()) : [];
            if ($paper !== null) {
                $replaced[] = $paper['material_id'];
            }

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
                if ($material->pivot->requires_design && in_array($material->raw_material_id, $replaced, true)) {
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
            //
            //    Where the ink is measured, any ink these options would draw
            //    is left out: the measurement already covers everything the
            //    printer lays down for this design.
            foreach ($design->customizationUnits() as $rateKey => $units) {
                foreach (CustomizationRate::materialsFor($rateKey) as $materialId => $perUnit) {
                    if (in_array($materialId, $replaced, true)) {
                        continue;
                    }

                    $add($materialId, $perUnit * $units * $quantity);
                }
            }

            // 2b. The ink itself, measured off the print: coverage × the
            //     product's printable area at the ordered size × the
            //     channel's rate, per item.
            if ($measured) {
                $size = $design->recipe['size'] ?? null;
                $sizeLabel = ($key = CustomizationRate::keyForSize($size))
                    ? CustomizationRate::DEFINITIONS[$key]['short']
                    : 'M';

                foreach ($this->ink->millilitres($design, $item->product) as $materialId => $draw) {
                    $add($materialId, $draw['quantity'] * $quantity);

                    $notes[$materialId][] = sprintf(
                        '%s: %s%% %s coverage of %s cm² (%s, size %s) × %s ml/cm²%s%s',
                        $item->product->name,
                        $this->number($draw['coverage'] * 100),
                        InkChannel::CHANNELS[$draw['channel']]['label'] ?? $draw['channel'],
                        $this->number($draw['area']),
                        $draw['source'] === 'measured' ? 'measured from the print' : 'estimated from the design',
                        $sizeLabel,
                        rtrim(rtrim(number_format($draw['rate'], 5, '.', ''), '0'), '.'),
                        $quantity > 1 ? " × {$quantity} items" : '',
                        $draw['quantity'] > 0 ? '' : ' — none of this colour in the artwork',
                    );
                }
            }

            // 2c. The transfer paper, measured off the same print: each
            //     panel's artwork is boxed, the piece the shop cuts is that
            //     box plus the margin, and the draw is the fraction of a
            //     sheet those pieces come to. A piece the sheet can't hold
            //     is still counted, and flagged, because the printers can't
            //     print it in one go and the reviewer has to decide.
            if ($paper !== null) {
                $add($paper['material_id'], $paper['sheets'] * $quantity);

                $sheet = $paper['sheet'];
                foreach ($paper['panels'] as $panel) {
                    $notes[$paper['material_id']][] = sprintf(
                        '%s: %s prints %s × %s cm (%s × %s in), cut %s × %s cm with the %s cm margin = %s of an %s sheet%s',
                        $item->product->name,
                        $panel['label'],
                        $this->number($panel['width_cm']),
                        $this->number($panel['height_cm']),
                        $this->number(round($panel['width_cm'] / 2.54, 1)),
                        $this->number(round($panel['height_cm'] / 2.54, 1)),
                        $this->number($panel['piece_width_cm']),
                        $this->number($panel['piece_height_cm']),
                        $this->number($sheet['margin_cm']),
                        $this->number($panel['fraction']),
                        $sheet['name'],
                        $quantity > 1 ? " × {$quantity} items" : '',
                    );

                    if (! $panel['fits']) {
                        $warnings[] = sprintf(
                            '%s: the %s artwork is %s × %s cm with its margin, larger than an %s sheet (%s × %s cm). The printers cannot print it in one piece — check the design before approving.',
                            $item->product->name,
                            $panel['label'],
                            $this->number($panel['piece_width_cm']),
                            $this->number($panel['piece_height_cm']),
                            $sheet['name'],
                            $this->number($sheet['width_cm']),
                            $this->number($sheet['height_cm']),
                        );
                    }
                }
            }

            // The print with its panels, so the screen can crop and label
            // each one rather than show the whole atlas, with each panel's
            // measured print size where it has one. No panels means a
            // design saved before they were recorded; the screen then shows
            // the print whole.
            if ($design->print_image_url) {
                $sizes = [];
                foreach ($paper['panels'] ?? [] as $panel) {
                    $sizes[$panel['label']] = [$panel['width_cm'], $panel['height_cm']];
                }

                $zones = [];
                foreach (is_array($design->print_zones) ? $design->print_zones : [] as $zone) {
                    $size = $sizes[$zone['label'] ?? ''] ?? null;
                    $zones[] = $zone + ['width_cm' => $size[0] ?? null, 'height_cm' => $size[1] ?? null];
                }

                $prints[$design->custom_design_id] = [
                    'url' => $design->print_image_url,
                    'label' => $item->product->name,
                    'zones' => $zones,
                ];
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
            'notes' => $notes,
            'prints' => array_values($prints),
            'warnings' => $warnings,
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
     * @return array{stage: string, note: string, lines: array<int, array<string, mixed>>, shortages: array<int, string>, prints: array<int, array{url: string, label: string, zones: array<int, array<string, mixed>>}>}
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
                $lines[] = $this->line(
                    $entry['model']->name,
                    $entry['model']->unit,
                    $entry['quantity'],
                    (float) $entry['model']->stock_quantity,
                    id: $id,
                    editable: true,
                    notes: $requirements['notes'][$id] ?? [],
                );
            }

            foreach ($requirements['textures'] as $entry) {
                $lines[] = $this->line($entry['model']->name, 'pcs', $entry['quantity'], (float) $entry['model']->stock_quantity);
            }

            return [
                'stage' => 'reserve',
                'note' => 'Approving reserves these off the shelf so another order cannot be promised them. They are not counted as used until staff start production.',
                'lines' => $lines,
                'shortages' => $this->shortages($order),
                'prints' => $requirements['prints'],
                // Things worth a look that don't block approval — a print
                // bigger than the sheet, for one.
                'warnings' => $requirements['warnings'],
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
                'prints' => [],
                'warnings' => [],
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
            'prints' => [],
            'warnings' => [],
        ];
    }

    /**
     * One row of plannedDraw(), formatted for display.
     *
     * @param  array<int, string>  $notes  the working behind a measured figure
     * @return array<string, mixed>
     */
    private function line(
        string $name,
        ?string $unit,
        float $quantity,
        float $stock,
        bool $checkStock = true,
        ?int $id = null,
        bool $editable = false,
        array $notes = [],
    ): array {
        return [
            // Named so the form can post a correction back against the right
            // material. Passed in rather than merged over the result: array
            // union keeps the left operand's keys, so merging onto a row that
            // already carries these silently discarded them.
            'id' => $id,
            'editable' => $editable,
            'notes' => $notes,
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
        $order->loadMissing('orderItems.product', 'orderItems.productVariant');
        $stock = app(ProductStockService::class);

        foreach ($order->orderItems as $item) {
            if ($item->product) {
                // Back into the size-and-colour cell it was taken from.
                $stock->give($item->product, $item->productVariant, (int) $item->quantity);
            }
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
                $quantity = round(max(0, (float) $overrides[$id]), 4);

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
                    // Four decimals, the ledger's own precision. It has to be that
                    // fine because a measured ink draw for a small print is a few
                    // thousandths of a millilitre — at two places magenta on a 2 × 4
                    // cm logo rounded to nothing and silently dropped off the order.
                    // A requirement that still rounds away is dropped rather than
                    // written as a zero-quantity movement, which record() would
                    // refuse anyway.
                    'quantity' => round($quantities[$material->raw_material_id], 4),
                ],
            ])
            ->filter(fn (array $entry) => $entry['quantity'] > 0)
            ->all();
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }
}
