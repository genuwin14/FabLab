<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Avatar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Someone without a photo used to get their initials from ui-avatars.com.
 * When that site stopped answering, every page logged
 * ERR_CONNECTION_TIMED_OUT and showed a broken avatar, so the initials are
 * now drawn by the app itself.
 */
class AvatarTest extends TestCase
{
    use RefreshDatabase;

    private function svg(string $src): string
    {
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $src);

        return base64_decode(substr($src, strlen('data:image/svg+xml;base64,')));
    }

    public function test_someone_without_a_photo_gets_their_initials_in_the_colours_asked_for(): void
    {
        $svg = $this->svg(Avatar::for(new User(['fullname' => 'John Doe']), '0e2e45', 'fff'));

        $this->assertStringContainsString('>JD</text>', $svg);
        $this->assertStringContainsString('fill="#0e2e45"', $svg);
        $this->assertStringContainsString('fill="#fff"', $svg);
    }

    public function test_initials_come_from_the_first_and_last_names(): void
    {
        $this->assertStringContainsString('>JC</text>', $this->svg(Avatar::initials('juan  dela cruz', '000', 'fff')));
        $this->assertStringContainsString('>M</text>', $this->svg(Avatar::initials('madonna', '000', 'fff')));
        $this->assertStringContainsString('>ÑR</text>', $this->svg(Avatar::initials('ñino reyes', '000', 'fff')));
    }

    public function test_an_account_with_no_name_falls_back_to_the_name_given(): void
    {
        $this->assertStringContainsString('>C</text>', $this->svg(Avatar::for(null, '000', 'fff', 'Customer')));
    }

    public function test_a_name_cannot_break_out_of_the_svg(): void
    {
        $svg = $this->svg(Avatar::initials('<script> &x', '000', 'fff'));

        $this->assertStringNotContainsString('<script', $svg);
        $this->assertStringContainsString('&lt;&amp;', $svg);
    }

    public function test_a_bad_colour_falls_back_rather_than_landing_in_the_markup(): void
    {
        $svg = $this->svg(Avatar::initials('John Doe', '"><script>', 'zzz'));

        $this->assertStringContainsString('fill="#0e2e45"', $svg);
        $this->assertStringContainsString('fill="#ffffff"', $svg);
    }

    public function test_a_photo_wins_over_the_initials(): void
    {
        $uploaded = new User(['fullname' => 'John Doe', 'photo' => 'profile_photos/john.jpg']);
        $google = new User(['fullname' => 'John Doe', 'photo' => 'https://lh3.googleusercontent.com/a/photo']);

        $this->assertSame(asset('storage/profile_photos/john.jpg'), Avatar::for($uploaded, '000', 'fff'));
        $this->assertSame('https://lh3.googleusercontent.com/a/photo', Avatar::for($google, '000', 'fff'));
    }

    public function test_no_page_asks_an_outside_site_for_the_avatar(): void
    {
        Sanctum::actingAs(User::create([
            'fullname' => 'John Doe', 'email' => 'john@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));

        $this->get(route('customer.shop'))
            ->assertOk()
            ->assertDontSee('ui-avatars.com', false)
            ->assertSee('data:image/svg+xml;base64,', false);
    }
}
