<?php

use App\Models\CustomizationRate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A transfer is the same size whatever the garment.
 *
 * The print-area factors shipped assuming a design printed on a 5XL covers
 * more fabric than on a Small, as a direct-to-garment print would. The shop
 * presses a cut transfer instead, and a 4×2 sticker is 4×2 on every size.
 * So every size now prints at the product's own area — factor 1 — and the
 * setting stays on the pricing screen for a shop that does scale.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('customization_rates')
            ->whereIn('key', array_keys(array_filter(
                CustomizationRate::DEFINITIONS,
                fn ($definition) => isset($definition['area_factor'])
            )))
            ->update(['print_area_factor' => 1]);
    }

    public function down(): void
    {
        // The old defaults are gone from the definitions; nothing to put back.
    }
};
