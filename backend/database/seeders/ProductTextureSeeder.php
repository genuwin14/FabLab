<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Texture;

/**
 * Which textures each customizable product offers in the 3D customizer.
 *
 * A product with no assignment falls back to showing every texture, so this is
 * about narrowing the choice to what actually suits the item — leather on a
 * plaque, cloth on a shirt — rather than about enabling customization.
 */
class ProductTextureSeeder extends Seeder
{
    public function run(): void
    {
        $textures = Texture::all()->keyBy('name');

        $assignments = [
            // Wood and leather read well engraved; cloth does not.
            'WD-PLQ-OAK' => ['Leather037', 'Leather034C', 'Fabric062'],

            'IDL-LACE-STD' => ['Fabric077', 'Fabric080', 'Fabric083'],

            'MG-WHT-11' => ['Fabric083', 'Leather037', 'Fabric062'],
            'MG-BLK-11' => ['Fabric083', 'Leather037', 'Fabric062'],

            // Garment: cloth only.
            'TS-CTN-WHT' => ['Fabric077', 'Fabric080', 'Fabric083', 'Fabric062'],
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
