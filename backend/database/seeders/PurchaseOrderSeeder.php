<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Admin User
        $admin = User::where('role', 'admin')->first();
        if (!$admin)
            return;

        // Get Suppliers and Products
        $suppliers = Supplier::all();
        $products = Product::all();

        $techSupply = $suppliers->firstWhere('name', 'Tech Supply Co.');
        $genMerch = $suppliers->firstWhere('name', 'General Merchandise Inc.');

        $mugWhite = $products->firstWhere('sku', 'MG-WHT-11');
        $mugBlack = $products->firstWhere('sku', 'MG-BLK-11');

        // Filament is a raw material, not a product — a purchase order line
        // points at it through raw_material_id.
        $filament = RawMaterial::where('name', 'PLA Filament Red 1.75mm')->first();
        $ink = RawMaterial::where('name', 'Sublimation Ink (CMYK Set)')->first();

        // IF data is missing (e.g. partial seed), skip
        if (!$techSupply || !$genMerch || !$filament || !$mugWhite)
            return;

        // PO 1: DRAFT (Tech Supply - Filament Restock)
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-' . now()->format('Ymd') . '-DRFT',
            'supplier_id' => $techSupply->supplier_id,
            'status' => 'draft',
            'expected_delivery_date' => now()->addDays(5),
            'total_cost' => 3000.00,
            'created_by' => $admin->id
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po1->purchase_order_id,
            'raw_material_id' => $filament->raw_material_id, // Red Filament
            'quantity' => 5,
            'cost' => 600.00
        ]);


        // PO 2: SENT (General Merch - Mugs)
        $po2 = PurchaseOrder::create([
            'po_number' => 'PO-' . now()->format('Ymd') . '-SENT',
            'supplier_id' => $genMerch->supplier_id,
            'status' => 'sent',
            'expected_delivery_date' => now()->addDays(7),
            'total_cost' => 4500.00,
            'created_by' => $admin->id
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po2->purchase_order_id,
            'product_id' => $mugWhite->product_id,
            'quantity' => 100,
            'cost' => 45.00
        ]);


        // PO 3: DELIVERED (Historical)
        $po3 = PurchaseOrder::create([
            'po_number' => 'PO-20231201-OLD1',
            'supplier_id' => $genMerch->supplier_id,
            'status' => 'delivered',
            'expected_delivery_date' => now()->subMonth(),
            'total_cost' => 5500.00,
            'created_by' => $admin->id,
            'created_at' => now()->subMonth()
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po3->purchase_order_id,
            'product_id' => $mugBlack->product_id,
            'quantity' => 100,
            'cost' => 55.00
        ]);

        // PO 4: SENT (Tech Supply — ink restock)
        //
        // The ink is sitting on its low-stock threshold, and this is the order
        // that answers it. Left at `sent` so it is one step from delivered:
        // marking it so puts 6 sets back on the shelf, which is the other half
        // of the story the usage ledger tells.
        if ($ink) {
            $po4 = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-INK1',
                'supplier_id' => $techSupply->supplier_id,
                'status' => 'sent',
                'expected_delivery_date' => now()->addDays(3),
                'total_cost' => 11100.00,
                'created_by' => $admin->id
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $po4->purchase_order_id,
                'raw_material_id' => $ink->raw_material_id,
                'quantity' => 6,
                'cost' => 1850.00
            ]);
        }
    }
}
