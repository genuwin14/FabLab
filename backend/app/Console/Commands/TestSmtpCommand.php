<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSmtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smtp:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info("Sending test email to {$email}...");

        try {
            \Illuminate\Support\Facades\Mail::raw('This is a test email from your Laravel application.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('SMTP Connection Test');
            });

            $this->info('Test email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send email.');
            $this->error($e->getMessage());
        }
    }
}
