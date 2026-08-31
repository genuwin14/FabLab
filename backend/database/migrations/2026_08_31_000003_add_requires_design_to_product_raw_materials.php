<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which of a product's materials are only spent when there is something to
 * print.
 *
 * A product's bill of materials was drawn unconditionally, once per unit
 * ordered. That is right for what the item is made of and wrong for what
 * decorating it costs: a plain white mug is handed over as-is, but the mug's
 * BOM still took a sheet of transfer paper and 4ml of ink for a print that
 * never happened. Ten plain mugs took ten sheets.
 *
 * Flagging a line here says "only when the customer designed something". The
 * blank still comes out of product stock either way — the blank *is* the
 * product — but the consumables that decorate it stay on the shelf.
 *
 * Defaults to false, so every existing bill of materials keeps behaving
 * exactly as it did until someone ticks a line.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_raw_materials', function (Blueprint $table) {
            $table->boolean('requires_design')->default(false)->after('quantity_required');
        });
    }

    public function down(): void
    {
        Schema::table('product_raw_materials', function (Blueprint $table) {
            $table->dropColumn('requires_design');
        });
    }
};
