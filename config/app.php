<?php

return [

    /**
     * Uygulama Adı
     */
    'name' => env('APP_NAME', 'Laravel'),

    /**
     * Canlılık Durumu
     *
     * @return production|local
     */
    'env' => env('APP_ENV', 'production'),

    /**
     * Hata Ayıklama
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * Uygulama Bağlantısı
     *
     * @return http://localhost
     */
    'url' => env('APP_URL', 'http://localhost'),

    /**
     * Forum Bağlantısı
     *
     * @return localhost
     */
    'domain' => env('APP_DOMAIN', 'localhost'),

    /**
     * Gereçler Bağlantısı
     *
     * @return null
     */
    'asset_url' => env('ASSET_URL', null),

    /**
     * Zaman Dilimi
     *
     * @return Europe/Istanbul
     */
    'timezone' => 'Europe/Istanbul',

    /**
     * Sistem Dili
     *
     * @return tr
     */
    'locale' => 'tr',

    /**
     * Alternatif Sistem Dili
     *
     * - locale değeri bulunamazsa aranacak dil.
     *
     * @return tr
     */
    'fallback_locale' => 'tr',

    /**
     * Faker Dili
     *
     * - sahte veri üretiminde kullanılacak dil.
     */
    'faker_locale' => 'tr_TR',

    /**
     * App Key
     */
    'key' => env('APP_KEY'),

    /**
     * Sistem Şifresi
     *
     * - kritik durumlarda sorulacak şifre.
     */
    'password' => env('APP_PASSWORD'),

    /**
     * Chipher
     */
    'cipher' => 'AES-256-CBC',

    /**
     * Sistem Versiyonu
     */
    'version' => '1.3.1030 beta',

    /**
     * Destek Konuları
     */
    'ticket' => [
        'types' => [
            'other' => 'Diğer Konular',
            'odeme-bildirimi' => 'Ödeme Bildirimi'
        ]
    ],

    /**
     * Intro Anahtarları
     */
    'intro' => [
        'keys' => [
            'welcome.create.organisation',
            'search.module'
        ]
    ],

    /**
     * E-posta Bildirimi Türleri
     */
    'notifications' => [
        'important' => 'Önemli Olay Bildirimleri',
        'login' => 'Giriş Bildirimleri'
    ],

    /**
     * Options Tablosu Varsayılanları
     */
    'options' => [
        'email_alerts.server' => 'date_format:Y-m-d H:i:s',
        'email_alerts.log' => 'date_format:Y-m-d H:i:s',
        'youtube.status' => 'string|in:on,off',
        'youtube.index.video' => 'string|in:on,off',
        'youtube.index.comment' => 'string|in:on,off',
        'google.index.search' => 'string|in:on,off',
        'google.status' => 'string|in:on,off',
        'twitter.index.auto' => 'string|in:on,off',
        'twitter.trend.status' => 'string|in:on,off',
        'twitter.status' => 'string|in:on,off',
    ],

    /**
     * Uygulama Modülleri
     */
    'modules' => [
        'youtube' => 'YouTube',
        'twitter' => 'Twitter',
        'sozluk' => 'Sözlük',
        'news' => 'Haber',
        'shopping' => 'Alışveriş',
    ],

    /**
     * Slider Ayarları
     */
    'carousel' => [
        'patterns' => [
            'sphere-1' => 'AI 1',
            'sphere-2' => 'AI 2',
            'sphere-3' => 'AI 3',
            'sphere-4' => 'AI 4',
        ]
    ],

    /**
     * Modül Haritası
     */
    'search' => [
        'modules' => [
            1 => [
                'name' => 'Gerçek Zamanlı',
                'route' => 'realtime.stream',
                'icon' => 'timeline'
            ],
            2 => [
                'name' => 'Pin Grupları',
                'route' => 'pin.groups',
                'icon' => 'group_work'
            ],
            3 => [
                'name' => 'Kelime Takip Havuzu',
                'route' => 'twitter.keyword.list',
                'icon' => 'streetview'
            ],
            4 => [
                'name' => 'Kullanıcı Takip Havuzu',
                'route' => 'twitter.account.list',
                'icon' => 'directions_walk'
            ],
            5 => [
                'name' => 'Destek',
                'route' => 'settings.support',
                'icon' => 'help'
            ],
            24 => [
                'name' => 'Canlı Trend',
                'route' => 'trend.live',
                'icon' => 'whatshot'
            ],
            25 => [
                'name' => 'Trend Endeksi',
                'route' => 'trend.index',
                'icon' => 'trending_up'
            ],
            26 => [
                'name' => 'Trend Arşivi',
                'route' => 'trend.archive',
                'icon' => 'archive'
            ],

            /*
             * root modülleri
             */
            6 => [
                'name' => 'Sunucu Bilgisi',
                'route' => 'admin.monitoring.server',
                'root' => true,
                'icon' => 'desktop_mac'
            ],
            7 => [
                'name' => 'Log Ekranı',
                'route' => 'admin.monitoring.log',
                'root' => true,
                'icon' => 'code'
            ],
            8 => [
                'name' => 'Kuyruk Ekranı',
                'route' => 'admin.monitoring.queue',
                'root' => true,
                'icon' => 'queue'
            ],
            9 => [
                'name' => 'Arkaplan İşlemleri',
                'route' => 'admin.monitoring.background',
                'root' => true,
                'icon' => 'hourglass_empty'
            ],
            10 => [
                'name' => 'Kupon Yönetimi',
                'route' => 'admin.discount.coupon.list',
                'root' => true,
                'icon' => 'card_giftcard'
            ],
            11 => [
                'name' => 'İndirim Günleri',
                'route' => 'admin.discount.day.list',
                'root' => true,
                'icon' => 'card_giftcard'
            ],
            12 => [
                'name' => 'Vekil Sunucu Yönetimi',
                'route' => 'admin.proxies',
                'root' => true,
                'icon' => 'vpn_key'
            ],
            13 => [
                'name' => 'Alışveriş Botları',
                'route' => 'crawlers.shopping.list',
                'root' => true,
                'icon' => 'widgets'
            ],
            14 => [
                'name' => 'YouTube Ayarları',
                'route' => 'admin.youtube.settings',
                'root' => true,
                'icon' => 'widgets'
            ],
            15 => [
                'name' => 'Google Ayarları',
                'route' => 'admin.google.settings',
                'root' => true,
                'icon' => 'widgets'
            ],
            16 => [
                'name' => 'Twitter Ayarları',
                'route' => 'admin.twitter.settings',
                'root' => true,
                'icon' => 'widgets'
            ],
            17 => [
                'name' => 'Twitter Hesapları',
                'route' => 'admin.twitter.accounts',
                'root' => true,
                'icon' => 'person'
            ],
            18 => [
                'name' => 'Token Yönetimi',
                'route' => 'admin.twitter.tokens.json',
                'root' => true,
                'icon' => 'accessibility'
            ],
            19 => [
                'name' => 'Kelime Havuzu',
                'route' => 'admin.twitter.stream.keywords',
                'root' => true,
                'icon' => 'streetview'
            ],
            20 => [
                'name' => 'Kullanıcı Havuzu',
                'route' => 'admin.twitter.stream.accounts',
                'root' => true,
                'icon' => 'directions_walk'
            ],
            17 => [
                'name' => 'Medya Botları',
                'route' => 'crawlers.media.list',
                'root' => true,
                'icon' => 'widgets'
            ],
            18 => [
                'name' => 'Sözlük Botları',
                'route' => 'crawlers.sozluk.list',
                'root' => true,
                'icon' => 'widgets'
            ],
            19 => [
                'name' => 'Sayfa Yönetimi',
                'route' => 'admin.page.list',
                'root' => true,
                'icon' => 'pages'
            ],
            20 => [
                'name' => 'Destek Talepleri',
                'route' => 'admin.tickets',
                'root' => true,
                'icon' => 'help'
            ],
            21 => [
                'name' => 'Kullanıcı Yönetimi',
                'route' => 'admin.user.list',
                'root' => true,
                'icon' => 'people'
            ],
            22 => [
                'name' => 'Organizasyon Yönetimi',
                'route' => 'admin.organisation.list',
                'root' => true,
                'icon' => 'group_work'
            ],
            23 => [
                'name' => 'Carousel Yönetimi',
                'route' => 'admin.carousels',
                'root' => true,
                'icon' => 'view_carousel'
            ],
        ]
    ],

    /**
     * Log Dosyaları
     */
    'log_files' => [
        base_path('supervisor/logs/crawler.log'),
        base_path('supervisor/logs/elasticsearch.log'),
        base_path('supervisor/logs/email.log'),
        base_path('supervisor/logs/trigger.log'),
        base_path('supervisor/logs/horizon.log'),
        storage_path('logs/laravel.log')
    ],

    /**
     * Grup E-posta Adresi
     */
    'group_email' => env('GROUP_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Jenssegers\Agent\AgentServiceProvider::class,
        Torann\GeoIP\GeoIPServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,
        Orangehill\Iseed\IseedServiceProvider::class,
        Alaouy\Youtube\YoutubeServiceProvider::class,
        Laravel\Socialite\SocialiteServiceProvider::class,
        Barryvdh\DomPDF\ServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

        /*
         * Private Classes
         */
        'Term' => App\Utilities\Term::class,
        'System' => App\Utilities\SystemUtility::class,

        /*
         * Package Service Providers...
         */
        'Agent' => Jenssegers\Agent\Facades\Agent::class,
        'GeoIP' => Torann\GeoIP\Facades\GeoIP::class,
        'Image' => Intervention\Image\Facades\Image::class,
        'Youtube' => Alaouy\Youtube\Facades\Youtube::class,
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
        'Sentiment' => PHPSentiment\Sentiment::class,
        'PDF' => Barryvdh\DomPDF\Facade::class,

    ],

];
