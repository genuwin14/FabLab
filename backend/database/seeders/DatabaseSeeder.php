<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            RawMaterialSeeder::class,
            TextureSeeder::class,
            EquipmentSeeder::class,
            BOMSeeder::class,
            // Must follow RawMaterialSeeder: it writes the usage ledger that
            // produces the units_* counters, rather than them being typed in.
            RawMaterialMovementSeeder::class,
            ProductTextureSeeder::class,
            PurchaseOrderSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
