<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS driver
    |--------------------------------------------------------------------------
    |
    | 'macrodroid' — an Android phone running MacroDroid sends the message from
    |                its own SIM. Laravel makes an outbound HTTP call, so this
    |                works from localhost with no public URL of its own.
    | 'philsms'    — the paid PhilSMS gateway.
    | 'log'        — writes the message to the Laravel log and reports success.
    |                Use it in development so nothing is actually sent.
    |
    */

    'driver' => env('SMS_DRIVER', 'philsms'),

    /*
    |--------------------------------------------------------------------------
    | Country code
    |--------------------------------------------------------------------------
    |
    | Used to turn a local number like 09171234567 into an international one.
    | 63 is the Philippines.
    |
    */

    'country_code' => env('SMS_COUNTRY_CODE', '63'),

    'drivers' => [

        'macrodroid' => [

            // Either the MacroDroid cloud webhook…
            //   https://trigger.macrodroid.com/<device-id>/<identifier>
            // …or, on the same Wi-Fi, MacroDroid's local HTTP server:
            //   http://192.168.1.50:8080/<identifier>
            //
            // The cloud URL is the reliable one: a phone's LAN address changes,
            // and the machine running Laravel has to be on the same network.
            'url' => env('MACRODROID_WEBHOOK_URL'),

            // MacroDroid copies query-string parameters into global variables
            // of the SAME NAME — and the variable has to already exist in the
            // app, or the value is silently dropped.
            'number_param' => env('MACRODROID_NUMBER_PARAM', 'sms_number'),
            'message_param' => env('MACRODROID_MESSAGE_PARAM', 'sms_text'),

            // How the recipient is written before being handed to Android:
            //   'e164'  → +639171234567   (safest, and what Android prefers)
            //   'local' → 09171234567     (try this if your carrier rejects +63)
            //   'raw'   → exactly as stored
            'number_format' => env('MACRODROID_NUMBER_FORMAT', 'e164'),

            // Kept short deliberately. This call happens inline while someone
            // is registering, so a phone that is off must not hold up the page.
            'timeout' => (int) env('MACRODROID_TIMEOUT', 10),
        ],

        'philsms' => [
            'url' => env('PHILSMS_URL', 'https://dashboard.philsms.com/api/v3'),
            'token' => env('PHILSMS_API_TOKEN'),
            'sender' => env('PHILSMS_SENDER', 'FabLabs'),
            'timeout' => (int) env('PHILSMS_TIMEOUT', 15),
        ],

        'log' => [
            'channel' => env('SMS_LOG_CHANNEL'),
        ],

    ],

];
