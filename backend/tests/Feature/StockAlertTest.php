<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Stock alerts used to fire for products only, so a raw material or texture
 * could run dry with nobody told. They now cover all three, and still only
 * fire on the crossing rather than on every movement below the line.
 */
class StockAlertTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09123456789', 'phone_verified' => true,
            'status' => 'active', 'notifications_enabled' => true,
        ]);

        Notification::fake();
    }

    private function material(float $stock = 100, float $threshold = 10): RawMaterial
    {
        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 'sup@example.test']);

        return RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => $stock, 'low_stock_threshold' => $threshold, 'unit' => 'm',
        ]);
    }

    private function texture(float $stock = 50, float $threshold = 5): Texture
    {
        return Texture::create([
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => $stock,
            'low_stock_threshold' => $threshold, 'unit' => 'pcs', 'price_modifier' => 0,
        ]);
    }

    private function product(int $stock = 30, int $threshold = 5): Product
    {
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        return Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => $stock, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'low_stock_threshold' => $threshold,
        ]);
    }

    public function test_a_raw_material_crossing_its_threshold_alerts_the_team(): void
    {
        $material = $this->material(100, 10);

        $material->decrement('stock_quantity', 92); // 100 -> 8

        Notification::assertSentTo($this->staff, LowStockAlert::class, function ($notification) use ($material) {
            $data = $notification->toArray($this->staff);

            return $data['item_type'] === 'Raw Material'
                && str_contains($data['title'], 'Fabric')
                && str_contains($data['body'], '8 m');
        });
    }

    public function test_a_texture_running_out_alerts_the_team(): void
    {
        $texture = $this->texture(50, 5);

        $texture->decrement('stock_quantity', 50); // 50 -> 0

        Notification::assertSentTo($this->staff, OutOfStockAlert::class, function ($notification) {
            return $notification->toArray($this->staff)['item_type'] === 'Texture';
        });
    }

    public function test_products_still_alert(): void
    {
        $product = $this->product(30, 5);

        $product->decrement('stock', 26); // 30 -> 4

        Notification::assertSentTo($this->staff, LowStockAlert::class, function ($notification) {
            return $notification->toArray($this->staff)['item_type'] === 'Product';
        });
    }

    public function test_no_second_alert_while_already_below_the_line(): void
    {
        $material = $this->material(8, 10); // already under

        $material->decrement('stock_quantity', 1); // 8 -> 7, still under

        Notification::assertNothingSent();
    }

    public function test_restocking_raises_nothing(): void
    {
        $material = $this->material(5, 10);

        $material->increment('stock_quantity', 100);

        Notification::assertNothingSent();
    }
}
