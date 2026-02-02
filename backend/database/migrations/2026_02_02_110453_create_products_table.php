<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock')->default(0);

            // Foreign Keys
            $table->foreignId('category_id')->constrained('categories', 'category_id')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers', 'supplier_id')->onDelete('set null');

            $table->string('status')->nullable(); // functional, defective
            $table->boolean('is_customizable')->default(false);
            $table->integer('low_stock_threshold')->nullable();
            $table->string('unit')->default('pcs');
            $table->decimal('cost', 12, 2)->nullable();
            $table->longText('image')->nullable(); // Base64 image

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
