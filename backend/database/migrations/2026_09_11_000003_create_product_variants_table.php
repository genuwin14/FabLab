<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock per size and colour.
 *
 * A garment was one figure: "200 shirts", with no way to say that the Navy
 * 5XL is down to one while the White Medium has fifty. The shop stocks
 * blanks per colour and per size, and that is what it needs to see.
 *
 * A variant is one cell of that grid: a product, a size (null for a
 * product that comes in one size — a mug), a colour (null for a product
 * with no colours assigned), and its own stock and threshold. The product's
 * own `stock` becomes the sum of its variants, kept in step whenever a
 * variant moves, so every screen and report that reads the total keeps
 * working; a product with no variants keeps its single figure as before.
 *
 * `has_sizes` says whether the product comes in the S–5XL run at all. Set
 * here for the customizable products the studio opens as a shirt or polo;
 * a mug, an umbrella or a tote has no size.
 *
 * Cart lines, order items and purchase order lines each record which
 * variant they moved, so checkout, cancellation and delivery put stock
 * back in the same cell they took it from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_sizes')->default(false)->after('is_customizable');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id('product_variant_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->cascadeOnDelete();
            $table->string('size', 20)->nullable();
            $table->foreignId('color_id')->nullable()->constrained('colors', 'color_id')->nullOnDelete();
            // "size:colour" with "-" for none, because a unique index over two
            // nullable columns lets duplicate nulls through.
            $table->string('variant_key', 40);
            $table->integer('stock')->default(0);
            $table->integer('low_stock_threshold')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'variant_key']);
        });

        foreach (['cart_items', 'order_items', 'purchase_order_items'] as $lines) {
            Schema::table($lines, function (Blueprint $table) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants', 'product_variant_id')->nullOnDelete();
            });
        }

        // The garments the studio opens as a shirt or polo come in sizes;
        // nothing else seeded does.
        DB::table('products')
            ->where('is_customizable', true)
            ->where(function ($query) {
                $query->where('name', 'like', '%shirt%')->orWhere('name', 'like', '%polo%');
            })
            ->update(['has_sizes' => true]);

        // Give every product that now tracks variants its grid, with the
        // stock it already had sitting in the first cell for an admin to
        // spread out.
        foreach (\App\Models\Product::where('has_sizes', true)->orWhereHas('colors')->get() as $product) {
            $product->ensureVariants();
        }
    }

    public function down(): void
    {
        foreach (['cart_items', 'order_items', 'purchase_order_items'] as $lines) {
            Schema::table($lines, function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_variant_id');
            });
        }

        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_sizes');
        });
    }
};
