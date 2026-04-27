<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id('raw_material_id');
            $table->string('name');
            $table->foreignId('supplier_id')->constrained('suppliers', 'supplier_id')->onDelete('cascade');
            $table->decimal('cost_per_unit', 10, 2);
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->decimal('low_stock_threshold', 10, 2)->default(10);
            $table->string('unit')->default('pcs');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};
