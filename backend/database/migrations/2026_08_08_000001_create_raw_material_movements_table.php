<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('raw_material_movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->foreignId('raw_material_id')->constrained('raw_materials', 'raw_material_id')->cascadeOnDelete();

            // Null when the shop's own automation moved the stock rather than a
            // person — an order approval, or a user who has since been removed.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders', 'order_id')->nullOnDelete();

            // A movement can be undone once and only once, which the unique
            // index enforces — two reversals would double-refund the stock.
            $table->unsignedBigInteger('reverses_movement_id')->nullable()->unique();
            $table->foreign('reverses_movement_id')->references('movement_id')->on('raw_material_movements')->nullOnDelete();

            $table->string('reason');
            $table->decimal('quantity', 10, 2);

            // What this did to `stock_quantity`, signed, plus the balance it
            // left behind. Storing both means the log can be read as a running
            // ledger without replaying every earlier row.
            $table->decimal('stock_delta', 10, 2);
            $table->decimal('stock_after', 10, 2);

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['raw_material_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_movements');
    }
};
