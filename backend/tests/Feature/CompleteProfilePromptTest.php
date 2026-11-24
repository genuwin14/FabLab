<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Google sign-ups arrive with an email and no phone number, so the shop had
 * no way to reach them. They're now asked for one — and can decline.
 */
class CompleteProfilePromptTest extends TestCase
{
    use RefreshDatabase;

    private function googleCustomer(string $contactNumber = ''): User
    {
        return User::create([
            'fullname' => 'Google Customer',
            'email' => 'g@example.test',
            'password' => null,
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone_verified' => true,
            'contact_number' => $contactNumber,
        ]);
    }

    public function test_a_customer_without_a_number_is_asked_for_one(): void
    {
        Sanctum::actingAs($this->googleCustomer());

        $this->get('/complete-profile')->assertOk()->assertSee('Contact Number', false);
    }

    /**
     * The number is kept, and texted a code so it can be proven too. Proving
     * it is offered, not forced: the account is verified by its Google email,
     * so the code page can be skipped straight into the shop.
     */
    public function test_saving_the_number_texts_it_a_code_and_lets_them_skip(): void
    {
        $sent = [];
        $this->mock(\App\Services\SmsService::class, function ($mock) use (&$sent) {
            $mock->shouldReceive('send')->andReturnUsing(function ($to, $message) use (&$sent) {
                $sent[] = $to;

                return true;
            });
        });

        $user = $this->googleCustomer();
        Sanctum::actingAs($user);

        $this->post('/complete-profile', ['contact_number' => '09171234567'])
            ->assertRedirect(route('verify.code'));

        $user->refresh();
        $this->assertSame('09171234567', $user->contact_number);
        $this->assertFalse((bool) $user->phone_verified, 'Typed in, not yet proven.');
        $this->assertSame(['09171234567'], $sent);

        $this->get(route('verify.code'))->assertOk()->assertSee('Skip for now');
        $this->get(route('customer.shop'))->assertOk();
    }

    public function test_the_number_is_required_when_submitting(): void
    {
        $user = $this->googleCustomer();
        Sanctum::actingAs($user);

        $this->post('/complete-profile', ['contact_number' => ''])
            ->assertSessionHasErrors('contact_number');

        $this->assertSame('', $user->refresh()->contact_number);
    }

    /** Skipping is a plain link to their own landing page — nobody gets stuck. */
    public function test_a_customer_who_already_has_a_number_is_not_asked(): void
    {
        Sanctum::actingAs($this->googleCustomer('09171234567'));

        $this->get('/complete-profile')->assertRedirect(route('customer.shop'));
    }

    public function test_the_prompt_is_reachable_by_every_role(): void
    {
        $staff = User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '', 'phone_verified' => true,
        ]);

        Sanctum::actingAs($staff);

        // Shared route, not gated by role.
        $this->get('/complete-profile')->assertOk();
    }
}
