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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id'); // Custom primary key name
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade'); // Parent order
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade'); // Item purchased
            $table->unsignedBigInteger('custom_design_id')->nullable(); // Link to customization if applicable
            $table->integer('quantity'); // Amount purchased
            $table->decimal('price', 12, 2); // Snapshot price at time of purchase
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
