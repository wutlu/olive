<?php

return [
    /**
     * Sistem Versiyonu
     */
    'version' => '1.3.1031-alpha',

    /**
     * Destek Konuları
     */
    'ticket' => [
        'types' => [
            'source' => 'Kaynak İsteği',
            'technic' => 'Teknik Konular',
            'other' => 'Diğer Konular',
            'odeme-bildirimi' => 'Ödeme Bildirimi',
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
        'important' => 'Önemli olay bildirimleri.',
        'login' => 'Giriş bildirimleri.',
        'forum' => 'Forum takip ve uyarı bildirimleri.',
        'badge' => 'Yeni kazanılan etiket bildirimleri.',
        'newsletter' => 'E-posta bülteni.',
    ],

    /**
     * Options Tablosu Varsayılanları
     */
    'options' => [
        'email_alerts.server' => 'date_format:Y-m-d H:i:s',
        'email_alerts.log' => 'date_format:Y-m-d H:i:s',

        'youtube.status' => 'string|in:on,off',
        'youtube.index.auto' => 'string|in:on,off',
        'youtube.index.status' => 'string|in:on,off',

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
        'youtube_video' => 'YouTube Video',
        'youtube_comment' => 'YouTube Yorum',
        'twitter' => 'Twitter',
        'sozluk' => 'Sözlük',
        'news' => 'Haber',
        'shopping' => 'Alışveriş',
    ],

    /**
     * Slider Parametreleri
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
     * Kullanıcı Parametreleri
     */
    'user' => [
        'badges' => [
            1 => [
                'name' => 'Yeni Üye',
                'image_src' => 'img/icons/badges/email_verified.png',
                'description' => 'E-posta adresinizi doğrulayın.'
            ],
            2 => [
                'name' => 'İlk Konu',
                'image_src' => 'img/icons/badges/first_thread.png',
                'description' => 'İlk forum konunuzu açın.'
            ],
            3 => [
            	'name' => '10 Cevap',
                'image_src' => 'img/icons/badges/10_answers.png',
                'description' => '10 soruya cevap verin.'
            ],
            4 => [
                'name' => 'İyi Cevap',
                'image_src' => 'img/icons/badges/best_answer.png',
                'description' => 'Verdiğiniz 1 cevap en iyisi seçilsin.'
            ],
            5 => [
                'name' => 'Profesör',
                'image_src' => 'img/icons/badges/professor.png',
                'description' => '100 soruya cevap verin.'
            ],
            6 => [
                'name' => 'Nostalji 1',
                'image_src' => 'img/icons/badges/nostalgie_1.png',
                'description' => '1 yılınızı doldurun.'
            ],
            7 => [
                'name' => 'Nostalji 2',
                'image_src' => 'img/icons/badges/nostalgie_2.png',
                'description' => '2 yılınızı doldurun.'
            ],
            8 => [
                'name' => 'Nostalji 3',
                'image_src' => 'img/icons/badges/nostalgie_3.png',
                'description' => '3 yılınızı doldurun.'
            ],
            9 => [
                'name' => 'Nostalji 4',
                'image_src' => 'img/icons/badges/nostalgie_4.png',
                'description' => '4 yılınızı doldurun.'
            ],
            10 => [
                'name' => 'Nostalji 5',
                'image_src' => 'img/icons/badges/nostalgie_5.png',
                'description' => '5 yılınızı doldurun.'
            ],

            /* ... */

            997 => [
                'name' => 'Moderatör',
                'image_src' => 'img/icons/badges/moderator.png',
                'description' => 'Olive ekibiyle çalışın.'
            ],
            998 => [
                'name' => 'Yönetici',
                'image_src' => 'img/icons/badges/root.png',
                'description' => 'veri.zone ofisinde çalışın.'
            ],
            999 => [
                'name' => 'Destekçi',
                'image_src' => 'img/icons/badges/supporter.png',
                'description' => 'En az 1 ödeme yapın.'
            ],
        ]
    ],

    /**
     * Arama Ayarları
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
                'name' => 'Veri Havuzu',
                'route' => 'data_pool.dashboard',
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
            27 => [
                'name' => 'Forum',
                'route' => 'forum.index',
                'icon' => 'library_books'
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
            28 => [
                'name' => 'E-posta Bülteni',
                'route' => 'admin.newsletter',
                'root' => true,
                'icon' => 'email'
            ],
            29 => [
                'name' => 'Ziyaretçi Logları',
                'route' => 'admin.session.logs',
                'root' => true,
                'icon' => 'accessibility'
            ],
        ]
    ],
];