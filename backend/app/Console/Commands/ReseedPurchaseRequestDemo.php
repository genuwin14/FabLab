<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use Database\Seeders\PurchaseRequestOrderSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Put the Purchase Request demo orders back at one-per-stage.
 *
 * Walking the flow advances them, so after a test run there is nothing left
 * sitting at Awaiting PR or Approved. This clears the PR orders and lays a
 * fresh set down — which the seeder alone won't do, since it skips itself
 * while any still exist.
 */
class ReseedPurchaseRequestDemo extends Command
{
    protected $signature = 'orders:reseed-pr-demo {--force : Skip the confirmation}';

    protected $description = 'Reset the Purchase Request demo orders to one per stage. Deletes existing PR orders.';

    public function handle(): int
    {
        // Deleting orders is not something to do against real data, and demo
        // seeders have no business running in production at all.
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to delete orders in production. Pass --force if you are certain.');

            return self::FAILURE;
        }

        $orders = Order::where('payment_method', Order::METHOD_PR)
            ->orderBy('order_id')
            ->get(['order_id', 'order_number', 'status']);

        if ($orders->isNotEmpty()) {
            $this->line('These Purchase Request orders will be deleted, along with their items:');

            foreach ($orders as $order) {
                $this->line("  {$order->order_number}  {$order->status}");
            }

            if (! $this->option('force') && ! $this->confirm('Delete them and seed a fresh set?', false)) {
                $this->info('Nothing changed.');

                return self::SUCCESS;
            }

            DB::transaction(function () use ($orders) {
                $ids = $orders->pluck('order_id');
                OrderItem::whereIn('order_id', $ids)->delete();
                Order::whereIn('order_id', $ids)->delete();
            });

            $this->info("Deleted {$orders->count()} order(s).");
        }

        $this->callSilent('db:seed', ['--class' => PurchaseRequestOrderSeeder::class, '--force' => true]);

        $fresh = Order::where('payment_method', Order::METHOD_PR)->orderBy('order_id')->get();

        $this->newLine();
        $this->info('Purchase Request demo orders:');

        foreach ($fresh as $order) {
            $this->line(sprintf('  %-22s %s', $order->order_number, $order->status));
        }

        return self::SUCCESS;
    }
}
