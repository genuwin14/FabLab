<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Texture;

class TextureSeeder extends Seeder
{
    public function run(): void
    {
        $textures = [
            [
                'name' => 'Smooth Black Leather',
                'description' => 'Matte black leather texture with fine grain.'
            ],
            [
                'name' => 'Natural Oak Grain',
                'description' => 'Light oak wood pattern with visible rings.'
            ],
            [
                'name' => 'Rustic Walnut',
                'description' => 'Dark walnut texture for a vintage look.'
            ],
            [
                'name' => 'Woven Fabric (Grey)',
                'description' => 'Coarse woven fabric pattern for modern sofas.'
            ],
            [
                'name' => 'Polished Chrome',
                'description' => 'Reflective metallic texture for chair legs.'
            ],
        ];

        foreach ($textures as $texture) {
            Texture::create($texture);
        }
    }
}
