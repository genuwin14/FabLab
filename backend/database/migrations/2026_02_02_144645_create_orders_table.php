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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id'); // Custom primary key name
            $table->string('order_number')->unique(); // e.g., ORDR-20260827-0001
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The buyer
            $table->enum('status', ['pending', 'approved', 'processing', 'ready_for_pickup', 'completed', 'cancelled'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('total_amount', 12, 2); // Final transaction price
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
