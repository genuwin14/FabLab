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

            return self::FAILURE;
        }

        $this->info('Accepted by the driver.');

        if ($driver === 'macrodroid') {
            $this->line('MacroDroid took the trigger — check the handset to confirm the text actually left.');
        }

        return self::SUCCESS;
    }
}
