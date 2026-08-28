<?php

namespace App\Services\Reports\Import;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;
use App\Services\RawMaterialStockService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Writes an approved import plan.
 *
 * Only rows the planner marked `update` are applied. Unmatched and ambiguous
 * rows were shown to the admin and are carried through to the result untouched,
 * so the summary afterwards accounts for every line of the report rather than
 * only the ones that worked.
 *
 * Raw materials go through RawMaterialStockService, which is the only thing
 * allowed to move their counters. Products and textures have no ledger — their
 * counters are edited directly everywhere else in the app — so they are updated
 * in place, and the whole run shares one transaction so a failure half way
 * cannot leave the report half-applied.
 */
class MaterialsImportApplier
{
    public function __construct(private RawMaterialStockService $stock) {}

    /**
     * @param  array{items: list<array<string, mixed>>}  $plan  from MaterialsImportPlanner
     * @param  array{note?: string|null, user_id?: int|null}  $context
     * @param  array{indexes: list<int>, supplier_id: int}|null  $create  rows the
     *         admin ticked to be created, and the supplier to file them under
     * @return array{applied: int, created: int, skipped: int, failed: list<array{name: string, reason: string}>}
     */
    public function apply(array $plan, array $context = [], ?array $create = null): array
    {
        $applied = 0;
        $created = 0;
        $skipped = 0;
        $failed = [];

        // Ticked rows, as a set — a 91-row report should not cost 91 scans.
        $toCreate = array_flip($create['indexes'] ?? []);

        DB::transaction(function () use ($plan, $context, $create, $toCreate, &$applied, &$created, &$skipped, &$failed) {
            foreach ($plan['items'] as $item) {
                $isNew = $item['status'] === 'unmatched' && isset($toCreate[$item['index']]);

                if ($item['status'] !== 'update' && ! $isNew) {
                    $skipped++;

                    continue;
                }

                try {
                    if ($isNew) {
                        $this->createOne($item, (int) $create['supplier_id'], $context);
                        $created++;
                    } else {
                        $this->applyOne($item, $context);
                        $applied++;
                    }
                } catch (Throwable $e) {
                    // Collected rather than thrown: one unimportable row should
                    // not cost the admin the whole upload. The transaction still
                    // wraps the run, so a database failure rolls everything back.
                    $failed[] = ['name' => $item['name'], 'reason' => $e->getMessage()];
                }
            }
        });

        return ['applied' => $applied, 'created' => $created, 'skipped' => $skipped, 'failed' => $failed];
    }

    /**
     * Bring in an item the report names but the system has never held.
     *
     * Created as a raw material, because that is what an Inventory of Materials
     * lists. Anything that is really a sellable product needs a SKU, a price and
     * a category the report does not carry, so those are left to be added on the
     * Products screen rather than invented here.
     *
     * The row is created empty and then given its figures through the ledger, so
     * an imported item's opening position is on the record the same way every
     * later movement will be, rather than appearing from nowhere.
     *
     * @param  array<string, mixed>  $item
     * @param  array{note?: string|null, user_id?: int|null}  $context
     */
    private function createOne(array $item, int $supplierId, array $context): void
    {
        $material = RawMaterial::create([
            'name' => $item['name'],
            'supplier_id' => $supplierId,
            // Not in the report. Left at zero rather than guessed, so an
            // unpriced item is visibly unpriced instead of plausibly wrong.
            'cost_per_unit' => 0,
            'stock_quantity' => 0,
            'unit' => $item['unit'] !== '' ? $item['unit'] : 'pcs',
            'department' => $item['department'] === 'Uncategorized' ? null : $item['department'],
            'description' => 'Imported from an inventory report.',
        ]);

        $values = $item['values'];

        $this->stock->openingBalance(
            material: $material,
            counters: [
                'on_display' => $values['on_display'],
                'sponsored' => $values['sponsored'],
                'damaged' => $values['damaged'],
                'consumed' => $values['consumed'],
            ],
            available: $values['available'],
            context: $context,
        );
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array{note?: string|null, user_id?: int|null}  $context
     */
    private function applyOne(array $item, array $context): void
    {
        $values = $item['values'];

        if ($item['type'] === 'Raw Material') {
            $material = RawMaterial::find($item['id']);

            if (! $material) {
                throw new RuntimeException('That material no longer exists.');
            }

            $this->stock->openingBalance(
                material: $material,
                counters: [
                    'on_display' => $values['on_display'],
                    'sponsored' => $values['sponsored'],
                    'damaged' => $values['damaged'],
                    'consumed' => $values['consumed'],
                ],
                available: $values['available'],
                context: $context,
            );

            return;
        }

        $model = match ($item['type']) {
            'Product' => Product::find($item['id']),
            'Texture' => Texture::find($item['id']),
            default => throw new RuntimeException("Unknown item type '{$item['type']}'."),
        };

        if (! $model) {
            throw new RuntimeException('That item no longer exists.');
        }

        $model->update([
            'units_on_display' => $values['on_display'],
            'units_sponsored' => $values['sponsored'],
            'units_damaged' => $values['damaged'],
            'units_consumed' => $values['consumed'],
            $item['stock_column'] => $values['available'],
        ]);
    }
}
