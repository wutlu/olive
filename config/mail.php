<?php

return [

    'driver' => env('MAIL_DRIVER', 'smtp'),

    'host' => env('MAIL_HOST', 'smtp.mailgun.org'),

    'port' => env('MAIL_PORT', 587),

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    'encryption' => env('MAIL_ENCRYPTION', 'tls'),

    'username' => env('MAIL_USERNAME'),

    'password' => env('MAIL_PASSWORD'),

    'sendmail' => '/usr/sbin/sendmail -bs',

    /**
     * Admin E-posta Adresi
     *
     * - Yönetimsel sorunlar bu e-posta adresine gönderilir.
     */
    'admin_email' => env('ADMIN_EMAIL', 'admin@veri.zone'),

    /**
     * Root E-posta Adresi
     *
     * - Sistemsel sorunlar bu e-posta adresine gönderilir.
     */
    'root_email' => env('ROOT_EMAIL', 'alper@veri.zone'),

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
