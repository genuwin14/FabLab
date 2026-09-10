<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The four sublimation inks as the printer sees them.
 *
 * Ink used to be a fixed figure per design element — a quarter millilitre of
 * black per line of text, whatever the text said or what colour it was. That
 * is a bill of materials guessing at a picture. What actually decides how
 * much ink a print takes is how much of the printable area each channel
 * covers, and that can be measured from the artwork itself.
 *
 * One row per channel. `raw_material_id` says which bottle on the shelf the
 * channel empties, and `ml_per_cm2` is what a square centimetre of solid
 * coverage in that channel costs. The rate is a property of the printer and
 * the paper, so it is set once and calibrated, not typed per order:
 * print a solid test square, weigh the bottle before and after, divide.
 *
 * Linked to the shipped ink materials by name where they exist, so a fresh
 * database measures ink the moment it is migrated and seeded. A channel with
 * no material still measures — the order screen just has nothing to draw.
 */
return new class extends Migration
{
    private const CHANNELS = ['cyan', 'magenta', 'yellow', 'black'];

    public function up(): void
    {
        Schema::create('ink_channels', function (Blueprint $table) {
            $table->id('ink_channel_id');
            $table->string('channel')->unique();
            $table->foreignId('raw_material_id')->nullable()
                ->constrained('raw_materials', 'raw_material_id')->nullOnDelete();

            // Five decimals: a plausible figure is a few thousandths of a
            // millilitre per square centimetre, and the ledger the result
            // lands in rounds to two decimals of a millilitre at the end.
            $table->decimal('ml_per_cm2', 10, 5)->default(0);
            $table->timestamps();
        });

        $now = now();
        foreach (self::CHANNELS as $channel) {
            $material = DB::table('raw_materials')
                ->where('name', 'Sublimation Ink (' . ucfirst($channel) . ')')
                ->whereNull('deleted_at')
                ->value('raw_material_id');

            DB::table('ink_channels')->insert([
                'channel' => $channel,
                'raw_material_id' => $material,
                'ml_per_cm2' => \App\Models\InkChannel::DEFAULT_ML_PER_CM2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ink_channels');
    }
};
