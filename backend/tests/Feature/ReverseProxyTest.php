<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

/**
 * In Docker the site sits behind the school's reverse proxy. The proxy ends
 * HTTPS and passes the request on as plain HTTP, so unless the app trusts the
 * proxy's X-Forwarded-* headers, every page links back to http:// and the
 * browser blocks it as mixed content.
 */
class ReverseProxyTest extends TestCase
{
    private function loginPageVia(string $remoteAddr)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $remoteAddr])
            ->get('/login', [
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'fablab.cspc.edu.ph',
                'X-Forwarded-Port' => '443',
            ]);
    }

    public function test_a_trusted_proxy_makes_the_page_link_to_https(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,127.0.0.1']);

        $this->loginPageVia('172.18.0.1')
            ->assertOk()
            ->assertSee('action="https://fablab.cspc.edu.ph/login"', false);
    }

    public function test_headers_from_an_untrusted_address_are_ignored(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,127.0.0.1']);

        $this->loginPageVia('203.0.113.9')
            ->assertOk()
            ->assertDontSee('fablab.cspc.edu.ph')
            ->assertSee('action="http://', false);
    }

    public function test_no_proxy_is_trusted_unless_one_is_configured(): void
    {
        config(['trustedproxy.proxies' => null]);

        $this->loginPageVia('172.18.0.1')
            ->assertOk()
            ->assertDontSee('fablab.cspc.edu.ph');
    }

    public function test_an_https_app_url_keeps_links_on_https_without_proxy_headers(): void
    {
        config(['app.url' => 'https://fablab.cspc.edu.ph']);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/login'));
    }

    public function test_an_http_app_url_leaves_the_scheme_alone(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('http://', url('/login'));
    }
}
