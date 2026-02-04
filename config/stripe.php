<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key and secret key give you access to Stripe's
    | API. The "publishable" key is typically used when interacting with
    | Stripe.js while the "secret" key accesses private API endpoints.
    |
    */

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This secret is used to verify that webhook requests are actually
    | coming from Stripe. You can find this in your Stripe Dashboard
    | under Developers > Webhooks > Signing secret.
    |
    */

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Tolerance
    |--------------------------------------------------------------------------
    |
    | Maximum difference allowed between the current time and the webhook's
    | timestamp (in seconds). This helps prevent replay attacks.
    |
    */

    'webhook_tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to use for Stripe payments. This should be
    | a valid ISO 4217 currency code (e.g., 'usd', 'eur', 'gbp').
    |
    */

    'currency' => env('STRIPE_CURRENCY', 'usd'),

];
