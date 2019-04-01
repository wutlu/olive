<?php

return [
    /**
     * Uygulama Adı
     */
    'name' => env('APP_NAME', 'Laravel'),

    /**
     * Canlılık Durumu
     *
     * @var production|local
     */
    'env' => env('APP_ENV', 'production'),

    /**
     * İlk Kurulum
     *
     * @var boolean
     */
    'first_migration' => env('FIRST_MIGRATION', false),

    /**
     * Hata Ayıklama
     *
     * @var boolean
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * Uygulama Bağlantısı
     *
     * @var http://localhost
     */
    'url' => env('APP_URL', 'http://localhost'),

    /**
     * Forum Bağlantısı
     *
     * @var localhost
     */
    'domain' => env('APP_DOMAIN', 'localhost'),

    /**
     * SSL
     *
     * @var boolean
     */
    'ssl' => env('SSL', false),

    /**
     * Gereçler Bağlantısı
     *
     * @var null
     */
    'asset_url' => env('ASSET_URL', null),

    /**
     * Zaman Dilimi
     *
     * @var Europe/Istanbul
     */
    'timezone' => 'Europe/Istanbul',

    /**
     * Sistem Dili
     *
     * @var tr
     */
    'locale' => 'tr',

    /**
     * Alternatif Sistem Dili
     *
     * - Locale değeri bulunamazsa aranacak dil.
     *
     * @var tr
     */
    'fallback_locale' => 'tr',

    /**
     * Faker Dili
     *
     * - Sahte veri üretiminde kullanılacak dil.
     */
    'faker_locale' => 'tr_TR',

    /**
     * App Key
     */
    'key' => env('APP_KEY'),

    /**
     * Sistem Şifresi
     *
     * - Kritik durumlarda sorulacak şifre.
     */
    'password' => env('APP_PASSWORD'),

    /**
     * Chipher
     */
    'cipher' => 'AES-256-CBC',

    /**
     * Admin Grup E-posta Adresi
     *
     * - Sistemsel sorunlar bu e-posta grubuna gönderilir.
     */
    'group_email' => env('GROUP_EMAIL', 'admin@veri.zone'),

    /**
     * Admin Organizasyon ID
     *
     * - Herhangi bir ROOT yetkisine sahip organizasyon ID'si.
     */
    'organisation_id_root' => env('ORGANISATION_ID_ROOT', 1),

    'alexa' => [
        /**
         * Alexa Rank Min
         *
         * - Alexa sıralaması girilen değerden yüksek olan
         * kaynak sitelerinden veri toplanmaz.
         */

        'rank_min' => env('ALEXA_RANK_MIN', 20000)
    ],

    /**
     * Log Dosyaları
     */
    'log_files' => [
        base_path('supervisor/logs/crawler.log'),
        base_path('supervisor/logs/power-crawler.log'),
        base_path('supervisor/logs/error-crawler.log'),
        base_path('supervisor/logs/elasticsearch.log'),
        base_path('supervisor/logs/email.log'),
        base_path('supervisor/logs/trigger.log'),
        base_path('supervisor/logs/horizon.log'),
        base_path('supervisor/logs/user_control.log'),
        storage_path('logs/laravel.log')
    ],

    'storages' => [
        '/'
    ],

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
        App\Providers\HorizonServiceProvider::class,

        /*
         * Local Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\SessionServiceProvider::class,

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
        'YouTube' => Alaouy\Youtube\Facades\Youtube::class,
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
        'Sentiment' => PHPSentiment\Sentiment::class,
        'PDF' => Barryvdh\DomPDF\Facade::class,
    ],

];
