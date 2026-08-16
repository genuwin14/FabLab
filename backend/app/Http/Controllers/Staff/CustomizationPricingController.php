<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CustomizationRate;

/**
 * Staff read the customizer's price list; they don't set it.
 *
 * It's here so staff can answer "what does an extra line of text cost?" without
 * asking an admin. Saving a new rate reprices every quote in the storefront the
 * moment it lands, so update() exists only on the admin controller and no staff
 * route writes to customization_rates.
 */
class CustomizationPricingController extends Controller
{
    public function index()
    {
        return view('staff.customization-pricing.index', [
            'rates' => CustomizationRate::forDisplay(),
            'logoMinScale' => \App\Models\CustomDesign::LOGO_MIN_SCALE,
            'logoMaxScale' => \App\Models\CustomDesign::LOGO_MAX_SCALE,
        ]);
    }
}
