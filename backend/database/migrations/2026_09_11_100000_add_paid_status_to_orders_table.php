<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A cashier order now passes through `paid` between approval and
     * production: the admin records the number on the official receipt,
     * which is what the customer brings to collect, and only then may staff
     * start the job. PR orders skip it — procurement pays them, and the
     * Notice of Award is what starts their production.
     */
    public function up(): void
    {
        $this->setStatuses([
            'pending',
            'awaiting_pr',
            'approved',
            'paid',
            'processing',
            'ready_for_pickup',
            'for_delivery',
            'completed',
            'cancelled',
        ]);

        // An approved cashier order that already carries a receipt number was
        // paid under the old flow, where staff typed it on the way into
        // production; it belongs in the new step rather than back at approval.
        DB::table('orders')
            ->where('status', 'approved')
            ->where('payment_method', 'cash')
            ->whereNotNull('payment_reference')
            ->update(['status' => 'paid']);
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'paid')->update(['status' => 'approved']);

        $this->setStatuses([
            'pending',
            'awaiting_pr',
            'approved',
            'processing',
            'ready_for_pickup',
            'for_delivery',
            'completed',
            'cancelled',
        ]);
    }

    /**
     * Rewrite the status column's allowed values. See the PR-flow migration
     * for why MySQL gets an ENUM and SQLite a plain string.
     */
    private function setStatuses(array $statuses): void
    {
        if (DB::getDriverName() === 'mysql') {
            $values = implode(',', array_map(fn ($s) => "'{$s}'", $statuses));

            DB::statement("ALTER TABLE orders MODIFY status ENUM({$values}) NOT NULL DEFAULT 'pending'");

            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
