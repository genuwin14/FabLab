<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Texture;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

/**
 * Textures are named after their ambientCG asset ID — Fabric083, Leather037 —
 * because that is what identifies the material the images came from, and it
 * keeps the row and the downloaded folder obviously the same thing.
 *
 * Each row's Color map ships with the repo in public/img/texture and is copied
 * onto the public disk as the row is created, so a rebuilt database comes up
 * with every finish already rendering. All six used to be a manual upload
 * through Admin → Textures after every migrate:fresh.
 *
 * The file is matched to the row by ambientCG's own naming — <name>_1K-JPG_Color
 * .jpg — which is the other reason the rows are named after the asset ID. Only
 * the Color map, and it must be seamless: the customizer tiles a texture across
 * the model, so a plain photograph shows its grid.
 *
 * A row whose file is missing is still created, just without an image, so a
 * checkout that skipped the assets seeds rather than fails.
 *
 * The stock figures below are deliberately varied: some healthy, one under its
 * threshold to exercise low-stock alerts, and one with no department so the
 * materials report still has an "Uncategorized" section to show.
 */
class TextureSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $techSupply = $suppliers->firstWhere('name', 'Tech Supply Co.');
        $genMerch = $suppliers->firstWhere('name', 'General Merchandise Inc.');
        $ecoMaterials = $suppliers->firstWhere('name', 'Eco-Materials Ltd.');

        $textures = [
            // Healthy stock, standard pricing
            [
                'name' => 'Leather037',
                'description' => 'Fine-grain leather. Seamless.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 120.00,
                'stock_quantity' => 80,
                'units_on_display' => 2,
                'units_sponsored' => 0,
                'units_damaged' => 1,
                'units_consumed' => 17,
                'low_stock_threshold' => 15,
                'unit' => 'meter',
                'price_modifier' => 50.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            [
                'name' => 'Fabric077',
                'description' => 'Plain woven cloth. Seamless.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 95.00,
                'stock_quantity' => 60,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 9,
                'low_stock_threshold' => 10,
                'unit' => 'meter',
                'price_modifier' => 0,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Low stock — should appear in inventory alerts
            [
                'name' => 'Leather034C',
                'description' => 'Dark tooled leather. Seamless.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 130.00,
                'stock_quantity' => 8,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 2,
                'units_consumed' => 0,
                'low_stock_threshold' => 12,
                'unit' => 'meter',
                'price_modifier' => 75.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Healthy fabric
            [
                'name' => 'Fabric083',
                'description' => 'Checked cotton weave. Seamless.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 65.00,
                'stock_quantity' => 120,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 25,
                'unit' => 'meter',
                'price_modifier' => 0,
                // No department — demonstrates "Uncategorized" section in report.
            ],
            [
                'name' => 'Fabric080',
                'description' => 'Ribbed knit. Seamless.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 78.00,
                'stock_quantity' => 95,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 4,
                'low_stock_threshold' => 20,
                'unit' => 'meter',
                'price_modifier' => 40.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Premium / very low stock
            [
                'name' => 'Fabric062',
                'description' => 'Heavy canvas. Seamless.',
                'supplier_id' => $techSupply?->supplier_id,
                'cost_per_unit' => 220.00,
                'stock_quantity' => 4,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 10,
                'unit' => 'meter',
                'price_modifier' => 100.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
        ];

        foreach ($textures as $texture) {
            $texture['image_path'] = $this->seedImage($texture['name']);

            Texture::create($texture);
        }
    }

    /**
     * Copy a texture's shipped Color map onto the public disk and return the
     * path to store, or null when the asset isn't there.
     *
     * The images live in public/img/texture because they are part of the repo
     * rather than something a user uploaded, but the column is read as a path on
     * the public disk — see App\Support\ImageUrl — so they are copied across
     * instead of being pointed at where they sit. That keeps one rule for every
     * image in the app, and leaves an admin free to replace any of them through
     * the usual upload without this becoming a special case.
     *
     * Already-copied files are left alone: the seeder runs on every test that
     * builds the demo data, and there is no reason to rewrite six JPEGs each
     * time.
     */
    private function seedImage(string $name): ?string
    {
        $file = $name . '_1K-JPG_Color.jpg';
        $source = public_path('img/texture/' . $file);

        if (! is_file($source)) {
            return null;
        }

        $path = 'textures/' . $file;

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents($source));
        }

        return $path;
    }
}
