<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Several foreign keys cascade on delete, so removing a category or supplier
 * that is still in use quietly destroys products, order lines and purchase
 * history. These deletes must be refused while dependants exist.
 */
class CatalogDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));
    }

    private function supplier(): Supplier
    {
        return Supplier::create(['name' => 'Supplier', 'email' => 's@example.test']);
    }

    public function test_a_category_holding_products_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Apparel', 'description' => 'x']);
        Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 5, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);

        $this->delete("/admin/categories/{$category->category_id}")->assertSessionHas('error');

        $this->assertNotNull(Category::find($category->category_id));
    }

    /** Soft-deleted products still hold the foreign key, so they still block it. */
    public function test_a_category_holding_only_soft_deleted_products_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Apparel', 'description' => 'x']);
        $product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 5, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);
        $product->delete();

        $this->delete("/admin/categories/{$category->category_id}")->assertSessionHas('error');

        $this->assertNotNull(Category::find($category->category_id));
    }

    public function test_an_empty_category_is_deleted(): void
    {
        $category = Category::create(['name' => 'Empty', 'description' => 'x']);

        $this->delete("/admin/categories/{$category->category_id}")->assertSessionHas('success');

        $this->assertNull(Category::find($category->category_id));
    }

    public function test_a_supplier_with_purchase_orders_cannot_be_deleted(): void
    {
        $supplier = $this->supplier();
        PurchaseOrder::create([
            'po_number' => 'PO-1', 'supplier_id' => $supplier->supplier_id, 'status' => 'draft',
            'total_cost' => 100, 'created_by' => auth()->id(),
        ]);

        $this->delete("/admin/suppliers/{$supplier->supplier_id}")->assertSessionHas('error');

        $this->assertNotNull(Supplier::find($supplier->supplier_id));
    }

    public function test_a_supplier_with_raw_materials_cannot_be_deleted(): void
    {
        $supplier = $this->supplier();
        RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 50, 'low_stock_threshold' => 5, 'unit' => 'm',
        ]);

        $this->delete("/admin/suppliers/{$supplier->supplier_id}")->assertSessionHas('error');

        $this->assertNotNull(Supplier::find($supplier->supplier_id));
    }

    public function test_an_unused_supplier_is_deleted(): void
    {
        $supplier = $this->supplier();

        $this->delete("/admin/suppliers/{$supplier->supplier_id}")->assertSessionHas('success');

        $this->assertNull(Supplier::find($supplier->supplier_id));
    }

    public function test_a_raw_material_on_a_purchase_order_cannot_be_deleted(): void
    {
        $supplier = $this->supplier();
        $material = RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 50, 'low_stock_threshold' => 5, 'unit' => 'm',
        ]);
        $po = PurchaseOrder::create([
            'po_number' => 'PO-1', 'supplier_id' => $supplier->supplier_id, 'status' => 'delivered',
            'total_cost' => 100, 'created_by' => auth()->id(),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->purchase_order_id,
            'raw_material_id' => $material->raw_material_id,
            'quantity' => 10, 'cost' => 10,
        ]);

        $this->delete("/admin/raw-materials/{$material->raw_material_id}")->assertSessionHas('error');

        $this->assertNotNull(RawMaterial::find($material->raw_material_id));
        $this->assertNotNull(PurchaseOrderItem::where('raw_material_id', $material->raw_material_id)->first());
    }

    public function test_a_texture_on_a_purchase_order_cannot_be_deleted(): void
    {
        $supplier = $this->supplier();
        $texture = Texture::create([
            'name' => 'Weave', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 5,
            'stock_quantity' => 20, 'low_stock_threshold' => 2, 'unit' => 'pcs', 'price_modifier' => 0,
        ]);
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2', 'supplier_id' => $supplier->supplier_id, 'status' => 'sent',
            'total_cost' => 50, 'created_by' => auth()->id(),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->purchase_order_id,
            'texture_id' => $texture->texture_id,
            'quantity' => 10, 'cost' => 5,
        ]);

        $this->delete("/admin/textures/{$texture->texture_id}")->assertSessionHas('error');

        $this->assertNotNull(Texture::find($texture->texture_id));
    }
}
