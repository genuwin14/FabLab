<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestResendOtpEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:resend-otp-email {email : The email address to send the new code to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test copy of the "resend code" email (OtpController::resend, email mode)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Mirrors the OTP format in OtpController::resend. Nothing is written
        // to the database — this only proves the mail leg of the flow works.
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $message = "Your new FABLAB verification code is: {$otp}";

        $this->info("Sending resent verification code to {$email}...");
        $this->line("Subject: FABLAB Verification Code");
        $this->line("Code:    {$otp}");

        try {
            Mail::raw($message, function ($mail) use ($email) {
                $mail->to($email)
                    ->subject('FABLAB Verification Code');
            });

            $this->info('✅ Resend-code email sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
        }
    }
}
