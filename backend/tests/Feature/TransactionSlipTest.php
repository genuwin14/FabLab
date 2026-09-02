<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\TransactionSlip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The slip's paper is cut to its content: TransactionSlip computes the page
 * height from measured constants, so anything the template gains has to be
 * bought back there or the last line spills onto a second page — which is
 * exactly what a mismeasure looks like and why these render real PDFs and
 * count the pages.
 */
class TransactionSlipTest extends TestCase
{
    use RefreshDatabase;

    private function order(?string $contact, array $productNames): Order
    {
        $user = User::create([
            'fullname' => 'Slip Customer',
            'email' => 'slip@example.test',
            'password' => 'password',
            'role' => 'customer',
            'contact_number' => $contact,
            'phone_verified' => (bool) $contact,
        ]);

        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        $order = Order::create([
            'order_number' => 'ORDR-20260902-0001',
            'user_id' => $user->id,
            'status' => 'approved',
            'total_amount' => 100 * count($productNames),
        ]);

        foreach ($productNames as $i => $name) {
            $product = Product::create([
                'sku' => 'SLIP-' . $i, 'name' => $name, 'price' => 100, 'stock' => 5, 'unit' => 'pcs',
                'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
            ]);

            OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $product->product_id,
                'quantity' => 1,
                'price' => 100,
            ]);
        }

        return $order->refresh();
    }

    /** "/Type /Page" marks each page object; the \b keeps "/Type /Pages" out. */
    private function pageCount(Order $order): int
    {
        return preg_match_all('~/Type\s*/Page\b~', TransactionSlip::pdf($order)->output());
    }

    public function test_the_slip_prints_the_contact_number_on_one_page(): void
    {
        $order = $this->order('09171234567', ['Plain Mug']);

        $html = view('emails.orders.transaction-slip', ['order' => $order])->render();
        $this->assertStringContainsString('09171234567', $html);

        $this->assertSame(1, $this->pageCount($order));
    }

    public function test_a_customer_without_a_number_gets_no_contact_line(): void
    {
        $order = $this->order(null, ['Plain Mug']);

        $html = view('emails.orders.transaction-slip', ['order' => $order])->render();
        $this->assertStringNotContainsString('Contact:', $html);

        $this->assertSame(1, $this->pageCount($order));
    }

    public function test_wrapping_product_names_and_the_contact_line_still_fit_one_page(): void
    {
        // Long names exercise the greedy-wrap mirror in height(); several of
        // them plus the contact line is the tallest slip the numbers allow for.
        $order = $this->order('09171234567', [
            'Personalized Heavyweight Natural Canvas Tote Bag',
            'White Piqué Polo Shirt With Embroidered Chest Crest',
            'Sublimation White Ceramic Mug 11oz Gift-Boxed Edition',
            'Auto-Open Umbrella Full-Canopy Custom Print',
        ]);

        $this->assertSame(1, $this->pageCount($order));
    }
}
