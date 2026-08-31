<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The bill of materials for a customization option.
 *
 * A product's own BOM (`product_raw_materials`) covers the blank item. It says
 * nothing about what a customer *adds* to it in the studio, so a design with
 * twelve lines of text and internal lighting drew ink and LED strip that no
 * order ever deducted: the fee was charged and the shelf never moved.
 *
 * This is the counterpart to `customization_rates` — that table says what an
 * option costs the customer, this one says what it costs the shop. Keyed on
 * the same fixed rate keys, so an option can't be given materials the
 * customizer doesn't know how to apply.
 *
 * Quantities are per one unit of the option: one line of text, one shape, one
 * uploaded image at 1x size, one lit item, one item of that size.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_rate_materials', function (Blueprint $table) {
            $table->id('customization_rate_material_id');
            $table->string('rate_key');
            $table->foreignId('raw_material_id')->constrained('raw_materials', 'raw_material_id')->cascadeOnDelete();

            // Four decimal places, unlike the product BOM's two: a millilitre of
            // ink per character is a normal figure here, and rounding it to 0.01
            // would either double it or erase it.
            $table->decimal('quantity_required', 15, 4);
            $table->timestamps();

            // One row per option per material — a second row for the same pair
            // would silently double the draw.
            $table->unique(['rate_key', 'raw_material_id']);
            $table->index('rate_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_rate_materials');
    }
};
