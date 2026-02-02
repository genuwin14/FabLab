<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Tech Supply Co.',
                'contact_person' => 'Mario Rossi',
                'email' => 'sales@techsupply.com',
                'phone' => '09121112222',
                'address' => 'Manila, Philippines'
            ],
            [
                'name' => 'General Merchandise Inc.',
                'contact_person' => 'Jane Smith',
                'email' => 'jane@genmerch.com',
                'phone' => '09123334444',
                'address' => 'Quezon City, Philippines'
            ],
            [
                'name' => 'Eco-Materials Ltd.',
                'contact_person' => 'Lin Wood',
                'email' => 'info@ecomaterials.com',
                'phone' => '09125556666',
                'address' => 'Cebu City, Philippines'
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
