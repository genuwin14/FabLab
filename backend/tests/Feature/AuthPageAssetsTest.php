<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The sign-in pages used to load the Google button's logo from svgrepo.com.
 * When that site stopped answering, every visit logged
 * ERR_CONNECTION_TIMED_OUT and the page kept loading. The logo is served by
 * the app now, like the avatars (see AvatarTest).
 */
class AuthPageAssetsTest extends TestCase
{
    public function test_the_google_button_uses_the_apps_own_logo(): void
    {
        $this->assertFileExists(public_path('img/google-g.svg'));

        foreach (['/login', '/register'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertDontSee('svgrepo.com', false)
                ->assertSee(asset('img/google-g.svg'), false);
        }
    }
}
