<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\RawMaterial;

class BOMSeeder extends Seeder
{
    public function run(): void
    {
        $sofa = Product::where('sku', 'FN-SFA-LTH-BLK')->first();
        
        if ($sofa) {
            $leather = RawMaterial::where('name', 'like', '%Leather%')->first();
            $foam = RawMaterial::where('name', 'like', '%Foam%')->first();
            $wood = RawMaterial::where('name', 'like', '%Wood%')->first();
            $springs = RawMaterial::where('name', 'like', '%Springs%')->first();

            $materials = [];
            if ($leather) $materials[$leather->raw_material_id] = ['quantity_required' => 5.5]; // 5.5 meters
            if ($foam) $materials[$foam->raw_material_id] = ['quantity_required' => 3.0]; // 3 pcs
            if ($wood) $materials[$wood->raw_material_id] = ['quantity_required' => 10.0]; // 10 planks
            if ($springs) $materials[$springs->raw_material_id] = ['quantity_required' => 24.0]; // 24 springs

            $sofa->rawMaterials()->attach($materials);
        }

        // Link T-Shirt to Cotton Fabric (Example)
        $tshirt = Product::where('sku', 'TS-CTN-WHT')->first();
        if ($tshirt) {
            $cotton = RawMaterial::where('name', 'like', '%Cotton%')->first();
            if ($cotton) {
                $tshirt->rawMaterials()->attach($cotton->raw_material_id, ['quantity_required' => 0.8]); // 0.8 meters per shirt
            }
        }
    }
}
