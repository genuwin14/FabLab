<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Notifications\PurchaseOrderOverdue;
use App\Support\Notifier;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class CheckOverduePurchaseOrders extends Command
{
    protected $signature = 'notifications:check-overdue-pos';

    protected $description = 'Notify staff and admins about purchase orders past their expected delivery date.';

    public function handle(): int
    {
        $overdue = PurchaseOrder::with('supplier')
            ->whereIn('status', ['sent', 'confirmed'])
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', now()->toDateString())
            ->get();

        $sent = 0;

        foreach ($overdue as $po) {
            $alreadyNotified = DatabaseNotification::query()
                ->where('type', PurchaseOrderOverdue::class)
                ->where('data->purchase_order_id', $po->purchase_order_id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            Notifier::staffAndAdmins(new PurchaseOrderOverdue($po));
            $sent++;
        }

        $this->info("Overdue PO check complete. {$sent} purchase order(s) flagged.");

        return self::SUCCESS;
    }
}
