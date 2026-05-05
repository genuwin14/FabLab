<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_textures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('texture_id')->constrained('textures', 'texture_id')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'texture_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_textures');
    }
};
