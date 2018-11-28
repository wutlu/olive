<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'google' => [
        'analytics' => [
            'code' => env('GOOGLE_ANALYTICS_CODE')
        ],
        'recaptcha' => [
            'site_key' => env('GOOGLE_RECAPTCHA_SITE_KEY'),
            'secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY')
        ],
        'youtube' => [
            'api' => [
                'key' => env('YOUTUBE_API_KEY')
            ]
        ]
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CONSUMER_KEY'),
        'client_secret' => env('TWITTER_CONSUMER_SECRET'),
        'access_token' => env('TWITTER_ACCESS_TOKEN'),
        'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET'),

        'redirect' => 'http://olive.veri.zone/twitter/oauth/callback',

        'api' => [
            'trend' => [
                'id' => env('TWITTER_TREND_ID')
            ]
        ],
        'chunk_count' => env('TWITTER_TWEET_CHUNK', 1000),

        /*
         * belirtilen kelimeler kabul edilmeyecek.
         * sadece alfa nümerik karakterler ve boşluk geçerli olacaktır.
         */
        'unaccepted_keywords' => explode(PHP_EOL, file_get_contents(database_path('words/unaccepted.txt')))
    ],

    'medium' => [
        'url' => 'http://'
    ],
];
