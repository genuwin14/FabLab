<?php

namespace App\Console\Commands;

use App\Services\PurchaseRequestService;
use Illuminate\Console\Command;

class CloseExpiredPurchaseRequests extends Command
{
    protected $signature = 'orders:close-expired-prs {--dry-run : List what would close without touching anything}';

    protected $description = 'Close orders whose Purchase Request window ran out with no PR number, returning their stock.';

    public function handle(PurchaseRequestService $purchaseRequests): int
    {
        $lapsed = $purchaseRequests->lapsed();

        if ($lapsed->isEmpty()) {
            $this->info('No Purchase Request windows have lapsed.');

            return self::SUCCESS;
        }

        $closed = 0;

        foreach ($lapsed as $order) {
            $deadline = $order->pr_deadline->format('j M Y');

            if ($this->option('dry-run')) {
                $this->line("Would close {$order->order_number} (window closed {$deadline}).");

                continue;
            }

            if ($purchaseRequests->close($order, $purchaseRequests->lapsedReason($order))) {
                $this->line("Closed {$order->order_number} (window closed {$deadline}).");
                $closed++;
            }
        }

        $this->info($this->option('dry-run')
            ? "Dry run complete. {$lapsed->count()} order(s) would close."
            : "Purchase Request sweep complete. {$closed} order(s) closed.");

        return self::SUCCESS;
    }
}
