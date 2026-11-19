<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * There is no screen for making admin or staff accounts, so a fresh server got
 * its first admin from the demo seeder, password "password". users:create
 * makes one with a real password instead.
 */
class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_admin_who_can_sign_in_straight_away(): void
    {
        $this->artisan('users:create', [
            '--role' => 'admin',
            '--name' => 'Lab Head',
            '--email' => 'head@cspc.edu.ph',
            '--phone' => '09171234567',
            '--password' => 'long-enough-1',
        ])->assertSuccessful();

        $user = User::where('email', 'head@cspc.edu.ph')->firstOrFail();

        $this->assertSame('admin', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue((bool) $user->phone_verified);
        $this->assertTrue(Hash::check('long-enough-1', $user->password));

        $this->post('/login', ['email' => 'head@cspc.edu.ph', 'password' => 'long-enough-1'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_the_phone_number_is_optional(): void
    {
        $this->artisan('users:create', [
            '--role' => 'staff',
            '--name' => 'Lab Staff',
            '--email' => 'staff@cspc.edu.ph',
            '--phone' => '',
            '--password' => 'long-enough-1',
        ])->assertSuccessful();

        $user = User::where('email', 'staff@cspc.edu.ph')->firstOrFail();

        $this->assertSame('staff', $user->role);
        $this->assertNull($user->contact_number);
        $this->assertFalse((bool) $user->phone_verified);
    }

    public function test_it_asks_for_whatever_was_left_out(): void
    {
        $this->artisan('users:create')
            ->expectsChoice('Role', 'staff', ['admin' => 'Admin', 'staff' => 'Staff'])
            ->expectsQuestion('Full name', 'Asked Staff')
            ->expectsQuestion('E-mail address (they sign in with it)', 'asked@cspc.edu.ph')
            ->expectsQuestion('Mobile number (optional)', '')
            ->expectsQuestion('Password', 'long-enough-1')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'asked@cspc.edu.ph', 'role' => 'staff']);
    }

    public function test_it_refuses_a_customer_role_a_taken_email_or_a_short_password(): void
    {
        User::create([
            'fullname' => 'Existing', 'email' => 'taken@cspc.edu.ph', 'password' => 'password',
            'role' => 'customer', 'phone_verified' => true,
        ]);

        $base = ['--name' => 'X', '--phone' => '', '--password' => 'long-enough-1'];

        $this->artisan('users:create', [...$base, '--role' => 'customer', '--email' => 'new@cspc.edu.ph'])
            ->assertFailed();
        $this->artisan('users:create', [...$base, '--role' => 'staff', '--email' => 'taken@cspc.edu.ph'])
            ->assertFailed();
        $this->artisan('users:create', [...$base, '--role' => 'staff', '--email' => 'new@cspc.edu.ph', '--password' => 'short'])
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'new@cspc.edu.ph']);
        $this->assertSame(1, User::count());
    }
}
