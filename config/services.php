<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'base_url' => 'https://api.razorpay.com'
    ],

    'paydeer' => [
        'key' => env('PAYDEER_CLIENT_KEY'),
        'secret' => env('PAYDEER_CLIENT_SECRET'),
        'base_url' => 'https://paydeer.in'
    ],

    'eko' => [
        'initiator_id' => env('EKO_INITIATOR_ID'),
        'key' => env('EKO_KEY'),
        'developer_key' => env('EKO_DEVELOPER_KEY'),
        'base_url' => 'https://api.eko.in:25002/ekoicici'
    ],

    'waayupay' => [
        'user_key' => env('WAAYUPAY_USER_KEY'),
        'email' => env('WAAYUPAY_EMAIL'),
        'password' => env('WAAYUPAY_PASSWORD'),
        'base_url' => 'https://payout.waayupay.com/api'
    ],

    'paysprint' => [
        'jwt' => env('PAYSPRINT_JWT'),
        'partner_id' => env('PAYSPRINT_PARTNERID'),
        'authorised_key' => env('PAYSPRINT_AUTHORISED_KEY'),
        'encryption_key' => env('PAYSPRINT_ENCRYPTION_KEY'),
        'encryption_iv' => env('PAYSPRINT_ENCRYPTION_IV'),
        'base_url' => 'https://sit.paysprint.in/service-api/api/v1/service'
    ],

    'rbl' => [
        'key' => env('RBL_KEY'),
        'secret' => env('RBL_SECRET'),
        'client_id' => env('RBL_CLIENT_ID'),
        'client_secret' => env('RBL_CLIENT_SECRET'),
        'base_url' => 'https://apideveloper.rblbank.com/test/qa/rbl'
    ],

    'safexpay' => [
        'merchant_id' => env('SAFEXPAY_MERCHANT_ID'),
        'merchant_key' => env('SAFEXPAY_MERCHANT_KEY'),
        'base_url' => 'https://remittance.safexpay.com/agWalletAPI/v2/agg',
        'iv' => env('SAFEXPAY_IV')
    ],

    'groscope' => [
        'base_url' => 'https://login.groscope.com/api',
        'token' => env('GROSCOPE_TOKEN')
    ],

    'instantpay' => [
        'base_url' => 'https://api.instantpay.in',
        'client_id' => env('INSTANTPAY_CLIENT_ID'),
        'client_secret' => env('INSTANTPAY_CLIENT_SECRET'),
    ],

    'payninja' => [
        'base_url' => 'https://api-staging.payninja.in',
        'client_id' => env('PAYNINJA_CLIENT_ID'),
        'client_secret' => env('PAYNINJA_CLIENT_SECRET'),
        'decrypt_secret' => env('PAYNINJA_DECRYPT_KEY')
    ],

    'cashfree' => [
        'base_url' => 'https://api.cashfree.com',
        'client_id' => env('CASHFREE_CLIENT_ID'),
        'client_secret' => env('CASHFREE_CLIENT_SECRET'),
    ],

    'payscope' => [
        'base_url' => 'https://login.groscope.in/v1',
        'client_id' => env('PAYSOCPE_CLIENT_ID'),
        'client_secret' => env('PAYSOCPE_CLIENT_SECRET'),
    ],

    'rafifintech' => [
        'base_url' => 'https://rafifintech.in/v1',
        'client_id' => env('RAFIFINTECH_CLIENT_ID'),
        'client_secret' => env('RAFIFINTECH_CLIENT_SECRET'),
    ]
];
