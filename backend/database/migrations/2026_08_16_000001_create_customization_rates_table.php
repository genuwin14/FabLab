<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What the customizer charges per design element used to be four numbers
 * hardcoded in two places — App\Models\CustomDesign and the studio's JavaScript
 * — so repricing meant a code change and a deploy. This table puts them behind
 * an admin screen instead, and makes the model the single source both sides
 * read from.
 *
 * Only the amount is stored. The keys are fixed and their labels live in
 * App\Models\CustomizationRate, so nobody can rename a key out from under the
 * pricing code or invent a rate the customizer doesn't know how to apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_rates', function (Blueprint $table) {
            $table->id('customization_rate_id');
            $table->string('key')->unique();
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });

        // Seed the rates the system has always charged, so an existing install
        // prices designs exactly as it did the day before this migration ran.
        $now = now();
        DB::table('customization_rates')->insert(
            collect(\App\Models\CustomizationRate::DEFINITIONS)
                ->map(fn($definition, $key) => [
                    'key' => $key,
                    'amount' => $definition['default'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->values()
                ->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_rates');
    }
};
