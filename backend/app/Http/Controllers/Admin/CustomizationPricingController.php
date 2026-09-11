<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomizationRate;
use App\Models\CustomizationRateMaterial;
use App\Models\InkChannel;
use App\Models\RawMaterial;
use App\Models\TransferSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin screen for what the customizer charges per design element, and what
 * each element costs the shop to make.
 *
 * Only the amounts and the materials are editable — the rate keys are fixed by
 * CustomizationRate::DEFINITIONS because each one is wired into how a design is
 * priced and costed.
 *
 * The two halves belong on one screen because they answer the same question
 * from opposite sides: charging ₱500 for internal lighting is only a margin if
 * you also know it eats an LED strip. Before the materials half existed the
 * fee was collected and nothing left the shelf.
 */
class CustomizationPricingController extends Controller
{
    public function index()
    {
        return view('admin.customization-pricing.index', [
            'rates' => CustomizationRate::forDisplay(),
            'inkChannels' => InkChannel::forDisplay(),
            'transferSheet' => TransferSheet::current(),
            'logoMinScale' => \App\Models\CustomDesign::LOGO_MIN_SCALE,
            'logoMaxScale' => \App\Models\CustomDesign::LOGO_MAX_SCALE,
            // Retired materials are left out: they can't be consumed, so
            // offering one would build a BOM line that silently does nothing.
            'materials' => RawMaterial::orderBy('name')->get(['raw_material_id', 'name', 'unit']),
        ]);
    }

    public function update(Request $request)
    {
        $keys = array_keys(CustomizationRate::DEFINITIONS);

        $rules = [
            'materials' => ['nullable', 'array'],
            'materials.*' => ['nullable', 'array'],
            'materials.*.*.raw_material_id' => ['nullable', 'integer', 'exists:raw_materials,raw_material_id'],
            // Four decimals is what the column holds. A millilitre of ink per
            // line of text is a normal figure here, so the minimum has to sit
            // below the product BOM's two-decimal floor.
            'materials.*.*.quantity' => ['nullable', 'numeric', 'min:0.0001', 'max:99999999.9999'],

            // Both optional, so a form that predates them — or a test that
            // only reprices — leaves the stored figures alone.
            'area_factors' => ['nullable', 'array'],
            'area_factors.*' => ['nullable', 'numeric', 'min:0.01', 'max:99'],
            'ink' => ['nullable', 'array'],
            'ink.*' => ['nullable', 'array'],
            'ink.*.raw_material_id' => ['nullable', 'integer', 'exists:raw_materials,raw_material_id'],
            'ink.*.ml_per_cm2' => ['nullable', 'numeric', 'min:0', 'max:9.99999'],
            'paper' => ['nullable', 'array'],
            'paper.raw_material_id' => ['nullable', 'integer', 'exists:raw_materials,raw_material_id'],
            'paper.width_cm' => ['nullable', 'numeric', 'min:1', 'max:999.99'],
            'paper.height_cm' => ['nullable', 'numeric', 'min:1', 'max:999.99'],
            'paper.margin_cm' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
        ];
        $messages = [
            'materials.*.*.raw_material_id.exists' => 'One of the materials you picked no longer exists.',
            'materials.*.*.quantity.numeric' => 'A material quantity must be a number.',
            'materials.*.*.quantity.min' => 'A material quantity must be greater than zero. Remove the row instead.',
            'materials.*.*.quantity.max' => 'A material quantity is higher than this system can store.',
            'area_factors.*.numeric' => 'A print-area factor must be a number.',
            'area_factors.*.min' => 'A print-area factor must be greater than zero.',
            'area_factors.*.max' => 'A print-area factor is higher than this system can store.',
            'ink.*.raw_material_id.exists' => 'One of the ink bottles you picked no longer exists.',
            'ink.*.ml_per_cm2.numeric' => 'An ink rate must be a number.',
            'ink.*.ml_per_cm2.min' => "An ink rate can't be negative.",
            'ink.*.ml_per_cm2.max' => 'An ink rate is higher than this system can store.',
            'paper.raw_material_id.exists' => 'The transfer paper you picked no longer exists.',
            'paper.width_cm.min' => 'The sheet width must be at least 1 cm.',
            'paper.height_cm.min' => 'The sheet height must be at least 1 cm.',
            'paper.margin_cm.min' => "The cut margin can't be negative.",
        ];

        foreach ($keys as $key) {
            $label = CustomizationRate::DEFINITIONS[$key]['label'];
            $rules["rates.{$key}"] = ['required', 'numeric', 'min:0', 'max:999999.99'];
            $messages["rates.{$key}.required"] = "Enter an amount for {$label}.";
            $messages["rates.{$key}.numeric"] = "{$label} must be a number.";
            $messages["rates.{$key}.min"] = "{$label} can't be negative.";
            $messages["rates.{$key}.max"] = "{$label} is higher than this system can store.";
        }

        $validated = $request->validate($rules, $messages);

        $materials = $this->cleanMaterials($validated['materials'] ?? [], $keys);

        if (is_string($materials)) {
            return back()->withInput()->withErrors(['materials' => $materials]);
        }

        // Prices and materials move together or not at all — a half-applied
        // save would quote customers one thing and cost the shop another.
        DB::transaction(function () use ($validated, $keys, $materials) {
            foreach ($keys as $key) {
                CustomizationRate::updateOrCreate(
                    ['key' => $key],
                    ['amount' => round((float) $validated['rates'][$key], 2)]
                );
            }

            // Replace rather than merge: a row the admin deleted in the form
            // has to disappear, and there is no "which ones did you remove"
            // signal in a post beyond its absence.
            CustomizationRateMaterial::whereIn('rate_key', $keys)->delete();

            foreach ($materials as $key => $lines) {
                foreach ($lines as $materialId => $quantity) {
                    CustomizationRateMaterial::create([
                        'rate_key' => $key,
                        'raw_material_id' => $materialId,
                        'quantity_required' => $quantity,
                    ]);
                }
            }

            // Only the size rows carry a print-area factor. Anything posted
            // against another key is ignored rather than stored.
            foreach ($validated['area_factors'] ?? [] as $key => $factor) {
                if ($factor === null || $factor === '' || ! isset(CustomizationRate::DEFINITIONS[$key]['area_factor'])) {
                    continue;
                }

                CustomizationRate::where('key', $key)->update(['print_area_factor' => round((float) $factor, 3)]);
            }

            foreach ($validated['ink'] ?? [] as $channel => $settings) {
                if (! isset(InkChannel::CHANNELS[$channel]) || ! is_array($settings)) {
                    continue;
                }

                $rate = $settings['ml_per_cm2'] ?? null;
                $materialId = $settings['raw_material_id'] ?? null;

                InkChannel::updateOrCreate(['channel' => $channel], [
                    // An emptied select unlinks the bottle; that is a valid
                    // answer ("measure it, deduct nothing") rather than a
                    // half-filled row to drop.
                    'raw_material_id' => filled($materialId) ? (int) $materialId : null,
                    'ml_per_cm2' => filled($rate) ? round((float) $rate, 5) : InkChannel::rates()[$channel]['ml_per_cm2'],
                ]);
            }

            if (is_array($validated['paper'] ?? null)) {
                $paper = $validated['paper'];
                $current = TransferSheet::current();
                $figure = fn (string $field) => filled($paper[$field] ?? null) ? round((float) $paper[$field], 2) : $current[$field];

                TransferSheet::updateOrCreate(['name' => $current['name']], [
                    'raw_material_id' => filled($paper['raw_material_id'] ?? null) ? (int) $paper['raw_material_id'] : null,
                    'width_cm' => $figure('width_cm'),
                    'height_cm' => $figure('height_cm'),
                    'margin_cm' => $figure('margin_cm'),
                ]);
            }
        });

        CustomizationRate::flushCache();
        InkChannel::flushCache();
        TransferSheet::flushCache();

        return redirect()
            ->route('admin.customization-pricing.index')
            ->with('success', 'Customization pricing and materials updated. New designs are quoted at these rates immediately, and approved orders draw on these materials.');
    }

    /**
     * Reduce the posted material rows to `rate_key => [material_id => qty]`.
     *
     * Half-filled rows are dropped rather than rejected — the form adds an
     * empty row for the admin to fill, and leaving it alone shouldn't fail the
     * save. A duplicate material within one option *is* rejected, because
     * quietly keeping one of the two would halve a draw the admin thought
     * they had doubled.
     *
     * @param  array<string, mixed>  $posted
     * @param  array<int, string>  $keys
     * @return array<string, array<int, float>>|string  the map, or an error message
     */
    private function cleanMaterials(array $posted, array $keys): array|string
    {
        $clean = [];

        foreach ($posted as $key => $lines) {
            // A key the customizer doesn't know how to apply can't be costed.
            if (! in_array($key, $keys, true) || ! is_array($lines)) {
                continue;
            }

            foreach ($lines as $line) {
                $materialId = (int) ($line['raw_material_id'] ?? 0);
                $quantity = $line['quantity'] ?? null;

                if ($materialId <= 0 || $quantity === null || $quantity === '') {
                    continue;
                }

                if (isset($clean[$key][$materialId])) {
                    $label = CustomizationRate::DEFINITIONS[$key]['label'];

                    return "{$label} lists the same material twice. Combine the two rows into one quantity.";
                }

                $clean[$key][$materialId] = round((float) $quantity, 4);
            }
        }

        return $clean;
    }
}
