<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "Add phone number verification."
 *
 * Sign-up texted a code, but the code could be skipped by typing a page's
 * address, a number changed later was never checked, and signing up again
 * with an existing email took over any account whose phone wasn't verified
 * (email sign-ups, Google ones, staff made with users:create). Now every page
 * needs a verified phone or email, a new number must prove it gets texts
 * before it replaces a verified one, and only an unfinished customer sign-up
 * can be signed up for again.
 */
class PhoneVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array{0: string, 1: string}> */
    private array $texts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('send')->andReturnUsing(function ($to, $message) {
                $this->texts[] = [$to, $message];

                return true;
            });
        });
    }

    // ------------------------------------------------------ signing up again

    public function test_signing_up_with_a_verified_accounts_email_is_refused(): void
    {
        $owner = $this->customer(['email_verified_at' => now(), 'phone_verified' => false]);

        $this->post('/register', $this->registration())->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertTrue(password_verify('owners-password', $owner->refresh()->password), 'The owner keeps their password.');
    }

    public function test_signing_up_with_a_staff_email_is_refused(): void
    {
        // What users:create makes without a phone: email verified, phone not.
        $staff = User::create([
            'fullname' => 'Staff', 'email' => 'taken@example.test', 'password' => 'owners-password',
            'role' => 'staff', 'contact_number' => '', 'phone_verified' => false, 'email_verified_at' => now(),
        ]);

        $this->post('/register', $this->registration())->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame('staff', $staff->refresh()->role);
        $this->assertTrue(password_verify('owners-password', $staff->password));
    }

    public function test_an_unfinished_sign_up_can_be_tried_again(): void
    {
        $this->customer(['phone_verified' => false]);

        $this->post('/register', $this->registration())->assertRedirect(route('verify.code'));

        $this->assertAuthenticated();
        $this->assertSame('Retried Name', User::where('email', 'taken@example.test')->value('fullname'));
    }

    // ------------------------------------------------------------ the gate

    public function test_an_account_that_verified_nothing_cannot_skip_the_code(): void
    {
        Sanctum::actingAs($this->customer(['phone_verified' => false]));

        $this->get(route('customer.shop'))->assertRedirect(route('verify.code'));
        $this->getJson(route('customer.cart.count'))->assertForbidden();
        $this->post(route('customer.cart.add'), ['product_id' => 1])->assertForbidden();

        // The way out stays open.
        $this->get(route('verify.code'))->assertOk();
    }

    public function test_either_channel_lets_the_account_in(): void
    {
        Sanctum::actingAs($this->customer(['phone_verified' => false, 'email_verified_at' => now()]));
        $this->get(route('customer.shop'))->assertOk();

        Sanctum::actingAs($this->customer(['email' => 'p@example.test', 'phone_verified' => true]));
        $this->get(route('customer.shop'))->assertOk();
    }

    // --------------------------------------------------------- the channel

    public function test_a_texted_code_confirms_the_phone_whatever_the_form_says(): void
    {
        $this->post('/register', $this->registration(['verification_mode' => 'sms']))->assertRedirect(route('verify.code'));
        $user = User::where('email', 'taken@example.test')->sole();

        $this->post('/verify-code', ['otp' => $user->phone_verification_code, 'verification_mode' => 'email'])
            ->assertRedirect(route('customer.shop'));

        $user->refresh();
        $this->assertTrue((bool) $user->phone_verified);
        $this->assertNull($user->email_verified_at, 'The code never went to the email.');
    }

    public function test_an_unverified_account_can_switch_to_the_other_channel(): void
    {
        Sanctum::actingAs($this->customer(['phone_verified' => false]));

        $this->withSession(['verification_channel' => 'sms'])
            ->get(route('verify.code'))
            ->assertOk()
            ->assertSee('Email the code to me instead');
    }

    // ------------------------------------------------- changing the number

    public function test_a_verified_number_stays_until_the_new_one_is_proven(): void
    {
        $user = $this->customer(['contact_number' => '09170000001', 'phone_verified' => true]);
        Sanctum::actingAs($user);

        $this->put(route('customer.profile.update'), $this->profile(['contact_number' => '09170000002']))
            ->assertRedirect(route('verify.code'));

        $user->refresh();
        $this->assertSame('09170000001', $user->contact_number, 'Not swapped before the new number proves itself.');
        $this->assertTrue((bool) $user->phone_verified);
        $this->assertSame('09170000002', end($this->texts)[0]);

        $this->get(route('verify.code'))->assertSee('new number ending in')->assertSee('0002')->assertSee('Skip for now');

        $this->post('/verify-code', ['otp' => $user->phone_verification_code])->assertRedirect(route('customer.shop'));

        $user->refresh();
        $this->assertSame('09170000002', $user->contact_number);
        $this->assertTrue((bool) $user->phone_verified);
    }

    public function test_an_unverified_number_is_replaced_and_texted_a_code(): void
    {
        $user = $this->customer(['contact_number' => '09170000001', 'phone_verified' => false, 'email_verified_at' => now()]);
        Sanctum::actingAs($user);

        $this->put(route('customer.profile.update'), $this->profile(['contact_number' => '09170000003']))
            ->assertRedirect(route('verify.code'));

        $user->refresh();
        $this->assertSame('09170000003', $user->contact_number);
        $this->assertFalse((bool) $user->phone_verified);
        $this->assertSame('09170000003', end($this->texts)[0]);
    }

    public function test_saving_the_profile_without_a_new_number_sends_nothing(): void
    {
        $user = $this->customer(['contact_number' => '09170000001', 'phone_verified' => true]);
        Sanctum::actingAs($user);

        $this->put(route('customer.profile.update'), $this->profile(['contact_number' => '09170000001']))
            ->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertSame([], $this->texts);
    }

    public function test_the_profile_says_whether_the_number_is_verified(): void
    {
        Sanctum::actingAs($this->customer(['contact_number' => '09170000001', 'phone_verified' => false, 'email_verified_at' => now()]));
        $this->get(route('customer.shop'))->assertSee('Not verified')->assertSee('Verify now');

        Sanctum::actingAs($this->customer(['email' => 'v@example.test', 'contact_number' => '09170000009', 'phone_verified' => true]));
        $this->get(route('customer.shop'))->assertSee('bi-patch-check-fill me-1"></i>Verified', false);
    }

    public function test_verify_now_texts_the_number_on_the_account(): void
    {
        $user = $this->customer(['contact_number' => '09170000001', 'phone_verified' => false, 'email_verified_at' => now()]);
        Sanctum::actingAs($user);

        // A new number someone typed and walked away from.
        $this->withSession(['pending_contact_number' => '09179999999'])
            ->post(route('verify.code.resend'), ['verification_mode' => 'sms', 'target' => 'current'])
            ->assertRedirect(route('verify.code'));

        $this->assertSame('09170000001', end($this->texts)[0]);
    }

    // ---------------------------------------------------------------- helpers

    private function customer(array $overrides = []): User
    {
        return User::create($overrides + [
            'fullname' => 'Owner', 'email' => 'taken@example.test', 'password' => 'owners-password',
            'role' => 'customer', 'contact_number' => '09123456789',
        ]);
    }

    private function registration(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Retried Name', 'email' => 'taken@example.test', 'phone' => '09171112222',
            'password' => 'new-password', 'password_confirmation' => 'new-password', 'verification_mode' => 'sms',
        ];
    }

    private function profile(array $overrides = []): array
    {
        return $overrides + ['fullname' => 'Owner', 'email' => auth()->user()->email];
    }
}
