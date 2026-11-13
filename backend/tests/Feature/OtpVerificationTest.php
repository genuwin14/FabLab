<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * A code sent to an email address proves the email, not the phone. Verifying
 * by email used to set phone_verified as well, so an unconfirmed number read
 * as confirmed everywhere it mattered.
 *
 * A code also lives ten minutes. UniSMS fails any code text that doesn't say
 * when it expires, so the text promises ten minutes and the check keeps it true.
 */
class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** The account-verification wording UniSMS delivers; group 1 is the code. */
    private const TEXT = '/^Your FABLAB OTP code is (\d{6}) for account verification\. Please do not share\. Expires in 10 minutes\.$/';

    private function unverified(): User
    {
        return User::create([
            'fullname' => 'New Customer',
            'email' => 'new@example.test',
            'password' => 'password',
            'role' => 'customer',
            'contact_number' => '09123456789',
            'phone_verified' => false,
            'phone_verification_code' => '123456',
        ]);
    }

    /** The session a code leaves behind while it is inside its ten minutes. */
    private function liveCode(): array
    {
        return ['phone_verification_expires' => now()->addMinutes(10)];
    }

    /** Stands in for the gateway and keeps whatever it was asked to send. */
    private function catchTexts(array &$sent): void
    {
        $this->mock(SmsService::class, function (MockInterface $mock) use (&$sent) {
            $mock->shouldReceive('send')->andReturnUsing(function ($to, $message) use (&$sent) {
                $sent[] = [$to, $message];

                return true;
            });
        });
    }

    public function test_verifying_by_email_confirms_the_email_only(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->withSession($this->liveCode())
            ->post('/verify-code', ['otp' => '123456', 'verification_mode' => 'email'])
            ->assertRedirect(route('customer.shop'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse((bool) $user->phone_verified);
        $this->assertNull($user->phone_verification_code);
    }

    public function test_verifying_by_sms_confirms_the_phone(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->withSession($this->liveCode())
            ->post('/verify-code', ['otp' => '123456', 'verification_mode' => 'sms'])
            ->assertRedirect(route('customer.shop'));

        $user->refresh();
        $this->assertTrue((bool) $user->phone_verified);
        $this->assertNull($user->email_verified_at);
    }

    public function test_a_wrong_code_verifies_nothing(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->withSession($this->liveCode())
            ->post('/verify-code', ['otp' => '000000', 'verification_mode' => 'sms'])
            ->assertSessionHasErrors('otp');

        $user->refresh();
        $this->assertFalse((bool) $user->phone_verified);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('123456', $user->phone_verification_code);
    }

    public function test_a_code_past_its_ten_minutes_verifies_nothing(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->withSession(['phone_verification_expires' => now()->subSecond()])
            ->post('/verify-code', ['otp' => '123456', 'verification_mode' => 'sms'])
            ->assertSessionHasErrors('otp');

        $user->refresh();
        $this->assertFalse((bool) $user->phone_verified);
        $this->assertSame('123456', $user->phone_verification_code);
    }

    /** A code from before the rule, or from another browser, has no clock to check. */
    public function test_a_code_with_no_expiry_on_record_verifies_nothing(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->post('/verify-code', ['otp' => '123456', 'verification_mode' => 'sms'])
            ->assertSessionHasErrors('otp');

        $this->assertFalse((bool) $user->refresh()->phone_verified);
    }

    public function test_registering_by_sms_texts_a_code_unisms_will_deliver(): void
    {
        $sent = [];
        $this->catchTexts($sent);

        $this->post('/register', [
            'name' => 'New Customer',
            'email' => 'fresh@example.test',
            'phone' => '09171234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'verification_mode' => 'sms',
        ])->assertRedirect(route('verify.code'));

        $this->assertCount(1, $sent);
        [$to, $message] = $sent[0];
        $this->assertSame('09171234567', $to);
        $this->assertMatchesRegularExpression(self::TEXT, $message);

        // The code in the text is the one on the account, and its clock has started.
        preg_match(self::TEXT, $message, $match);
        $this->assertSame($match[1], User::where('email', 'fresh@example.test')->value('phone_verification_code'));
        $this->assertTrue(now()->addMinutes(9)->lessThan(session('phone_verification_expires')));
        $this->assertTrue(now()->addMinutes(10)->greaterThanOrEqualTo(session('phone_verification_expires')));
    }

    public function test_resending_texts_a_fresh_code_on_a_fresh_clock(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);
        $sent = [];
        $this->catchTexts($sent);

        // The first code has run out; Resend Code is the way back in.
        $this->withSession(['phone_verification_expires' => now()->subMinute()])
            ->post('/verify-code/resend', ['verification_mode' => 'sms'])
            ->assertRedirect(route('verify.code'));

        $this->assertCount(1, $sent);
        $this->assertMatchesRegularExpression(self::TEXT, $sent[0][1]);
        preg_match(self::TEXT, $sent[0][1], $match);
        $this->assertSame($match[1], $user->refresh()->phone_verification_code);

        $this->post('/verify-code', ['otp' => $match[1], 'verification_mode' => 'sms'])
            ->assertRedirect(route('customer.shop'));

        $this->assertTrue((bool) $user->refresh()->phone_verified);
        $this->assertNull(session('phone_verification_expires'));
    }

    /**
     * Access is granted on either channel, so an account verified by email and
     * carrying an unconfirmed phone number must still be able to sign in.
     */
    public function test_an_email_verified_account_can_sign_in(): void
    {
        // The state the email path leaves behind: email confirmed, phone not.
        $user = $this->unverified();
        $user->email_verified_at = now();
        $user->phone_verification_code = null;
        $user->save();

        $this->post('/login', ['email' => 'new@example.test', 'password' => 'password'])
            ->assertRedirect(route('customer.shop'));

        $this->assertAuthenticatedAs($user);
    }
}
