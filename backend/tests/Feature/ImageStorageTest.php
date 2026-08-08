<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Images are files on the public disk, with the row keeping the path. Rows
 * written when images were base64 data URIs still render, so both kinds live
 * together until `images:offload` converts the old ones.
 */
class ImageStorageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);

        $this->category = Category::create(['name' => 'Cat', 'description' => 'x']);
        Sanctum::actingAs($this->admin);
    }

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Shirt', 'sku' => 'P-' . uniqid(), 'category_id' => $this->category->category_id,
            'price' => 100, 'stock' => 5, 'unit' => 'pcs', 'low_stock_threshold' => 1,
        ], $overrides);
    }

    public function test_an_uploaded_product_image_becomes_a_file(): void
    {
        $this->post('/admin/products', $this->productPayload([
            'image_file' => UploadedFile::fake()->image('shirt.jpg'),
        ]))->assertRedirect();

        $product = Product::firstOrFail();

        $this->assertStringStartsWith('products/', $product->image);
        $this->assertStringNotContainsString('base64', $product->image);
        Storage::disk('public')->assertExists($product->image);
        $this->assertStringContainsString('/storage/' . $product->image, $product->image_url);
    }

    public function test_replacing_an_image_removes_the_old_file(): void
    {
        $this->post('/admin/products', $this->productPayload([
            'image_file' => UploadedFile::fake()->image('first.jpg'),
        ]));

        $product = Product::firstOrFail();
        $first = $product->image;

        $this->put("/admin/products/{$product->product_id}", $this->productPayload([
            'sku' => $product->sku,
            'image_file' => UploadedFile::fake()->image('second.jpg'),
        ]))->assertRedirect();

        $product->refresh();

        $this->assertNotSame($first, $product->image);
        Storage::disk('public')->assertExists($product->image);
        Storage::disk('public')->assertMissing($first);
    }

    public function test_textures_and_raw_materials_store_files_too(): void
    {
        $supplier = Supplier::create(['name' => 'Acme', 'email' => 'acme@example.test']);

        $this->post('/admin/textures', [
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => 10,
            'low_stock_threshold' => 2, 'unit' => 'pcs', 'price_modifier' => 0,
            'image_file' => UploadedFile::fake()->image('weave.png'),
        ])->assertRedirect();

        $this->post('/admin/raw-materials', [
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 50, 'low_stock_threshold' => 5, 'unit' => 'meter',
            'image_file' => UploadedFile::fake()->image('fabric.png'),
        ])->assertRedirect();

        $this->assertStringStartsWith('textures/', Texture::firstOrFail()->image_path);
        $this->assertStringStartsWith('raw-materials/', RawMaterial::firstOrFail()->image_path);
        Storage::disk('public')->assertExists(Texture::firstOrFail()->image_path);
        Storage::disk('public')->assertExists(RawMaterial::firstOrFail()->image_path);
    }

    public function test_legacy_inline_images_still_render(): void
    {
        $inline = 'data:image/png;base64,iVBORw0KGgo=';

        $product = Product::create($this->productPayload(['image' => $inline]));

        // Handed back untouched, so old rows keep working.
        $this->assertSame($inline, $product->image_url);
    }

    public function test_a_missing_image_has_no_url(): void
    {
        $product = Product::create($this->productPayload());

        $this->assertNull($product->image_url);
    }

    public function test_serialised_models_carry_the_url(): void
    {
        $product = Product::create($this->productPayload(['image' => 'products/x.jpg']));

        $json = json_decode($product->toJson(), true);

        $this->assertArrayHasKey('image_url', $json);
        $this->assertStringContainsString('/storage/products/x.jpg', $json['image_url']);
    }

    public function test_the_offload_command_converts_inline_rows(): void
    {
        $png = base64_encode(file_get_contents(__DIR__ . '/../../public/FABLAB-LOGO.png'));

        $product = Product::create($this->productPayload([
            'image' => 'data:image/png;base64,' . $png,
        ]));

        $this->artisan('images:offload')->assertSuccessful();

        $product->refresh();

        $this->assertStringStartsWith('products/', $product->image);
        $this->assertStringEndsWith('.png', $product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_the_offload_dry_run_changes_nothing(): void
    {
        $inline = 'data:image/png;base64,' . base64_encode('not-a-real-png-but-decodable');

        $product = Product::create($this->productPayload(['image' => $inline]));

        $this->artisan('images:offload --dry-run')->assertSuccessful();

        $this->assertSame($inline, $product->refresh()->image);
    }
}
