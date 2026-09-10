<?php

use App\Models\CustomizationRate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How big a print physically is, which is the other half of measuring ink.
 *
 * Coverage measured off the artwork is a fraction — "cyan covers 12% of the
 * printable panels". Turning that into millilitres needs the panels' real
 * size, and that is a property of the garment: a product carries the
 * printable area of its blank at Medium, and each size carries how much
 * bigger or smaller its panels are than that. A 5XL shirt prints the same
 * design over more fabric than a Small, so it takes more ink — which the
 * fixed per-element figures never knew.
 *
 * `print_area_cm2` is nullable, and null means "not measured yet": such a
 * product keeps drawing whatever its element bills of materials say, so an
 * install that hasn't filled this in behaves exactly as it did.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('print_area_cm2', 10, 2)->nullable()->after('is_customizable');
        });

        Schema::table('customization_rates', function (Blueprint $table) {
            // Only meaningful on the size rows; 1.0 everywhere else.
            $table->decimal('print_area_factor', 6, 3)->default(1)->after('amount');
        });

        foreach (CustomizationRate::DEFINITIONS as $key => $definition) {
            if (isset($definition['area_factor'])) {
                DB::table('customization_rates')
                    ->where('key', $key)
                    ->update(['print_area_factor' => $definition['area_factor']]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customization_rates', function (Blueprint $table) {
            $table->dropColumn('print_area_factor');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('print_area_cm2');
        });
    }
};
