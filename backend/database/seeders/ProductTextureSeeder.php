<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Texture;

class ProductTextureSeeder extends Seeder
{
    public function run(): void
    {
        $textures = Texture::all()->keyBy('name');

        $assignments = [
            'WD-PLQ-OAK' => [
                'Natural Oak Grain',
                'Rustic Walnut',
                'Smooth Black Leather',
                'Polished Chrome',
            ],
            'IDL-LACE-STD' => [
                'Woven Fabric (Grey)',
                'Smooth Black Leather',
                'Polished Chrome',
            ],
            'MG-WHT-11' => [
                'Smooth Black Leather',
                'Polished Chrome',
                'Woven Fabric (Grey)',
            ],
            'MG-BLK-11' => [
                'Smooth Black Leather',
                'Polished Chrome',
                'Woven Fabric (Grey)',
            ],
            'TS-CTN-WHT' => [
                'Smooth Black Leather',
                'Natural Oak Grain',
                'Woven Fabric (Grey)',
            ],
        ];

        foreach ($assignments as $sku => $textureNames) {
            $product = Product::where('sku', $sku)->first();
            if (!$product) continue;

            $textureIds = collect($textureNames)
                ->map(fn($name) => $textures[$name]?->texture_id ?? null)
                ->filter()
                ->all();

            if (!empty($textureIds)) {
                $product->textures()->sync($textureIds);
            }
        }
    }
}
