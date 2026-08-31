<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a finish costs the shop in raw material.
 *
 * A texture carries its own stock, so ordering one already moved something.
 * A colour never did — `create_colors_table` says so outright: "a color has no
 * image, no supplier and no stock". So a customer could pick a red finish, be
 * charged the colour's surcharge, and no ink ever left the shelf.
 *
 * Both finishes get the same nullable link so the two behave alike on the
 * order screen. Leaving it unset keeps exactly the old behaviour, which is why
 * it is nullable rather than defaulted to some arbitrary material:
 *
 *   - a colour with no material linked consumes nothing, as before;
 *   - a texture with no material linked moves only its own stock, as before,
 *     and a linked material is drawn *in addition* to it — the texture row is
 *     the printed sheet, the material is what it was printed with.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['colors', 'textures'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('raw_material_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('raw_materials', 'raw_material_id')
                    // Retiring a material must not take the swatch with it. The
                    // link simply goes quiet and the finish stops consuming.
                    ->nullOnDelete();

                // Per item finished in this colour or texture. Four decimals to
                // match the customization BOM — ink is measured in millilitres.
                $blueprint->decimal('material_quantity', 15, 4)->default(0)->after('raw_material_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['colors', 'textures'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['raw_material_id']);
                $blueprint->dropColumn(['raw_material_id', 'material_quantity']);
            });
        }
    }
};
