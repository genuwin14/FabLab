<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Raw Materials',
                'description' => 'Unprocessed materials like wood, acrylic, and filament.'
            ],
            [
                'name' => 'Machinery & Equipment',
                'description' => 'Permanent assets such as laser cutters and 3D printers.'
            ],
            [
                'name' => 'Finished Goods',
                'description' => 'Ready-to-sell customized products.'
            ],
            [
                'name' => 'Merchandise',
                'description' => 'Fablab branded shirts, mugs, and other items.'
            ],
            [
                'name' => 'Electronics',
                'description' => 'Sensors, microcontrollers, and wiring components.'
            ],
            [
                'name' => 'Furniture',
                'description' => 'Large scale furniture items like sofas, tables, and chairs.'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
