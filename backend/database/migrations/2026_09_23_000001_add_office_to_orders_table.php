<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Which CSPC office an order is for. Empty means the customer is buying
     * for themself. A Purchase Request is always filed by an office, so
     * checkout insists on one there; a PAXS order may name one or not.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('office')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('office');
        });
    }
};
