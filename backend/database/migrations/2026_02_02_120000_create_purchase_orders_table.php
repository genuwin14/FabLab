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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('purchase_order_id');
            $table->string('po_number')->unique(); // e.g., PO-2024-001
            $table->foreignId('supplier_id')->constrained('suppliers', 'supplier_id')->onDelete('cascade');
            $table->enum('status', ['draft', 'sent', 'confirmed', 'delivered', 'cancelled'])->default('draft');
            $table->date('expected_delivery_date')->nullable(); // Supplier ETA
            $table->decimal('total_cost', 12, 2)->nullable(); // Total purchase value
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin who created the PO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
