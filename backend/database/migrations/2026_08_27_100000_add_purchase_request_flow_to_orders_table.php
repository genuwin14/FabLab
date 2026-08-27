<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Institutional buyers pay through CSPC procurement rather than the
     * cashier: they file a Purchase Request, and the order only moves once
     * procurement hands back a PR number. FabLab then uploads the Notice of
     * Award to start production and the Purchase Order to send it out.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('status');
            $table->string('pr_number')->nullable()->after('payment_reference');
            $table->timestamp('pr_deadline')->nullable()->after('pr_number');
            $table->string('noa_path')->nullable()->after('pr_deadline');
            $table->string('po_path')->nullable()->after('noa_path');
        });

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

    public function down(): void
    {
        // Anything parked mid-PR has no home in the old set; send it back to
        // pending so the column can narrow without losing the row.
        DB::table('orders')->where('status', 'awaiting_pr')->update(['status' => 'pending']);
        DB::table('orders')->where('status', 'for_delivery')->update(['status' => 'ready_for_pickup']);

        $this->setStatuses([
            'pending',
            'approved',
            'processing',
            'ready_for_pickup',
            'completed',
            'cancelled',
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'pr_number', 'pr_deadline', 'noa_path', 'po_path']);
        });
    }

    /**
     * Rewrite the status column's allowed values.
     *
     * MySQL keeps a real ENUM, so production still refuses a status the app
     * doesn't know. SQLite — which the test suite runs on — writes the same
     * constraint as a CHECK that can only be replaced by rebuilding the
     * table, so there it becomes a plain string and App\Models\Order::STATUSES
     * is the guard instead.
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
