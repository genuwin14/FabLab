<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleCallbackRouteTest extends TestCase
{
    /**
     * Google sends the user back to whatever GOOGLE_REDIRECT_URI says. Local
     * setups use /login/google/callback; the deployment guides, the Docker
     * settings and fablab.sh use /auth/google/callback. Either must land in
     * the callback handler, which turns a visit without Google's state into
     * a redirect to the login page, and never a 404.
     */
    public function test_both_callback_addresses_reach_the_google_handler(): void
    {
        foreach (['/login/google/callback', '/auth/google/callback'] as $path) {
            $this->get($path)
                ->assertRedirect(route('login'))
                ->assertSessionHas('error', 'Google authentication failed.');
        }
    }
}
