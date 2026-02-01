<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test SMS using the SmsService';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\SmsService $smsService)
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message');

        $this->info("Sending SMS to {$phone}...");
        $this->info("Message: {$message}");

        $success = $smsService->send($phone, $message);

        if ($success) {
            $this->info('SMS sent successfully!');
        } else {
            $this->error('Failed to send SMS. Check logs for details.');
        }
    }
}
