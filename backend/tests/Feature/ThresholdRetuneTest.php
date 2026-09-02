<?php

namespace Tests\Feature;

use App\Enums\StockMovementReason;
use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * stock:retune-thresholds sets each item's low-stock line to half of what the
 * previous month drew. The rules worth guarding: each draw counts once even
 * though the ledger writes it twice (reserved, then consumed), only orders
 * that actually took stock count, a quiet item keeps its hand-set line, and
 * an item the new line overtakes is alerted immediately — the stock observer
 * never would, because no later movement can cross a line it is already under.
 */
class ThresholdRetuneTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Carbon $lastMonth;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->customer = User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);

        // Staff to receive alerts.
        User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09123456780', 'phone_verified' => true,
            'status' => 'active', 'notifications_enabled' => true,
        ]);

        $this->lastMonth = now()->subMonthNoOverflow()->startOfMonth()->addDays(10);
    }

    private function material(float $stock = 100, float $threshold = 10): RawMaterial
    {
        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 'sup@example.test']);

        return RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => $stock, 'low_stock_threshold' => $threshold, 'unit' => 'm',
        ]);
    }

    private function product(int $stock = 50, int $threshold = 5): Product
    {
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        return Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => $stock, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'low_stock_threshold' => $threshold,
        ]);
    }

    private function texture(float $stock = 50, float $threshold = 5): Texture
    {
        return Texture::create([
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => $stock,
            'low_stock_threshold' => $threshold, 'unit' => 'pcs', 'price_modifier' => 0,
        ]);
    }

    /** A ledger row written on a chosen day, the way the services write them. */
    private function movement(
        RawMaterial $material,
        StockMovementReason $reason,
        float $quantity,
        ?Carbon $when = null,
        ?int $reverses = null,
    ): RawMaterialMovement {
        $movement = new RawMaterialMovement([
            'raw_material_id' => $material->raw_material_id,
            'reason' => $reason,
            'quantity' => $quantity,
            'stock_delta' => $reason === StockMovementReason::Reversal ? $quantity : -$quantity,
            'stock_after' => (float) $material->stock_quantity,
            'reverses_movement_id' => $reverses,
        ]);

        $movement->created_at = $when ?? $this->lastMonth;
        $movement->save();

        return $movement;
    }

    private function order(Product $product, int $quantity, string $status, ?Carbon $when = null, ?int $designId = null): Order
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => $status,
            'total_amount' => 100 * $quantity,
        ]);

        $order->created_at = $when ?? $this->lastMonth;
        $order->save();

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'custom_design_id' => $designId,
            'quantity' => $quantity,
            'price' => 100,
        ]);

        return $order;
    }

    // ------------------------------------------------------------ materials

    public function test_a_materials_line_follows_last_months_draw(): void
    {
        $material = $this->material(threshold: 10);
        $this->movement($material, StockMovementReason::Consumed, 20);
        $this->movement($material, StockMovementReason::Consumed, 10);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        // 30 drawn -> two weeks of that demand is 15.
        $this->assertEquals(15.0, $material->refresh()->stockThreshold());
    }

    public function test_a_draw_reserved_then_consumed_counts_once(): void
    {
        $material = $this->material(threshold: 10);

        // The chain startProduction() writes: the reservation, its reversal,
        // and the consumption for the same quantity.
        $reserved = $this->movement($material, StockMovementReason::Reserved, 20);
        $this->movement($material, StockMovementReason::Reversal, 20, reverses: $reserved->movement_id);
        $this->movement($material, StockMovementReason::Consumed, 20);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(10.0, $material->refresh()->stockThreshold());
    }

    public function test_a_quiet_item_keeps_its_hand_set_line(): void
    {
        $material = $this->material(threshold: 10);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(10.0, $material->refresh()->stockThreshold());
    }

    public function test_this_months_draw_is_not_last_months(): void
    {
        $material = $this->material(threshold: 10);
        $this->movement($material, StockMovementReason::Consumed, 40, when: now());

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(10.0, $material->refresh()->stockThreshold());
    }

    public function test_write_offs_are_not_demand(): void
    {
        $material = $this->material(threshold: 10);
        $this->movement($material, StockMovementReason::Damaged, 40);
        $this->movement($material, StockMovementReason::Sponsored, 40);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(10.0, $material->refresh()->stockThreshold());
    }

    // ------------------------------------------------------------- products

    public function test_a_products_line_follows_last_months_orders(): void
    {
        $product = $this->product(threshold: 5);
        $this->order($product, 20, 'completed');
        $this->order($product, 10, 'approved');

        // Neither of these ever took stock.
        $this->order($product, 50, 'pending');
        $this->order($product, 50, 'cancelled');

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(15, $product->refresh()->stockThreshold());
    }

    // ------------------------------------------------------------- textures

    public function test_a_textures_line_follows_the_finishes_ordered(): void
    {
        $product = $this->product();
        $texture = $this->texture(threshold: 5);

        $design = CustomDesign::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->product_id,
            'recipe' => [
                'base_style' => 't-shirt',
                'size' => 'medium',
                'texture_id' => $texture->texture_id,
                'elements' => ['text' => [], 'shapes' => [], 'logos' => []],
            ],
        ]);

        $this->order($product, 24, 'completed', designId: $design->custom_design_id);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(12.0, $texture->refresh()->stockThreshold());
    }

    // --------------------------------------------------------------- alerts

    public function test_an_item_the_new_line_overtakes_is_alerted(): void
    {
        // Stock 12, line at 2: quiet. Last month drew 30, so the line moves to
        // 15 — above the shelf. No stock movement will ever cross a line the
        // stock is already under, so the retune has to raise the alert itself.
        $material = $this->material(stock: 12, threshold: 2);
        $this->movement($material, StockMovementReason::Consumed, 30);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        $this->assertEquals(15.0, $material->refresh()->stockThreshold());
        Notification::assertSentTimes(LowStockAlert::class, 1);
    }

    public function test_an_item_still_above_its_new_line_is_not_alerted(): void
    {
        $material = $this->material(stock: 100, threshold: 10);
        $this->movement($material, StockMovementReason::Consumed, 30);

        $this->artisan('stock:retune-thresholds')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
