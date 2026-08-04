<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The reset flow carries a code in the session with a ten-minute life. These
 * pin its behaviour so the auth controller can be tidied without guessing.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    public function test_an_unknown_email_is_refused(): void
    {
        $this->post('/forgot-password', ['email' => 'nobody@example.test'])
            ->assertSessionHasErrors('email');
    }

    public function test_a_known_email_offers_masked_delivery_choices(): void
    {
        $this->user();

        $this->post('/forgot-password', ['email' => 'c@example.test'])
            ->assertOk()
            ->assertViewHas('maskedEmail')
            ->assertViewHas('maskedPhone');
    }

    public function test_the_whole_reset_journey(): void
    {
        $user = $this->user();
        Mail::fake();

        $this->post('/forgot-password/send', [
            'email' => 'c@example.test',
            'verification_mode' => 'email',
        ])->assertRedirect(route('password.verify.show'));

        $otp = session('password_reset_otp');
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        $this->post('/forgot-password/verify', ['otp' => $otp])
            ->assertRedirect(route('password.reset.form'));

        $this->post('/reset-password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));

        // The one-time material is cleared behind us.
        $this->assertNull(session('password_reset_otp'));
        $this->assertNull(session('password_reset_token'));
    }

    public function test_a_wrong_code_is_rejected(): void
    {
        $this->user();
        Mail::fake();

        $this->post('/forgot-password/send', ['email' => 'c@example.test', 'verification_mode' => 'email']);

        $this->post('/forgot-password/verify', ['otp' => '000000'])
            ->assertSessionHasErrors('otp');
    }

    public function test_an_expired_code_sends_them_back_to_the_start(): void
    {
        $this->user();
        Mail::fake();

        $this->post('/forgot-password/send', ['email' => 'c@example.test', 'verification_mode' => 'email']);
        $otp = session('password_reset_otp');

        // Ten minutes and one second later.
        session(['password_reset_expires' => now()->subSecond()]);

        $this->post('/forgot-password/verify', ['otp' => $otp])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_the_reset_form_is_closed_without_a_verified_code(): void
    {
        $this->get('/reset-password')->assertRedirect(route('password.request'));

        $this->post('/reset-password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));
    }
}
