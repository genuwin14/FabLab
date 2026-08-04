<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A code sent to an email address proves the email, not the phone. Verifying
 * by email used to set phone_verified as well, so an unconfirmed number read
 * as confirmed everywhere it mattered.
 */
class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_verifying_by_email_confirms_the_email_only(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->post('/verify-code', ['otp' => '123456', 'verification_mode' => 'email'])
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

        $this->post('/verify-code', ['otp' => '123456', 'verification_mode' => 'sms'])
            ->assertRedirect(route('customer.shop'));

        $user->refresh();
        $this->assertTrue((bool) $user->phone_verified);
        $this->assertNull($user->email_verified_at);
    }

    public function test_a_wrong_code_verifies_nothing(): void
    {
        $user = $this->unverified();
        Sanctum::actingAs($user);

        $this->post('/verify-code', ['otp' => '000000', 'verification_mode' => 'sms'])
            ->assertSessionHasErrors('otp');

        $user->refresh();
        $this->assertFalse((bool) $user->phone_verified);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('123456', $user->phone_verification_code);
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
