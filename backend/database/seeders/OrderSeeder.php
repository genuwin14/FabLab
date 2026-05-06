<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();
        if (!$customer)
            return;

        $products = Product::all();
        $mugWhite = $products->firstWhere('sku', 'MG-WHT-11');
        $mugBlack = $products->firstWhere('sku', 'MG-BLK-11');
        $tshirt = $products->firstWhere('sku', 'TS-CTN-WHT');
        $plywood = $products->firstWhere('sku', 'RW-PLY-001');
        $sofa = $products->firstWhere('sku', 'FN-SFA-LTH-BLK');

        if (!$mugWhite || !$mugBlack || !$tshirt || !$plywood || !$sofa)
            return;

        // Order 1: PENDING (Awaiting admin approval)
        $o1 = Order::create([
            'order_number' => 'ORDR-' . now()->format('Ymd') . '-PEND',
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 540.00,
        ]);
        OrderItem::create([
            'order_id' => $o1->order_id,
            'product_id' => $tshirt->product_id,
            'quantity' => 3,
            'price' => 180.00,
        ]);

        // Order 2: APPROVED (Approved, awaiting payment / processing)
        $o2 = Order::create([
            'order_number' => 'ORDR-' . now()->format('Ymd') . '-APRV',
            'user_id' => $customer->id,
            'status' => 'approved',
            'total_amount' => 850.00,
        ]);
        OrderItem::create([
            'order_id' => $o2->order_id,
            'product_id' => $mugWhite->product_id,
            'quantity' => 10,
            'price' => 85.00,
        ]);

        // Order 3: PROCESSING (Payment received, being prepared)
        $o3 = Order::create([
            'order_number' => 'ORDR-' . now()->format('Ymd') . '-PROC',
            'user_id' => $customer->id,
            'status' => 'processing',
            'payment_reference' => 'GCASH-REF-' . rand(100000, 999999),
            'total_amount' => 1850.00,
        ]);
        OrderItem::create([
            'order_id' => $o3->order_id,
            'product_id' => $mugBlack->product_id,
            'quantity' => 10,
            'price' => 95.00,
        ]);
        OrderItem::create([
            'order_id' => $o3->order_id,
            'product_id' => $tshirt->product_id,
            'quantity' => 5,
            'price' => 180.00,
        ]);

        // Order 4: READY FOR PICKUP
        $o4 = Order::create([
            'order_number' => 'ORDR-' . now()->format('Ymd') . '-RDY',
            'user_id' => $customer->id,
            'status' => 'ready_for_pickup',
            'payment_reference' => 'GCASH-REF-' . rand(100000, 999999),
            'total_amount' => 2250.00,
        ]);
        OrderItem::create([
            'order_id' => $o4->order_id,
            'product_id' => $plywood->product_id,
            'quantity' => 5,
            'price' => 450.00,
        ]);

        // Order 5: COMPLETED (Historical, picked up last month)
        $o5 = Order::create([
            'order_number' => 'ORDR-20260401-DONE',
            'user_id' => $customer->id,
            'status' => 'completed',
            'payment_reference' => 'GCASH-REF-' . rand(100000, 999999),
            'total_amount' => 45000.00,
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth()->addDays(3),
        ]);
        OrderItem::create([
            'order_id' => $o5->order_id,
            'product_id' => $sofa->product_id,
            'quantity' => 1,
            'price' => 45000.00,
        ]);

        // Order 6: CANCELLED (Customer changed their mind)
        $o6 = Order::create([
            'order_number' => 'ORDR-' . now()->format('Ymd') . '-CXL',
            'user_id' => $customer->id,
            'status' => 'cancelled',
            'reason' => 'Customer requested cancellation before approval.',
            'total_amount' => 170.00,
        ]);
        OrderItem::create([
            'order_id' => $o6->order_id,
            'product_id' => $mugWhite->product_id,
            'quantity' => 2,
            'price' => 85.00,
        ]);
    }
}
