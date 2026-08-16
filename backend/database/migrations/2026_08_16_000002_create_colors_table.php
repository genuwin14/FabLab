<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A customer could only finish a product with an image texture, so a plain
 * navy t-shirt was unorderable. Colors are the plain-finish counterpart:
 * admin-managed swatches a customer picks *instead of* a texture, each able to
 * carry its own surcharge the way a texture does.
 *
 * Kept in their own table rather than bolted onto `textures` because a color
 * has no image, no supplier and no stock — none of the inventory machinery a
 * texture row carries would mean anything on it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id('color_id');
            $table->string('name');
            // '#RRGGBB'. Stored as text so it round-trips to CSS and to
            // THREE.Color without conversion at either end.
            $table->string('hex_code', 7);
            $table->string('description')->nullable();
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->timestamps();
            // Retired rather than erased: a saved design's recipe references
            // color_id, and those have to keep resolving.
            $table->softDeletes();
        });

        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('color_id')->constrained('colors', 'color_id')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'color_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('colors');
    }
};
