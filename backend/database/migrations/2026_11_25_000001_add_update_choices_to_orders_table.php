<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How the customer asked to hear about an order, ticked at checkout. The
 * in-app bell always rings; these add email and texts on top of it.
 *
 * The defaults are what every order got before the choice existed: email
 * on, no texts. So older orders carry on exactly as they did.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('notify_email')->default(true)->after('office');
            $table->boolean('notify_sms')->default(false)->after('notify_email');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['notify_email', 'notify_sms']);
        });
    }
};
