<?php

return [
    'enabled' => env('REGISTRATION_ENABLED', false),
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'regular_amount' => (int) env('REGISTRATION_PRICE_CENTS', 820000),
    'promotion_amount' => env('REGISTRATION_PROMO_PRICE_CENTS') ? (int) env('REGISTRATION_PROMO_PRICE_CENTS') : null,
    'capacity' => (int) env('REGISTRATION_CAPACITY', 50),
    'contact' => env('REGISTRATION_CONTACT'),
    'currency' => 'mxn',
];
