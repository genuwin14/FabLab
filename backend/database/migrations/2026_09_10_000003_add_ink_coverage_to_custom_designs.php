<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the studio measured off the print when the design was saved.
 *
 * `ink_coverage` is the fraction of the printable panels each channel covers,
 * `{"cyan": 0.12, "magenta": 0.05, ...}`, worked out from the flat print the
 * studio exports alongside the recipe and the 3D snapshot. `print_image` is
 * that flat print, kept so a reviewer correcting the figure is looking at the
 * same artwork the number came from.
 *
 * Both nullable: a design saved before this existed has neither, and its ink
 * is estimated from the recipe instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_designs', function (Blueprint $table) {
            $table->json('ink_coverage')->nullable()->after('snapshot');
            $table->string('print_image')->nullable()->after('ink_coverage');
        });
    }

    public function down(): void
    {
        Schema::table('custom_designs', function (Blueprint $table) {
            $table->dropColumn(['ink_coverage', 'print_image']);
        });
    }
};
