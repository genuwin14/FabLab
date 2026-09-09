<?php

use App\Models\CustomizationRate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The studio offered Small, Medium and Large. The shop takes garments up to
 * 5XL, so the size list now runs S, M, L, XL, 2XL, 3XL, 4XL, 5XL — one rate
 * row each, so an admin can price the bigger sizes like any other.
 *
 * Seeded at zero, like the first three: until an admin sets them, a 5XL costs
 * what a Medium does. A fresh install already gets these rows from the table's
 * create migration, which reads the same DEFINITIONS, so only the missing ones
 * are inserted.
 */
return new class extends Migration
{
    private const KEYS = ['size_xl', 'size_2xl', 'size_3xl', 'size_4xl', 'size_5xl'];

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
        DB::table('customization_rate_materials')->whereIn('rate_key', self::KEYS)->delete();
        DB::table('customization_rates')->whereIn('key', self::KEYS)->delete();
    }
};
