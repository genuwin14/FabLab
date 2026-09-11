<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The sheet a design is printed on before it is pressed onto the product.
 *
 * The shop's process is a heat transfer: the artwork goes through the
 * printer onto transfer paper, the piece is cut out, and it is pressed on
 * like a sticker. The printers take up to A4, and every piece is cut from
 * an A4 sheet — so what an order costs in paper is how much of a sheet each
 * print's piece takes, not a count per garment size, which is what the
 * customization bill of materials used to say.
 *
 * One row: the sheet's size, the margin the cutter leaves around the
 * artwork, and the raw material the sheets are stocked as. Linked to the
 * shipped transfer paper by name where it exists, the same way the ink
 * channels are.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_sheets', function (Blueprint $table) {
            $table->id('transfer_sheet_id');
            $table->string('name');
            $table->foreignId('raw_material_id')->nullable()
                ->constrained('raw_materials', 'raw_material_id')->nullOnDelete();
            $table->decimal('width_cm', 6, 2);
            $table->decimal('height_cm', 6, 2);
            // Around the artwork on every side, so the piece that is cut is a
            // little bigger than the print.
            $table->decimal('margin_cm', 5, 2)->default(0.5);
            $table->timestamps();
        });

        $material = DB::table('raw_materials')
            ->where('name', 'Sublimation Transfer Paper (A4)')
            ->whereNull('deleted_at')
            ->value('raw_material_id');

        $now = now();
        DB::table('transfer_sheets')->insert([
            'name' => 'A4',
            'raw_material_id' => $material,
            'width_cm' => \App\Models\TransferSheet::A4_WIDTH_CM,
            'height_cm' => \App\Models\TransferSheet::A4_HEIGHT_CM,
            'margin_cm' => \App\Models\TransferSheet::DEFAULT_MARGIN_CM,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_sheets');
    }
};
