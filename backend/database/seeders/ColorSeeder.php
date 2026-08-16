<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

/**
 * A starter palette so the customizer offers plain finishes out of the box.
 * Admins manage the real range under Admin → Colors.
 */
class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['name' => 'Classic White',  'hex_code' => '#f5f5f5', 'description' => 'Standard undyed finish.',            'price_modifier' => 0],
            ['name' => 'Jet Black',      'hex_code' => '#12151a', 'description' => 'Deep matte black.',                  'price_modifier' => 0],
            ['name' => 'Heather Grey',   'hex_code' => '#9aa3ad', 'description' => 'Neutral mid grey.',                  'price_modifier' => 0],
            ['name' => 'Navy Blue',      'hex_code' => '#1b2a4a', 'description' => 'Deep navy, the house standard.',     'price_modifier' => 0],
            ['name' => 'Cherry Red',     'hex_code' => '#b02a37', 'description' => 'Rich warm red.',                     'price_modifier' => 40],
            ['name' => 'Forest Green',   'hex_code' => '#1f4d2e', 'description' => 'Muted deep green.',                  'price_modifier' => 40],
            ['name' => 'Sunset Orange',  'hex_code' => '#e2670e', 'description' => 'High-visibility orange.',            'price_modifier' => 60],
            ['name' => 'FABLAB Gold',    'hex_code' => '#ffc508', 'description' => 'House accent. Special-order dye.',   'price_modifier' => 120],
        ];

        foreach ($colors as $color) {
            Color::firstOrCreate(['name' => $color['name']], $color);
        }
    }
}
