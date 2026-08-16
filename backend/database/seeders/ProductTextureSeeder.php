<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Texture;

/**
 * Which textures each customizable product offers in the 3D customizer.
 *
 * A product is offered exactly what it is assigned here and nothing else, so an
 * item left out of this list has no patterned finish at all — customers pick a
 * plain colour for it instead. Only products the studio has a model for are
 * worth listing; the ID lace and the oak plaque are not customizable.
 */
class ProductTextureSeeder extends Seeder
{
    public function run(): void
    {
        $textures = Texture::all()->keyBy('name');

        $assignments = [
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
