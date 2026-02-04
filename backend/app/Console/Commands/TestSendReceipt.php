<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;

class TestSendReceipt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:send-receipt {email : The email address to send the receipt to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test order receipt email using the latest order or dummy data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Preparing to send test receipt to: {$email}");

        // Attempt to fetch the latest order
        $order = Order::with(['user', 'orderItems.product'])->latest()->first();

        if (!$order) {
            $this->warn("No existing orders found in database. Creating a temporary dummy order for testing...");

            // Create a dummy structure that mimics the Order model with relationships
            $user = new User(['fullname' => 'Test User', 'email' => $email]);

            $product1 = new Product(['name' => 'Test Product A', 'price' => 150.00]);
            $product2 = new Product(['name' => 'Test Product B', 'price' => 500.00]);

            $item1 = new OrderItem(['quantity' => 2, 'price' => 150.00]);
            $item1->setRelation('product', $product1);

            $item2 = new OrderItem(['quantity' => 1, 'price' => 500.00]);
            $item2->setRelation('product', $product2);

            $order = new Order([
                'order_number' => 'TEST-ORD-' . rand(1000, 9999),
                'total_amount' => 800.00,
                'status' => 'pending',
                'created_at' => now()
            ]);

            $order->setRelation('user', $user);
            $order->setRelation('orderItems', collect([$item1, $item2]));
        } else {
            $this->info("Using latest Order #{$order->order_number} from database.");
        }

        try {
            Mail::to($email)->send(new OrderReceipt($order));
            $this->info('✅ Receipt email sent successfully!');
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
        }
    }
}
