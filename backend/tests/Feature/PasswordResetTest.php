<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
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

    /**
     * UniSMS takes a code text and then fails it ("OTP does not follow valid
     * format") unless it names the brand, the code, its purpose and when it
     * expires. The ten minutes it promises is the window checked above.
     */
    public function test_the_sms_code_is_worded_the_way_unisms_requires(): void
    {
        $this->user();
        $sent = [];
        $this->mock(SmsService::class, function (MockInterface $mock) use (&$sent) {
            $mock->shouldReceive('send')->andReturnUsing(function ($to, $message) use (&$sent) {
                $sent[] = [$to, $message];

                return true;
            });
        });

        $this->post('/forgot-password/send', ['email' => 'c@example.test', 'verification_mode' => 'sms'])
            ->assertRedirect(route('password.verify.show'));

        $this->assertCount(1, $sent);
        $this->assertSame('09123456789', $sent[0][0]);
        $this->assertSame(
            'Your FABLAB OTP code is ' . session('password_reset_otp')
                . ' for password reset. Please do not share. Expires in 10 minutes.',
            $sent[0][1]
        );
    }

    /** The code page used to name the email address even when the code was texted. */
    public function test_the_code_page_says_where_the_code_went(): void
    {
        $this->user();
        Mail::fake();
        $this->mock(SmsService::class, fn (MockInterface $mock) => $mock->shouldReceive('send')->andReturn(true));

        $this->post('/forgot-password/send', ['email' => 'c@example.test', 'verification_mode' => 'sms']);
        $this->get('/forgot-password/verify')
            ->assertOk()
            ->assertSee('phone number ending in')
            ->assertSee('6789')
            ->assertDontSee('c@example.test');

        $this->post('/forgot-password/send', ['email' => 'c@example.test', 'verification_mode' => 'email']);
        $this->get('/forgot-password/verify')
            ->assertOk()
            ->assertSee('c@example.test')
            ->assertDontSee('phone number ending in');
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
