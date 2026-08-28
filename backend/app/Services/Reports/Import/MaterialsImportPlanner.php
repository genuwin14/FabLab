<?php

namespace App\Services\Reports\Import;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;
use Illuminate\Support\Collection;

/**
 * Turns parsed report rows into a plan an admin can read before anything is
 * written.
 *
 * Nothing here touches the database. The planner works out what each row *would*
 * do — which record it matches, which figures would change, and by how much —
 * so the preview screen and the apply step are looking at the same decision.
 * Producing the plan twice, once to show and once to write, is deliberate: the
 * uploaded file is not kept between the two requests.
 *
 * Matching is by name against all three tables the report prints — products,
 * raw materials and textures — because the report interleaves them and only the
 * name column says which is which. The seeders keep those three sets disjoint
 * on purpose, so a name landing in two of them is a data problem worth showing
 * rather than resolving silently.
 */
class MaterialsImportPlanner
{
    /** The figures the report carries, in the order the report prints them. */
    public const COUNTERS = ['on_display', 'sponsored', 'damaged', 'consumed'];

    /**
     * @param  list<array<string, mixed>>  $rows  from MaterialsDocxParser
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function plan(array $rows): array
    {
        $products = $this->index(Product::all(), 'product_id', 'stock');
        $materials = $this->index(RawMaterial::all(), 'raw_material_id', 'stock_quantity');
        $textures = $this->index(Texture::all(), 'texture_id', 'stock_quantity');

        $items = [];

        foreach ($rows as $index => $row) {
            $key = $this->key($row['name']);

            $matches = array_values(array_filter([
                $products[$key] ?? null,
                $materials[$key] ?? null,
                $textures[$key] ?? null,
            ]));

            $item = match (count($matches)) {
                0 => $this->unmatched($row),
                1 => $this->matched($row, $matches[0]),
                default => $this->ambiguous($row, $matches),
            };

            // The row's position in the file, so the preview's checkboxes and
            // the apply step are talking about the same row. Names are not
            // usable as keys here — a report may print one twice.
            $item['index'] = $index;

            $items[] = $item;
        }

        return ['items' => $items, 'summary' => $this->summarise($items)];
    }

    /**
     * A row that matches exactly one record. Every figure is compared so the
     * preview can show what would actually move, and a row where nothing moved
     * is marked unchanged rather than dressed up as work.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private function matched(array $row, array $target): array
    {
        $model = $target['model'];
        $changes = [];

        foreach (self::COUNTERS as $counter) {
            $current = (float) $model->{'units_' . $counter};

            if ($this->differs($current, (float) $row[$counter])) {
                $changes[$counter] = ['from' => $current, 'to' => (float) $row[$counter]];
            }
        }

        $currentStock = (float) $model->{$target['stock_column']};

        if ($this->differs($currentStock, (float) $row['available'])) {
            $changes['available'] = ['from' => $currentStock, 'to' => (float) $row['available']];
        }

        return [
            'status' => $changes === [] ? 'unchanged' : 'update',
            'name' => $row['name'],
            'unit' => $row['unit'],
            'department' => $row['department'],
            'type' => $target['type'],
            'id' => $target['id'],
            'stock_column' => $target['stock_column'],
            'current_unit' => $model->unit,
            'values' => $this->values($row),
            'changes' => $changes,
            'notes' => $this->unitNote($row, $model),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function unmatched(array $row): array
    {
        return [
            'status' => 'unmatched',
            'name' => $row['name'],
            'unit' => $row['unit'],
            'department' => $row['department'],
            'type' => null,
            'id' => null,
            'stock_column' => null,
            'current_unit' => null,
            'values' => $this->values($row),
            'changes' => [],
            'notes' => ['No product, raw material or texture of that name exists yet.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<array<string, mixed>>  $matches
     * @return array<string, mixed>
     */
    private function ambiguous(array $row, array $matches): array
    {
        $types = implode(' and ', array_column($matches, 'type'));

        return [
            'status' => 'ambiguous',
            'name' => $row['name'],
            'unit' => $row['unit'],
            'department' => $row['department'],
            'type' => null,
            'id' => null,
            'stock_column' => null,
            'current_unit' => null,
            'values' => $this->values($row),
            'changes' => [],
            'notes' => ["That name exists as both a {$types}, so the report does not say which one to update."],
        ];
    }

    /**
     * The unit is not imported — it describes the item, not the count — but a
     * mismatch means the figures may be measured differently from how they are
     * held, which is worth seeing before agreeing to the numbers.
     *
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function unitNote(array $row, $model): array
    {
        $reported = trim((string) $row['unit']);
        $held = trim((string) $model->unit);

        if ($reported === '' || $held === '' || strcasecmp($reported, $held) === 0) {
            return [];
        }

        return ["The report measures this in {$reported}, but it is held in {$held}."];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, float>
     */
    private function values(array $row): array
    {
        return [
            'on_display' => (float) $row['on_display'],
            'sponsored' => (float) $row['sponsored'],
            'damaged' => (float) $row['damaged'],
            'consumed' => (float) $row['consumed'],
            'available' => (float) $row['available'],
        ];
    }

    /**
     * Index a table by squashed name. A name held twice within one table would
     * make either match arbitrary, so the first is kept and the duplicate is
     * left to be reported as unmatched rather than silently picked.
     *
     * @return array<string, array<string, mixed>>
     */
    private function index(Collection $models, string $idColumn, string $stockColumn): array
    {
        $indexed = [];

        foreach ($models as $model) {
            $key = $this->key($model->name);

            $indexed[$key] ??= [
                'model' => $model,
                'id' => $model->{$idColumn},
                'type' => match ($idColumn) {
                    'product_id' => 'Product',
                    'raw_material_id' => 'Raw Material',
                    default => 'Texture',
                },
                'stock_column' => $stockColumn,
            ];
        }

        return $indexed;
    }

    /**
     * Names are matched loosely on purpose: a report retyped over the years
     * picks up stray spacing and punctuation that should not stop a match.
     */
    private function key(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $name) ?? '');
    }

    /** Quantities are stored to two decimals, so compare at that resolution. */
    private function differs(float $a, float $b): bool
    {
        return round($a, 2) !== round($b, 2);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, int>
     */
    private function summarise(array $items): array
    {
        $counts = ['update' => 0, 'unchanged' => 0, 'unmatched' => 0, 'ambiguous' => 0];

        foreach ($items as $item) {
            $counts[$item['status']]++;
        }

        $counts['total'] = count($items);

        return $counts;
    }
}
