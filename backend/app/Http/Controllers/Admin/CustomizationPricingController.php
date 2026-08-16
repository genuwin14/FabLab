<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomizationRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin screen for what the customizer charges per design element.
 *
 * Only the amounts are editable — the rate keys are fixed by
 * CustomizationRate::DEFINITIONS because each one is wired into how a design is
 * priced.
 */
class CustomizationPricingController extends Controller
{
    public function index()
    {
        return view('admin.customization-pricing.index', [
            'rates' => CustomizationRate::forDisplay(),
            'logoMinScale' => \App\Models\CustomDesign::LOGO_MIN_SCALE,
            'logoMaxScale' => \App\Models\CustomDesign::LOGO_MAX_SCALE,
        ]);
    }

    public function update(Request $request)
    {
        $keys = array_keys(CustomizationRate::DEFINITIONS);

        $rules = [];
        $messages = [];
        foreach ($keys as $key) {
            $label = CustomizationRate::DEFINITIONS[$key]['label'];
            $rules["rates.{$key}"] = ['required', 'numeric', 'min:0', 'max:999999.99'];
            $messages["rates.{$key}.required"] = "Enter an amount for {$label}.";
            $messages["rates.{$key}.numeric"] = "{$label} must be a number.";
            $messages["rates.{$key}.min"] = "{$label} can't be negative.";
            $messages["rates.{$key}.max"] = "{$label} is higher than this system can store.";
        }

        $validated = $request->validate($rules, $messages);

        // All four move together or none do — a half-applied price list would
        // quote customers one thing and charge them another.
        DB::transaction(function () use ($validated, $keys) {
            foreach ($keys as $key) {
                CustomizationRate::updateOrCreate(
                    ['key' => $key],
                    ['amount' => round((float) $validated['rates'][$key], 2)]
                );
            }
        });

        CustomizationRate::flushCache();

        return redirect()
            ->route('admin.customization-pricing.index')
            ->with('success', 'Customization pricing updated. New designs are quoted at these rates immediately.');
    }
}
