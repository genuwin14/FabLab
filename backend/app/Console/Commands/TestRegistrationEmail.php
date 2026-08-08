<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestRegistrationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:registration-email {email : The email address to send the verification code to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test copy of the signup verification code email (AuthController::register, email mode)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Mirrors the OTP format in AuthController::register. Nothing is written
        // to the database — this only proves the mail leg of the flow works.
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $message = "Your FABLAB verification code is: {$otp}";

        $this->info("Sending signup verification code to {$email}...");
        $this->line("Subject: FABLAB Account Verification");
        $this->line("Code:    {$otp}");

        try {
            Mail::raw($message, function ($mail) use ($email) {
                $mail->to($email)
                    ->subject('FABLAB Account Verification');
            });

            $this->info('✅ Verification email sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
        }
    }
}
