<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('units_on_display')->default(0)->after('stock');
            $table->integer('units_sponsored')->default(0)->after('units_on_display');
            $table->integer('units_damaged')->default(0)->after('units_sponsored');
            $table->integer('units_consumed')->default(0)->after('units_damaged');
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('units_on_display', 10, 2)->default(0)->after('stock_quantity');
            $table->decimal('units_sponsored', 10, 2)->default(0)->after('units_on_display');
            $table->decimal('units_damaged', 10, 2)->default(0)->after('units_sponsored');
            $table->decimal('units_consumed', 10, 2)->default(0)->after('units_damaged');
        });

        Schema::table('textures', function (Blueprint $table) {
            $table->decimal('units_on_display', 10, 2)->default(0)->after('stock_quantity');
            $table->decimal('units_sponsored', 10, 2)->default(0)->after('units_on_display');
            $table->decimal('units_damaged', 10, 2)->default(0)->after('units_sponsored');
            $table->decimal('units_consumed', 10, 2)->default(0)->after('units_damaged');
        });
    }

    public function down(): void
    {
        $cols = ['units_on_display', 'units_sponsored', 'units_damaged', 'units_consumed'];

        Schema::table('products', fn (Blueprint $t) => $t->dropColumn($cols));
        Schema::table('raw_materials', fn (Blueprint $t) => $t->dropColumn($cols));
        Schema::table('textures', fn (Blueprint $t) => $t->dropColumn($cols));
    }
};
