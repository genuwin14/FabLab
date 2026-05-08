<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id('equipment_id');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('property_no')->nullable();
            $table->date('date_acquired')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('status')->default('Serviceable');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
