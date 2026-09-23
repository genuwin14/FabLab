<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Reports\OrdersExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The Export button on All Orders and on the staff order list did nothing.
 * It now downloads the orders the list's filters are showing — every page
 * of them — as a PDF, a Word document or a CSV for Excel.
 */
class OrderExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->user('admin', 'a@example.test');
        $this->staff = $this->user('staff', 's@example.test');
        $this->customer = $this->user('customer', 'c@example.test');

        $category = Category::create(['name' => 'Mugs', 'description' => 'x']);
        $this->product = Product::create([
            'name' => 'Black Mug', 'sku' => 'MG-1', 'category_id' => $category->category_id,
            'price' => 95, 'stock' => 20, 'is_customizable' => false,
        ]);
    }

    public static function formats(): array
    {
        return [
            'pdf' => ['pdf', 'application/pdf'],
            'word' => ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'csv' => ['csv', 'text/csv; charset=UTF-8'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formats')]
    public function test_all_orders_downloads_in_each_format(string $format, string $type): void
    {
        $this->order();
        Sanctum::actingAs($this->admin);

        $response = $this->get(route('admin.orders.export', ['format' => $format]))->assertOk();

        $this->assertSame($type, $response->headers->get('content-type'));
        $this->assertStringContainsString("orders-" . now()->format('Y-m-d') . ".{$format}", $response->headers->get('content-disposition'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formats')]
    public function test_the_staff_list_downloads_too(string $format): void
    {
        $this->order();
        Sanctum::actingAs($this->staff);

        $this->get(route('staff.orders.export', ['format' => $format]))->assertOk();
    }

    public function test_the_list_pages_link_to_the_export_with_their_filters(): void
    {
        Sanctum::actingAs($this->admin);

        $this->get(route('admin.orders.index', ['status' => 'pending', 'search' => 'Registrar']))
            ->assertOk()
            ->assertSee(route('admin.orders.export', ['format' => 'csv', 'search' => 'Registrar', 'status' => 'pending']));

        Sanctum::actingAs($this->staff);

        $this->get(route('staff.orders.index'))
            ->assertOk()
            ->assertSee(route('staff.orders.export', ['format' => 'pdf']));
    }

    public function test_a_customer_cannot_export_the_orders(): void
    {
        Sanctum::actingAs($this->customer);

        $this->get(route('admin.orders.export', ['format' => 'csv']))->assertRedirect(route('customer.shop'));
        $this->get(route('staff.orders.export', ['format' => 'csv']))->assertRedirect(route('customer.shop'));
    }

    public function test_an_unknown_format_is_not_found(): void
    {
        Sanctum::actingAs($this->admin);

        $this->get('/admin/orders/export/xlsx')->assertNotFound();
    }

    public function test_the_export_takes_every_page_the_filters_show(): void
    {
        foreach (range(1, 12) as $i) {
            $this->order(['order_number' => sprintf('ORDR-PEND-%02d', $i)]);
        }
        $this->order(['order_number' => 'ORDR-DONE-01', 'status' => 'completed']);

        Sanctum::actingAs($this->admin);

        // The screen shows ten a page; the file holds all twelve.
        $this->assertSame(10, $this->get(route('admin.orders.index', ['status' => 'pending']))->viewData('orders')->count());

        $csv = $this->csvRows(['status' => 'pending']);

        $this->assertCount(13, $csv, 'a header and twelve orders');
        $this->assertNotContains('ORDR-DONE-01', array_column($csv, 0));
    }

    public function test_the_csv_says_who_each_order_is_for_and_how_it_is_paid(): void
    {
        $order = $this->order([
            'order_number' => 'ORDR-PR-01',
            'payment_method' => Order::METHOD_PR,
            'office' => 'Office of the Registrar',
            'pr_number' => 'PR-2026-0117',
            'total_amount' => 190,
        ], quantity: 2);
        $order->forceFill(['created_at' => '2026-09-23 17:52:00'])->save();

        Sanctum::actingAs($this->admin);

        $csv = $this->csvRows();

        $this->assertSame(
            ['Order No.', 'Date', 'Customer', 'Ordered For', 'Paid Through', 'Items', 'Receipt / PR No.', 'Status', 'Total (PHP)'],
            $csv[0]
        );
        $this->assertSame(
            ['ORDR-PR-01', '2026-09-23', 'Customer', 'Office of the Registrar', 'Procurement', 'Black Mug × 2', 'PR-2026-0117', 'Pending', '190.00'],
            $csv[1]
        );
    }

    public function test_the_csv_opens_in_excel_as_utf8(): void
    {
        $this->order();
        Sanctum::actingAs($this->admin);

        $content = $this->get(route('admin.orders.export', ['format' => 'csv']))->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
    }

    public function test_a_formula_typed_into_an_office_stays_text_in_excel(): void
    {
        $this->order(['office' => '=HYPERLINK("http://example.test","Open")']);
        Sanctum::actingAs($this->admin);

        $this->assertSame("'=HYPERLINK(\"http://example.test\",\"Open\")", $this->csvRows()[1][3]);
    }

    public function test_an_ampersand_in_an_office_leaves_the_word_file_readable(): void
    {
        $this->order(['office' => 'Planning & Development <Office>']);
        Sanctum::actingAs($this->admin);

        $response = $this->get(route('admin.orders.export', ['format' => 'docx']))->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $document = new \DOMDocument();
        $this->assertTrue(@$document->loadXML($xml), 'Word would call this file corrupt');
        $this->assertStringContainsString('Planning &amp; Development &lt;Office&gt;', $xml);
    }

    public function test_the_value_leaves_cancelled_orders_out(): void
    {
        $this->order(['total_amount' => 100]);
        $this->order(['total_amount' => 250, 'status' => 'completed']);
        $this->order(['total_amount' => 999, 'status' => 'cancelled']);

        $report = app(OrdersExport::class)->build(Request::create('/'));

        $this->assertSame(3, $report['count']);
        $this->assertSame(1, $report['cancelledCount']);
        $this->assertSame(350.0, $report['openValue']);
    }

    public function test_the_heading_spells_out_the_filters(): void
    {
        Carbon::setTestNow('2026-09-23 10:00:00');

        $label = fn (array $query) => app(OrdersExport::class)->build(Request::create('/', 'GET', $query))['filterLabel'];

        $this->assertSame('All statuses · All time', $label([]));
        $this->assertSame('Awaiting PR · This month (September 2026)', $label(['status' => 'awaiting_pr', 'date' => 'month']));
        $this->assertSame('All statuses · This week (September 21–27, 2026) · Search: “Registrar”', $label(['date' => 'week', 'search' => 'Registrar']));
        $this->assertSame('Pending · Today (September 23, 2026)', $label(['status' => 'pending', 'date' => 'today']));
    }

    public function test_a_retired_product_is_still_named_in_the_export(): void
    {
        $this->order();
        $this->product->delete();

        Sanctum::actingAs($this->admin);

        $this->assertSame('Black Mug × 1', $this->csvRows()[1][5]);
    }

    // ---------------------------------------------------------------- helpers

    /** @return array<int, array<int, string>> */
    private function csvRows(array $query = []): array
    {
        $content = $this->get(route('admin.orders.export', ['format' => 'csv'] + $query))
            ->assertOk()
            ->streamedContent();

        $lines = preg_split('/\r\n|\n/', trim(substr($content, 3)));

        return array_map(fn ($line) => str_getcsv($line, ',', '"', ''), $lines);
    }

    private function order(array $attributes = [], int $quantity = 1): Order
    {
        $order = Order::create($attributes + [
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_method' => Order::METHOD_CASH,
            'total_amount' => 95 * $quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->product->product_id,
            'quantity' => $quantity,
            'price' => 95,
        ]);

        return $order;
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
