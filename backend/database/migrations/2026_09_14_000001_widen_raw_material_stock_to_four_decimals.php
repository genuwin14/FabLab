<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Raw material stock and its ledger move to four decimal places.
     *
     * Ink is measured from the artwork, and a small print draws very
     * little: a 2 × 4 cm logo on a mug takes a few thousandths of a
     * millilitre of each colour. At two places magenta and black rounded to
     * 0.00 and were dropped off the order, so the review showed one bottle
     * for a three-colour print. The ledger now keeps what the estimator
     * measures.
     *
     * Only the raw material side widens. Products and textures count
     * whole-ish units and are not drawn from a measurement.
     */
    public function up(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('stock_quantity', 12, 4)->default(0)->change();
            $table->decimal('units_on_display', 12, 4)->default(0)->change();
            $table->decimal('units_sponsored', 12, 4)->default(0)->change();
            $table->decimal('units_damaged', 12, 4)->default(0)->change();
            $table->decimal('units_consumed', 12, 4)->default(0)->change();
        });

        Schema::table('raw_material_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 4)->change();
            $table->decimal('stock_delta', 12, 4)->change();
            $table->decimal('stock_after', 12, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('raw_material_movements', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('stock_delta', 10, 2)->change();
            $table->decimal('stock_after', 10, 2)->change();
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('stock_quantity', 10, 2)->default(0)->change();
            $table->decimal('units_on_display', 10, 2)->default(0)->change();
            $table->decimal('units_sponsored', 10, 2)->default(0)->change();
            $table->decimal('units_damaged', 10, 2)->default(0)->change();
            $table->decimal('units_consumed', 10, 2)->default(0)->change();
        });
    }
};
