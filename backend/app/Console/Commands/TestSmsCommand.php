<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Command;

class TestSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone} {message=FabLab test message}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Send a test SMS through the configured driver';

    public function handle(SmsService $smsService): int
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message');
        $driver = $smsService->driver();

        $this->line('');
        $this->line("  Driver  : <options=bold>{$driver}</>");
        $this->line("  To      : {$phone}");
        $this->line("  Message : {$message}");

        if ($driver === 'macrodroid') {
            $url = (string) config('sms.drivers.macrodroid.url', '');

            if ($url === '') {
                $this->line('');
                $this->error('MACRODROID_WEBHOOK_URL is not set in .env — nothing to call.');

                return self::FAILURE;
            }

            // The device id in the URL is the only thing protecting it, so show
            // just enough to confirm the right one is configured.
            $this->line('  Webhook : ' . preg_replace('#//([^/]+)/([^/]{0,4})[^/]*#', '//$1/$2…', $url));
        }

        if ($driver === 'unisms') {
            $key = (string) config('sms.drivers.unisms.key', '');

            if ($key === '') {
                $this->line('');
                $this->error('UNISMS_API_KEY is not set in .env — nothing to send with.');

                return self::FAILURE;
            }

            $this->line('  Sender  : ' . config('sms.drivers.unisms.sender'));
            // Enough to tell two keys apart without printing a live secret.
            $this->line('  Key     : ' . substr($key, 0, 4) . str_repeat('•', 8));
        }

        $this->line('');

        if (! $smsService->send($phone, $message)) {
            $this->error('Failed. See storage/logs/laravel.log for the reason.');

            if ($driver === 'macrodroid') {
                $this->line('');
                $this->line('  Common causes:');
                $this->line('   • The phone is offline or MacroDroid is not running');
                $this->line('   • The macro is disabled, or its webhook identifier differs from the URL');
                $this->line('   • Battery optimisation has frozen MacroDroid in the background');
            }

            if ($driver === 'unisms') {
                $this->line('');
                $this->line('  Common causes:');
                $this->line('   • The account has run out of credits');
                $this->line('   • UNISMS_SENDER is not a Sender ID approved on the account');
                $this->line('   • The key was revoked or belongs to a different account');
            }

            return self::FAILURE;
        }

        $this->info('Accepted by the driver.');

        if ($driver === 'macrodroid') {
            $this->line('MacroDroid took the trigger — check the handset to confirm the text actually left.');
        }

        if ($driver === 'unisms') {
            $this->line('UniSMS queued it. The reference id is in storage/logs/laravel.log; whether');
            $this->line('it was delivered arrives on the webhook, or under Messages on the dashboard.');
        }

        return self::SUCCESS;
    }
}
