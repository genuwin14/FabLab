<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where the printable panels sit on a design's flat print.
 *
 * The print the studio exports is the model's whole texture atlas: the
 * front, back and sleeves of a shirt are small rectangles in it, and the
 * rest is seams and gaps the printer never sees. Shown whole, a chest
 * design looks tiny and off in a corner. With the panels' rectangles kept
 * beside the print, the order screens can show "Front", "Back" and each
 * sleeve cropped and labelled — the artwork the way it lands on the garment.
 *
 * `[{"id": "front", "label": "Front", "u0": .., "v0": .., "u1": .., "v1": ..,
 * "flipU": false, "flipV": false}, ...]`, in UV space, straight from the
 * studio's model definition. Nullable: a design saved before this existed
 * shows its whole print instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_designs', function (Blueprint $table) {
            $table->json('print_zones')->nullable()->after('print_image');
        });
    }

    public function down(): void
    {
        Schema::table('custom_designs', function (Blueprint $table) {
            $table->dropColumn('print_zones');
        });
    }
};
