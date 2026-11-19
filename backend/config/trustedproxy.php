<?php

/*
|--------------------------------------------------------------------------
| Trusted proxies
|--------------------------------------------------------------------------
|
| Laravel's TrustProxies middleware reads this when nothing is set in code
| (Illuminate\Http\Middleware\TrustProxies::setTrustedProxyIpAddresses).
|
| Left unset, no proxy is trusted. That is right for Hostinger and for local
| work, where requests reach PHP directly. In the Docker setup the site sits
| behind the school's reverse proxy, which ends HTTPS and forwards plain
| HTTP. Trusting that proxy's X-Forwarded-* headers is what lets the app see
| the visitor's real address and know the visit was secure.
|
| A comma-separated list of addresses or ranges (10.0.0.0/8), or "*" to
| trust whatever connects. docker/.env.example sets the private ranges.
|
*/

return [

    'proxies' => env('TRUSTED_PROXIES'),

];
