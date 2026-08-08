<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Categories classify **products** — the things the FabLab sells.
     *
     * There is deliberately no "Raw Materials" category. Raw materials are not
     * products: they live in the `raw_materials` table, are drawn down by a
     * product's bill of materials, and are restocked through purchase orders.
     * Seeding them as products too meant the same plywood existed twice, once
     * on each screen, with two stock figures that never agreed.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Finished Goods',
                'description' => 'Customised output made in-house: lanyards, booklets, engraved items.'
            ],
            [
                'name' => 'Merchandise',
                'description' => 'FabLab branded shirts, mugs, and other items.'
            ],
            [
                'name' => 'Electronics',
                'description' => 'Sensors, microcontrollers, and wiring components.'
            ],
            [
                'name' => 'Furniture',
                'description' => 'Woodworks output such as stools, tables, and shelving.'
            ],
            [
                'name' => 'Machinery & Equipment',
                'description' => 'Permanent assets such as laser cutters and 3D printers.'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
