<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * One order parked at each stage of the Purchase Request path, so every screen
 * has something to show without anyone having to walk an order through CSPC
 * procurement by hand.
 *
 * Kept apart from OrderSeeder so the PR demo data can be laid down on its own,
 * without a second copy of the cashier orders coming with it.
 */
class PurchaseRequestOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();
        if (! $customer) {
            return;
        }

        $products = Product::all();
        $mugBlack = $products->firstWhere('sku', 'MG-BLK-11');
        $idLace = $products->firstWhere('sku', 'IDL-LACE-STD');
        $plaque = $products->firstWhere('sku', 'WD-PLQ-OAK');

        if (! $mugBlack || ! $idLace || ! $plaque) {
            return;
        }

        // Already laid down — running twice would just pile up demo rows.
        if (Order::where('payment_method', Order::METHOD_PR)->exists()) {
            $this->command?->info('Purchase Request demo orders already present; nothing seeded.');

            return;
        }

        // Waiting on the customer to come back from procurement.
        $awaiting = Order::create([
            'order_number' => Order::nextOrderNumber(),
            'user_id' => $customer->id,
            'status' => 'awaiting_pr',
            'payment_method' => Order::METHOD_PR,
            'pr_deadline' => now()->addDays((int) config('fablab.pr_deadline_days'))->endOfDay(),
            'total_amount' => 3400.00,
        ]);
        OrderItem::create([
            'order_id' => $awaiting->order_id,
            'product_id' => $mugBlack->product_id,
            'quantity' => 40,
            'price' => 85.00,
        ]);

        // Number in hand and approved — the admin's NOA is what starts it.
        $awaitingNoa = Order::create([
            'order_number' => Order::nextOrderNumber(),
            'user_id' => $customer->id,
            'status' => 'approved',
            'payment_method' => Order::METHOD_PR,
            'pr_number' => 'PR-2026-0117',
            'pr_deadline' => now()->subDays(2)->endOfDay(),
            'total_amount' => 7500.00,
        ]);
        OrderItem::create([
            'order_id' => $awaitingNoa->order_id,
            'product_id' => $idLace->product_id,
            'quantity' => 50,
            'price' => 150.00,
        ]);

        // In production, waiting on the PO to release delivery.
        $awaitingPo = Order::create([
            'order_number' => Order::nextOrderNumber(),
            'user_id' => $customer->id,
            'status' => 'processing',
            'payment_method' => Order::METHOD_PR,
            'pr_number' => 'PR-2026-0104',
            'pr_deadline' => now()->subDays(9)->endOfDay(),
            'noa_path' => $this->placeholder('NOA', 'PR-2026-0104'),
            'total_amount' => 5800.00,
        ]);
        OrderItem::create([
            'order_id' => $awaitingPo->order_id,
            'product_id' => $plaque->product_id,
            'quantity' => 4,
            'price' => 1450.00,
        ]);

        // Both documents in, out for delivery — staff's one step is left.
        $forDelivery = Order::create([
            'order_number' => Order::nextOrderNumber(),
            'user_id' => $customer->id,
            'status' => 'for_delivery',
            'payment_method' => Order::METHOD_PR,
            'pr_number' => 'PR-2026-0098',
            'pr_deadline' => now()->subDays(16)->endOfDay(),
            'noa_path' => $this->placeholder('NOA', 'PR-2026-0098'),
            'po_path' => $this->placeholder('PO', 'PR-2026-0098'),
            'total_amount' => 2550.00,
        ]);
        OrderItem::create([
            'order_id' => $forDelivery->order_id,
            'product_id' => $mugBlack->product_id,
            'quantity' => 30,
            'price' => 85.00,
        ]);
    }

    /**
     * A stand-in document on the private disk, so the admin's view links open
     * something real rather than a 404. Hand-rolled rather than rendered —
     * pulling in the PDF engine to seed demo data isn't worth it.
     */
    private function placeholder(string $kind, string $prNumber): string
    {
        $text = "{$kind} · {$prNumber} · FabLab sample document";
        $body = "BT /F1 13 Tf 60 730 Td ({$text}) Tj ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length ' . strlen($body) . " >>\nstream\n{$body}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";

        $path = 'orders/samples/' . strtolower($kind) . '-' . strtolower(str_replace('-', '', $prNumber)) . '.pdf';
        Storage::disk('local')->put($path, $pdf);

        return $path;
    }
}
