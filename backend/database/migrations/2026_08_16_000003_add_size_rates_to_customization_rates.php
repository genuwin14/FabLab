<?php

use App\Models\CustomizationRate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Size used to be presentation only — picking S/M/L scaled the 3D model and
 * changed nothing else, which was no use when the customer can already zoom.
 * It now records what size was ordered and can carry a surcharge, so a Large
 * shirt can cost more than a Small.
 *
 * Seeded at zero: until an admin sets them, every size costs what it did before.
 */
return new class extends Migration
{
    private const KEYS = ['size_small', 'size_medium', 'size_large'];

    public function up(): void
    {
        $now = now();
        $existing = DB::table('customization_rates')->whereIn('key', self::KEYS)->pluck('key')->all();

        $rows = collect(self::KEYS)
            ->reject(fn($key) => in_array($key, $existing, true))
            ->map(fn($key) => [
                'key' => $key,
                'amount' => CustomizationRate::DEFINITIONS[$key]['default'],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($rows) {
            DB::table('customization_rates')->insert($rows);
        }
    }

    public function down(): void
    {
        DB::table('customization_rates')->whereIn('key', self::KEYS)->delete();
    }
};
