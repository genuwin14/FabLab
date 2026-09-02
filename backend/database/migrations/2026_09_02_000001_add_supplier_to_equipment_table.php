<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where each machine was bought.
 *
 * Equipment was the one table with no relationships at all — a deliberate
 * choice for a fixed-asset register, but the register already talks about
 * suppliers without naming one: "Returned to supplier for repair" is a status
 * a machine can hold. This records which supplier that is, the same way
 * raw_materials and textures already point at theirs.
 *
 * Nullable, because the machines registered before this column existed have
 * no recorded origin and inventing one would be worse than admitting it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('brand')
                ->constrained('suppliers', 'supplier_id')
                // A supplier being deleted must not erase the machine — the
                // asset outlives the vendor; the link simply goes quiet.
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
