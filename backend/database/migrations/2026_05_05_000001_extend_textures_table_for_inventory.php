<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('textures', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('description')
                ->constrained('suppliers', 'supplier_id')->nullOnDelete();
            $table->decimal('cost_per_unit', 10, 2)->default(0)->after('supplier_id');
            $table->decimal('stock_quantity', 10, 2)->default(0)->after('cost_per_unit');
            $table->decimal('low_stock_threshold', 10, 2)->default(10)->after('stock_quantity');
            $table->string('unit')->default('pcs')->after('low_stock_threshold');
            $table->decimal('price_modifier', 10, 2)->default(0)->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('textures', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'supplier_id',
                'cost_per_unit',
                'stock_quantity',
                'low_stock_threshold',
                'unit',
                'price_modifier',
            ]);
        });
    }
};
