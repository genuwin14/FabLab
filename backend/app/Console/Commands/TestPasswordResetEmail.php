<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestPasswordResetEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:password-reset-email {email : The email address to send the reset code to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test copy of the forgot-password reset code email (AuthController::sendResetCode, email mode)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Mirrors the OTP format in AuthController::sendResetCode. No session
        // entry is created, so this code cannot actually reset a password —
        // it only proves the mail leg of the flow works.
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $message = "Your FABLAB password reset code is: {$otp}";

        $this->info("Sending password reset code to {$email}...");
        $this->line("Subject: FABLAB Password Reset Code");
        $this->line("Code:    {$otp}");

        try {
            Mail::raw($message, function ($mail) use ($email) {
                $mail->to($email)
                    ->subject('FABLAB Password Reset Code');
            });

            $this->info('✅ Password reset email sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
        }
    }
}
