<?php

namespace Tests\Feature;

use App\Models\Product;
use ReflectionClass;
use Tests\TestCase;

/**
 * Guards the wiring a new 3D shape has to be threaded through.
 *
 * Adding one is not a single edit. Product::customizerShape() decides which
 * model a product opens in, but the studio only has that model if a loader
 * exists for it, the GLB is on disk, and every page that boots the viewer pulls
 * the loader in. Miss the last one and the failure is silent and partial: the
 * customizer works, then the same design previews as a bare t-shirt in the
 * staff order screen because that blade never loaded the file. The polo was
 * added with exactly that gap in one of the four blades.
 */
class CustomizerShapeWiringTest extends TestCase
{
    /** Every page that calls init() and therefore needs the loaders. */
    private const STUDIO_VIEWS = [
        'resources/views/customer/prod-customize/components/scripts.blade.php',
        'resources/views/customer/prod-customize/my-designs.blade.php',
        'resources/views/admin/order/order.blade.php',
        'resources/views/staff/order/order.blade.php',
    ];

    /**
     * The shape name the model matches on, and the GLB it renders.
     *
     * Written out rather than derived, so swapping a file out has to be a
     * deliberate edit here too.
     */
    private const SHAPE_ASSETS = [
        'mug' => 'cup.glb',
        'polo' => 'polo.glb',
        't-shirt' => 't-shirt.glb',
        'umbrella' => 'umbreella_open.glb',
        'bag' => 'tote_bag.glb',
    ];

    private function shapes(): array
    {
        $shapes = (new ReflectionClass(Product::class))->getConstant('CUSTOMIZER_SHAPES');
        $this->assertNotEmpty($shapes, 'Product::CUSTOMIZER_SHAPES should list the renderable shapes.');

        return $shapes;
    }

    public function test_every_offered_shape_has_a_loader(): void
    {
        foreach ($this->shapes() as $shape) {
            $this->assertArrayHasKey(
                $shape,
                self::SHAPE_ASSETS,
                "\"{$shape}\" is offered as a customizer shape but this test does not know which GLB it renders."
            );

            $this->assertFileExists(
                base_path("public/js/customizer/models/{$shape}.js"),
                "\"{$shape}\" is offered as a customizer shape but has no loader script."
            );
        }
    }

    public function test_every_offered_shape_has_its_model_file(): void
    {
        // public/gbl is gitignored — the GLBs run to tens of megabytes each and
        // are provisioned per environment rather than committed. So this can
        // only check a machine that actually has them; on a fresh clone there is
        // nothing to check rather than something to fail.
        if (glob(base_path('public/gbl/*.glb')) === []) {
            $this->markTestSkipped('No GLBs provisioned in public/gbl on this machine.');
        }

        foreach ($this->shapes() as $shape) {
            $this->assertFileExists(
                base_path('public/gbl/' . self::SHAPE_ASSETS[$shape]),
                "\"{$shape}\" is offered as a customizer shape but " . self::SHAPE_ASSETS[$shape]
                    . ' is not in public/gbl, so it renders as a placeholder box.'
            );
        }
    }

    public function test_every_shape_loader_is_pulled_into_every_studio_view(): void
    {
        foreach ($this->shapes() as $shape) {
            foreach (self::STUDIO_VIEWS as $view) {
                $this->assertStringContainsString(
                    "js/customizer/models/{$shape}.js",
                    file_get_contents(base_path($view)),
                    "{$view} boots the 3D viewer but never loads the \"{$shape}\" model, so a {$shape} renders there as the default t-shirt."
                );
            }
        }
    }

    public function test_the_shape_switcher_offers_every_shape_it_can_build(): void
    {
        // The picker is only shown when the customizer is opened without a
        // product, so a shape missing from it is reachable by product but not
        // by hand — the studio quietly offers less than it can render.
        $panel = file_get_contents(base_path('resources/views/customer/prod-customize/components/control-panel.blade.php'));
        $handlers = file_get_contents(base_path('public/js/customizer/handlers.js'));
        $core = file_get_contents(base_path('public/js/customizer/core.js'));

        foreach ($this->shapes() as $shape) {
            $this->assertStringContainsString("data-shape=\"{$shape}\"", $panel, "The base-style picker has no \"{$shape}\" button.");
            $this->assertStringContainsString("'{$shape}'", $handlers, "Clicking the \"{$shape}\" button builds nothing.");
            $this->assertStringContainsString("'{$shape}'", $core, "init() cannot open a design that starts on \"{$shape}\".");
        }
    }
}
