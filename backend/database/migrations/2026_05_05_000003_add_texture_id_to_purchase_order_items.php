<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('texture_id')->nullable()->after('raw_material_id')
                ->constrained('textures', 'texture_id')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['texture_id']);
            $table->dropColumn('texture_id');
        });
    }
};
