<?php

use App\Models\CustomizationRate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Put a price on the bigger garment sizes where nobody ever has.
 *
 * The panel asked for shirts to be priced by size. Every size shipped at ₱0,
 * so a 5XL cost what a Small did until an admin happened to set them. A
 * database where every size is still ₱0 gets the shipped surcharges (XL and
 * up); one where any size has been priced is left exactly as it is. They are
 * the shop's to adjust on Customization Pricing.
 */
return new class extends Migration
{
    public function up(): void
    {
        $sizes = $this->sizeDefaults();

        $alreadyPriced = DB::table('customization_rates')
            ->whereIn('key', array_keys($sizes))
            ->where('amount', '!=', 0)
            ->exists();

        if ($alreadyPriced) {
            return;
        }

        foreach ($sizes as $key => $amount) {
            DB::table('customization_rates')->where('key', $key)->update(['amount' => $amount, 'updated_at' => now()]);
        }

        CustomizationRate::flushCache();
    }

    /** Back to ₱0, but only the sizes still at the figure this set. */
    public function down(): void
    {
        foreach ($this->sizeDefaults() as $key => $amount) {
            DB::table('customization_rates')->where('key', $key)->where('amount', $amount)->update(['amount' => 0]);
        }

        CustomizationRate::flushCache();
    }

    /** @return array<string, float> */
    private function sizeDefaults(): array
    {
        return collect(CustomizationRate::DEFINITIONS)
            ->filter(fn (array $definition) => $definition['group'] === 'sizes')
            ->map(fn (array $definition) => (float) $definition['default'])
            ->all();
    }
};
