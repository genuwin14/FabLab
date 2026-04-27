<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('raw_material_id')->nullable()->after('product_id')->constrained('raw_materials', 'raw_material_id')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['raw_material_id']);
            $table->dropColumn('raw_material_id');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
