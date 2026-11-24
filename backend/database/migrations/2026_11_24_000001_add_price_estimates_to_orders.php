<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What the system quoted, kept beside what the shop confirmed.
 *
 * The panel asked that the cost the system works out be treated as an
 * estimate the shop confirms. An admin can now change a line's price when
 * approving an order, so `price` and `total_amount` become the confirmed
 * figures, and these keep the estimate the customer saw at checkout.
 * Existing orders were never re-priced, so their estimate is what they cost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('estimated_total', 12, 2)->nullable()->after('total_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('estimated_price', 12, 2)->nullable()->after('price');
        });

        DB::table('orders')->update(['estimated_total' => DB::raw('total_amount')]);
        DB::table('order_items')->update(['estimated_price' => DB::raw('price')]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('estimated_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('estimated_total');
        });
    }
};
