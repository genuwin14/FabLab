<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Product;
use App\Models\User;
use App\Services\InkEstimator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Measuring ink off the artwork, and estimating it when there is no artwork.
 *
 * The number that matters is coverage: the fraction of the printable panels
 * each of the printer's four channels lays down. A solid cyan square over
 * half the print is 50% cyan and nothing else; white is no ink; a
 * half-transparent pixel counts half. Designs saved before prints were
 * exported get the same shape of answer estimated from their recipe.
 */
class InkMeasurementTest extends TestCase
{
    use RefreshDatabase;

    private InkEstimator $estimator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->estimator = new InkEstimator();
    }

    /**
     * A PNG data URL, `$side` pixels square, transparent except where
     * `$paint` fills it in. `$paint` receives the GD image.
     */
    private function png(int $side, callable $paint): string
    {
        $image = imagecreatetruecolor($side, $side);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

        $paint($image);

        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($bytes);
    }

    public function test_a_solid_cyan_half_measures_as_half_cyan_and_nothing_else(): void
    {
        $print = $this->png(256, function ($image) {
            imagefilledrectangle($image, 0, 0, 127, 255, imagecolorallocatealpha($image, 0, 255, 255, 0));
        });

        $coverage = $this->estimator->measure($print);

        $this->assertEqualsWithDelta(0.5, $coverage['cyan'], 0.01);
        $this->assertEqualsWithDelta(0.0, $coverage['magenta'], 0.001);
        $this->assertEqualsWithDelta(0.0, $coverage['yellow'], 0.001);
        $this->assertEqualsWithDelta(0.0, $coverage['black'], 0.001);
    }

    public function test_coverage_is_a_fraction_of_the_printable_panels_not_the_canvas(): void
    {
        $print = $this->png(256, function ($image) {
            imagefilledrectangle($image, 0, 0, 127, 255, imagecolorallocatealpha($image, 0, 255, 255, 0));
        });

        // The panels occupy half the canvas, and the cyan fills those panels.
        $this->assertEqualsWithDelta(1.0, $this->estimator->measure($print, 0.5)['cyan'], 0.01);

        // A fraction that would push past 100% is clamped, not honoured.
        $this->assertSame(1.0, $this->estimator->measure($print, 0.1)['cyan']);
    }

    public function test_white_takes_no_ink_and_black_takes_only_black(): void
    {
        $print = $this->png(64, function ($image) {
            imagefilledrectangle($image, 0, 0, 31, 63, imagecolorallocatealpha($image, 255, 255, 255, 0));
            imagefilledrectangle($image, 32, 0, 63, 63, imagecolorallocatealpha($image, 0, 0, 0, 0));
        });

        $coverage = $this->estimator->measure($print);

        $this->assertEqualsWithDelta(0.5, $coverage['black'], 0.01);
        $this->assertEqualsWithDelta(0.0, $coverage['cyan'] + $coverage['magenta'] + $coverage['yellow'], 0.001);
    }

    public function test_a_half_transparent_pixel_counts_half(): void
    {
        $print = $this->png(64, function ($image) {
            // Alpha 63 of 127 is about half opaque.
            imagefilledrectangle($image, 0, 0, 63, 63, imagecolorallocatealpha($image, 0, 0, 0, 63));
        });

        $this->assertEqualsWithDelta(0.5, $this->estimator->measure($print)['black'], 0.02);
    }

    public function test_something_that_is_not_an_image_measures_as_nothing(): void
    {
        $this->assertNull($this->estimator->measure('data:image/png;base64,AAA'));
        $this->assertNull($this->estimator->measure('https://example.test/print.png'));
        $this->assertNull($this->estimator->measure(''));
    }

    public function test_an_estimate_knows_red_text_takes_no_cyan(): void
    {
        $coverage = $this->estimator->estimate([
            'elements' => ['text' => [['text' => 'HELLO', 'color' => '#ff0000', 'scale' => 2]], 'shapes' => [], 'logos' => []],
        ]);

        $this->assertSame(0.0, $coverage['cyan']);
        $this->assertSame(0.0, $coverage['black']);
        $this->assertGreaterThan(0, $coverage['magenta']);
        $this->assertEqualsWithDelta($coverage['magenta'], $coverage['yellow'], 0.0001, 'Pure red is equal parts magenta and yellow.');
    }

    public function test_an_estimate_grows_with_the_element_and_ignores_white(): void
    {
        $small = $this->estimator->estimate([
            'elements' => ['shapes' => [['type' => 'circle', 'color' => '#0000ff', 'scale' => 1]], 'text' => [], 'logos' => []],
        ]);
        $large = $this->estimator->estimate([
            'elements' => ['shapes' => [['type' => 'circle', 'color' => '#0000ff', 'scale' => 3]], 'text' => [], 'logos' => []],
        ]);
        $white = $this->estimator->estimate([
            'elements' => ['shapes' => [['type' => 'circle', 'color' => '#ffffff', 'scale' => 5]], 'text' => [], 'logos' => []],
        ]);

        $this->assertGreaterThan($small['cyan'], $large['cyan']);
        $this->assertEqualsWithDelta(9.0, $large['cyan'] / $small['cyan'], 0.05, 'Three times the radius is nine times the area.');
        $this->assertSame(0.0, array_sum($white));
    }

    public function test_an_estimate_reads_the_uploaded_image_itself(): void
    {
        $magenta = $this->png(32, function ($image) {
            imagefilledrectangle($image, 0, 0, 31, 31, imagecolorallocatealpha($image, 255, 0, 255, 0));
        });

        $coverage = $this->estimator->estimate([
            'elements' => ['logos' => [['src' => $magenta, 'scale' => 1]], 'text' => [], 'shapes' => []],
        ]);

        // A 200px square of solid magenta on a 1024² canvas.
        $this->assertEqualsWithDelta((200 * 200) / (1024 * 1024), $coverage['magenta'], 0.001);
        $this->assertSame(0.0, $coverage['cyan']);
        $this->assertSame(0.0, $coverage['yellow']);
    }

    public function test_an_unreadable_image_is_assumed_to_be_full_colour(): void
    {
        $coverage = $this->estimator->estimate([
            'elements' => ['logos' => [['src' => 'data:image/png;base64,AAA', 'scale' => 1]], 'text' => [], 'shapes' => []],
        ]);

        foreach (['cyan', 'magenta', 'yellow', 'black'] as $channel) {
            $this->assertGreaterThan(0, $coverage[$channel]);
        }
    }

    public function test_a_design_uses_its_measured_coverage_when_it_has_one(): void
    {
        [$customer, $product] = $this->customerAndProduct();

        $design = CustomDesign::create([
            'user_id' => $customer->id, 'product_id' => $product->product_id,
            'recipe' => ['elements' => ['text' => [['text' => 'x', 'color' => '#ff0000']]]],
            'ink_coverage' => ['cyan' => 0.2, 'magenta' => 0.1, 'yellow' => 0, 'black' => 0.05],
        ]);

        $answer = $this->estimator->coverageFor($design);
        $this->assertSame('measured', $answer['source']);
        $this->assertSame(0.2, $answer['coverage']['cyan']);

        $design->update(['ink_coverage' => null]);
        $answer = $this->estimator->coverageFor($design->refresh());
        $this->assertSame('estimated', $answer['source']);
        $this->assertSame(0.0, $answer['coverage']['cyan']);
        $this->assertGreaterThan(0, $answer['coverage']['magenta']);
    }

    public function test_saving_from_the_studio_measures_and_keeps_the_print(): void
    {
        Storage::fake('public');
        [$customer, $product] = $this->customerAndProduct();
        Sanctum::actingAs($customer);

        $print = $this->png(128, function ($image) {
            imagefilledrectangle($image, 0, 0, 127, 63, imagecolorallocatealpha($image, 255, 255, 0, 0));
        });

        $this->postJson(route('customer.customize.save'), [
            'product_id' => $product->product_id,
            'custom_recipe' => json_encode(['base_style' => 't-shirt', 'size' => 'large', 'elements' => []]),
            'custom_snapshot' => 'data:image/png;base64,AAA',
            'custom_print' => $print,
            'custom_print_area' => 0.5,
            'custom_print_zones' => json_encode([
                ['id' => 'front', 'label' => 'Front', 'u0' => 0.159, 'v0' => 0.446, 'u1' => 0.392, 'v1' => 0.878],
                ['id' => 'back', 'label' => 'Back', 'u0' => 0.615, 'v0' => 0.447, 'u1' => 0.812, 'v1' => 0.833, 'flipU' => true],
            ]),
        ])->assertOk()->assertJson(['success' => true]);

        $design = CustomDesign::sole();
        $this->assertEqualsWithDelta(1.0, $design->ink_coverage['yellow'], 0.01, 'Yellow fills the half of the canvas that is printable.');
        $this->assertEquals(0, $design->ink_coverage['cyan']);
        Storage::disk('public')->assertExists('designs/prints/' . $design->custom_design_id . '.png');

        // The panels ride along, so the order screens can crop each one out.
        $this->assertCount(2, $design->print_zones);
        $this->assertSame('Front', $design->print_zones[0]['label']);
        $this->assertSame(0.159, $design->print_zones[0]['u0']);
        $this->assertFalse($design->print_zones[0]['flipU']);
        $this->assertTrue($design->print_zones[1]['flipU']);
        $this->assertStringStartsWith('/storage/designs/prints/', $design->print_image_url);

        // And each panel records where its artwork sits. The yellow fills the
        // top half of the canvas, so on the front panel (v 0.446–0.878) it
        // runs from the panel's top edge down to the middle of the canvas.
        $front = $design->print_zones[0]['bbox'];
        $this->assertEqualsWithDelta(0.159, $front['u0'], 0.01);
        $this->assertEqualsWithDelta(0.392, $front['u1'], 0.01);
        $this->assertEqualsWithDelta(0.446, $front['v0'], 0.01);
        $this->assertEqualsWithDelta(0.5, $front['v1'], 0.01);
        $this->assertNotNull($design->print_zones[1]['bbox']);
    }

    public function test_a_panel_with_nothing_on_it_has_no_box(): void
    {
        $zones = [
            ['id' => 'front', 'label' => 'Front', 'u0' => 0.0, 'v0' => 0.0, 'u1' => 0.5, 'v1' => 1.0],
            ['id' => 'back', 'label' => 'Back', 'u0' => 0.5, 'v0' => 0.0, 'u1' => 1.0, 'v1' => 1.0],
        ];

        // A 20px square at (40,40) of a 200px print: entirely on the front.
        $print = $this->png(200, function ($image) {
            imagefilledrectangle($image, 40, 40, 59, 59, imagecolorallocatealpha($image, 0, 0, 0, 0));
        });

        $boxes = $this->estimator->boundingBoxes($print, $zones);

        $this->assertEqualsWithDelta(0.2, $boxes[0]['u0'], 0.01);
        $this->assertEqualsWithDelta(0.3, $boxes[0]['u1'], 0.01);
        $this->assertEqualsWithDelta(0.2, $boxes[0]['v0'], 0.01);
        $this->assertEqualsWithDelta(0.3, $boxes[0]['v1'], 0.01);
        $this->assertNull($boxes[1]);
    }

    public function test_panels_that_do_not_fit_on_the_print_are_dropped(): void
    {
        Storage::fake('public');
        [$customer, $product] = $this->customerAndProduct();
        Sanctum::actingAs($customer);

        $print = $this->png(32, function ($image) {
            imagefilledrectangle($image, 0, 0, 31, 31, imagecolorallocatealpha($image, 0, 0, 0, 0));
        });

        // Re-saves the same design each time, as the studio does once it has an id.
        $post = fn ($zones) => $this->postJson(route('customer.customize.save'), [
            'product_id' => $product->product_id,
            'design_id' => CustomDesign::first()?->custom_design_id,
            'custom_recipe' => json_encode(['elements' => []]),
            'custom_print' => $print,
            'custom_print_zones' => is_string($zones) ? $zones : json_encode($zones),
        ])->assertOk();

        // One good panel among the bad: off the atlas, no area, not an object.
        $post([
            ['label' => 'Front', 'u0' => 0.1, 'v0' => 0.1, 'u1' => 0.5, 'v1' => 0.5],
            ['label' => 'Off', 'u0' => 0.5, 'v0' => 0.5, 'u1' => 1.5, 'v1' => 0.9],
            ['label' => 'Flat', 'u0' => 0.5, 'v0' => 0.5, 'u1' => 0.5, 'v1' => 0.9],
            ['label' => 'Nameless', 'u0' => 'x'],
            'not a panel',
        ]);
        $zones = CustomDesign::sole()->print_zones;
        $this->assertCount(1, $zones);
        $this->assertSame('Front', $zones[0]['label']);

        // Nothing usable at all stores nothing, and the screens show the print whole.
        $post('not json');
        $this->assertNull(CustomDesign::sole()->print_zones);
    }

    public function test_adding_to_the_cart_measures_the_print_too(): void
    {
        Storage::fake('public');
        [$customer, $product] = $this->customerAndProduct();
        Sanctum::actingAs($customer);

        $print = $this->png(64, function ($image) {
            imagefilledrectangle($image, 0, 0, 63, 63, imagecolorallocatealpha($image, 0, 0, 0, 0));
        });

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode(['base_style' => 't-shirt', 'elements' => []]),
            'custom_print' => $print,
            'custom_print_area' => 1,
        ])->assertOk();

        $this->assertEqualsWithDelta(1.0, CustomDesign::sole()->ink_coverage['black'], 0.01);
    }

    public function test_re_saving_without_a_print_keeps_the_measurement(): void
    {
        Storage::fake('public');
        [$customer, $product] = $this->customerAndProduct();
        Sanctum::actingAs($customer);

        $design = CustomDesign::create([
            'user_id' => $customer->id, 'product_id' => $product->product_id,
            'recipe' => ['elements' => []],
            'ink_coverage' => ['cyan' => 0.3, 'magenta' => 0, 'yellow' => 0, 'black' => 0],
            'print_image' => 'designs/prints/kept.png',
        ]);

        // My Designs re-posts the recipe with no print, and so does a
        // corrupt one: neither may wipe what was measured.
        foreach ([null, 'data:image/png;base64,AAA'] as $print) {
            $this->postJson(route('customer.customize.save'), [
                'product_id' => $product->product_id,
                'design_id' => $design->custom_design_id,
                'custom_recipe' => json_encode(['elements' => [], 'size' => 'small']),
                'custom_print' => $print,
            ])->assertOk();

            $this->assertSame(0.3, $design->refresh()->ink_coverage['cyan']);
            $this->assertSame('designs/prints/kept.png', $design->print_image);
        }
    }

    /** @return array{0: User, 1: Product} */
    private function customerAndProduct(): array
    {
        $customer = User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);
        $product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
            'is_customizable' => true, 'print_area_cm2' => 2400,
        ]);

        return [$customer, $product];
    }
}
