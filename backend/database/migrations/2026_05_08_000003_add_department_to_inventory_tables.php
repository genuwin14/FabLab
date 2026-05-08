<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('department')->nullable()->after('category_id');
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->string('department')->nullable()->after('description');
        });

        Schema::table('textures', function (Blueprint $table) {
            $table->string('department')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('department'));
        Schema::table('raw_materials', fn (Blueprint $t) => $t->dropColumn('department'));
        Schema::table('textures', fn (Blueprint $t) => $t->dropColumn('department'));
    }
};
