<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Sales had charts but no way to file the numbers. The exports run off the
 * same SalesReport service as the page, so a document can't disagree with the
 * screen it came from.
 */
class SalesReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function admin(): User
    {
        return $this->admin;
    }

    private function completedSale(float $amount = 250, int $quantity = 2): Order
    {
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);
        $product = Product::create([
            'sku' => 'P-' . uniqid(), 'name' => 'Shirt', 'price' => 125, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);

        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->admin()->id,
            'status' => 'completed',
            'total_amount' => $amount,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id, 'product_id' => $product->product_id,
            'quantity' => $quantity, 'price' => 125,
        ]);

        return $order;
    }

    public function test_the_page_and_the_export_agree(): void
    {
        $this->completedSale(250);
        Sanctum::actingAs($this->admin());

        $page = $this->get('/admin/sales')->assertOk();

        $this->assertSame(250.0, $page->viewData('totalRevenue'));
        $this->assertSame(1, $page->viewData('orderCount'));

        $pdf = $this->get('/admin/sales/pdf')->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringContainsString('sales-report-', $pdf->headers->get('content-disposition'));
    }

    public function test_the_preview_streams_inline(): void
    {
        $this->completedSale();
        Sanctum::actingAs($this->admin());

        $response = $this->get('/admin/sales/preview')->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    public function test_the_word_export_downloads_a_document(): void
    {
        $this->completedSale();
        Sanctum::actingAs($this->admin());

        $response = $this->get('/admin/sales/docx')->assertOk();

        $this->assertStringContainsString(
            'openxmlformats-officedocument.wordprocessingml',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('.docx', $response->headers->get('content-disposition'));
    }

    public function test_the_export_honours_the_selected_range(): void
    {
        // A sale from well before the 7-day window.
        $old = $this->completedSale(999);
        $old->created_at = now()->subDays(30);
        $old->save();

        $this->completedSale(250);
        Sanctum::actingAs($this->admin());

        $sevenDays = $this->get('/admin/sales?range=7days')->assertOk();
        $this->assertSame(250.0, $sevenDays->viewData('totalRevenue'));

        $ninetyDays = $this->get('/admin/sales?range=90days')->assertOk();
        $this->assertSame(1249.0, $ninetyDays->viewData('totalRevenue'));

        // The same filter reaches the export.
        $this->get('/admin/sales/pdf?range=7days')->assertOk();
    }

    public function test_only_completed_orders_count_as_sales(): void
    {
        $pending = $this->completedSale(500);
        $pending->update(['status' => 'pending']);

        Sanctum::actingAs($this->admin());

        $this->assertSame(0.0, $this->get('/admin/sales')->viewData('totalRevenue'));
    }

    public function test_staff_see_the_same_figures_without_the_exports(): void
    {
        $this->completedSale(250);

        Sanctum::actingAs(User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]));

        $this->assertSame(250.0, $this->get('/staff/sales')->viewData('totalRevenue'));

        // Exports stay admin-only.
        $this->get('/admin/sales/pdf')->assertRedirect(route('staff.dashboard'));
    }
}
