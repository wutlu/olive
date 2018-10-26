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
        'unaccepted_keywords' => [
			'bir',
			'icin',
			'cok',
			'kadar',
			'ama',
			'gibi',
			'ile',
			'ben',
			'daha',
			'var',
			'her',
			'diye',
			'olan',
			'guzel',
			'iyi',
			'yok',
			'sen',
			'sonra',
			'bile',
			'sey',
			'olarak',
			'olsun',
			'degil',
			'zaman',
			'nasil',
			'gun',
			'hic',
			'beni',
			'boyle',
			'seni',
			'tek',
			'bana',
			'artik',
			'once',
			'benim',
			'allah',
			'ilk',
			'biz',
			'sana',
			'buyuk',
			'son',
			'sadece',
			'turk',
			'yeni',
			'insan',
			'tum',
			'yine',
			'devam',
			'neden',
			'senin',
			'hep',
			'iki',
			'kendi',
			'eden',
			'turkiye',
			'bunu',
			'olmak',
			'yil',
			'oyle',
			'oldu',
			'olur',
			'siz',
			'ayni',
			'simdi',
			'bugun',
			'baska',
			'oldugu',
			'biri',
			'bizim',
			'sayin',
			'mutlu',
			'karsi',
			'adam',
			'fazla',
			'zaten',
			'bin',
			'oldugunu',
			'insanlar',
			'bize',
			'hicbir',
			'herkes',
			'size',
			'hala',
			'icinde',
			'kim',
			'bizi',
			'gece',
			'onu',
			'tam',
			'kisi',
			'butun',
			'olsa',
			'gelen',
			'saat',
			'gore',
			'ise',
			'takip',
			'kimse',
			'falan',
			'geri',
			'yapan',
			'biraz',
			'varsa',
			'yer',
			'dogru',
			'bak',
			'sizin',
			'kotu',
			'cunku',
			'dunya',
			'istiyorum',
			'tarafindan',
			'araciligiyla',
			'birlikte',
			'lutfen',
			'diyen',
			'niye',
			'sizi',
			'geldi',
			'genel',
			'gunu',
			'destek',
			'para',
			'ali',
			'olacak',
			'seyi',
			'seyler',
			'yerine',
			'iste',
			'kadin',
			'kendini',
			'kez',
			'gerek',
			'zor',
			'gunaydin',
			'asla',
			'sekilde',
			'demek',
			'hayirli',
			'yere',
			'kabul',
			'kiz',
			'cocuk',
			'ona',
			'yoksa',
			'milyon',
			'cumhurbaskani',
			'veya',
			'yani',
			'hem',
			'isteyen',
			'amk',
			'etmek',
			'baskani',
			'yerde',
			'sene',
			'bende',
			'abd',
			'ilgili',
			'sehit',
			'olmayan',
			'belli'
        ]
    ],

    'medium' => [
        'url' => 'http://'
    ],
];
