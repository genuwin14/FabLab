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
     * @return array{applied: int, skipped: int, failed: list<array{name: string, reason: string}>}
     */
    public function apply(array $plan, array $context = []): array
    {
        $applied = 0;
        $skipped = 0;
        $failed = [];

        DB::transaction(function () use ($plan, $context, &$applied, &$skipped, &$failed) {
            foreach ($plan['items'] as $item) {
                if ($item['status'] !== 'update') {
                    $skipped++;

                    continue;
                }

                try {
                    $this->applyOne($item, $context);
                    $applied++;
                } catch (Throwable $e) {
                    // Collected rather than thrown: one unimportable row should
                    // not cost the admin the whole upload. The transaction still
                    // wraps the run, so a database failure rolls everything back.
                    $failed[] = ['name' => $item['name'], 'reason' => $e->getMessage()];
                }
            }
        });

        return ['applied' => $applied, 'skipped' => $skipped, 'failed' => $failed];
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
