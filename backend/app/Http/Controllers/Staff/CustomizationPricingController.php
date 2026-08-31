<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CustomizationRate;
use App\Models\RawMaterial;

/**
 * Staff read the customizer's price list; they don't set it.
 *
 * It's here so staff can answer "what does an extra line of text cost?" without
 * asking an admin — and, since the same screen now carries each option's bill
 * of materials, "what will it take off the shelf?", which is the more useful
 * of the two at the bench. Saving a new rate reprices every quote in the
 * storefront the moment it lands, so update() exists only on the admin
 * controller and no staff route writes to customization_rates.
 */
class CustomizationPricingController extends Controller
{
    public function index()
    {
        return view('staff.customization-pricing.index', [
            'rates' => CustomizationRate::forDisplay(),
            'logoMinScale' => \App\Models\CustomDesign::LOGO_MIN_SCALE,
            'logoMaxScale' => \App\Models\CustomDesign::LOGO_MAX_SCALE,
            // Keyed on the id, because the view resolves the ids each rate
            // carries rather than offering a list to pick from — staff read
            // this screen, they don't edit it.
            'materialLookup' => RawMaterial::get(['raw_material_id', 'name', 'unit'])->keyBy('raw_material_id'),
        ]);
    }
}
