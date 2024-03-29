<?php

return [
    /**
     * Sistem Versiyonu
     */
    'version' => '8.2.5',

    /**
     * db
     */
    'db' => [
        'alias' => env('DB_ALIAS', 'oliveone')
    ],

    /**
     * Gizlilik Politikası ve Kullanım Koşulları Versiyonu
     *
     * - Gizlilik Politikası veya Kullanım Koşullarında yapılacak
     * olan değişiklikler durumunda, kullanıcılara tekrar okuyup
     * kabul etmelerini gerektirecek popup açılır.
     *
     * @var integer
     */
    'term_version' => 4,

    /**
     * Asistan Bilgisi
     *
     * @var array
     */
    'assistant' => [
        'name' => 'mutlu',
        'avatar' => '/img/team-mutlu.jpg'
    ],

    /**
     * Analiz Katmanları
     */
    'analysis' => [
        'sentiment' => [
            'types' => [
                'sentiment-pos' => [
                    'per' => 25,
                    'title' => 'Pozitif'
                ],
                'sentiment-neg' => [
                    'per' => 25,
                    'title' => 'Negatif'
                ],
                'sentiment-neu' => [
                    'per' => 25,
                    'title' => 'Nötr'
                ],
                'sentiment-hte' => [
                    'per' => 25,
                    'title' => 'Nefret Söylemi'
                ]
            ],
            'ignore' => 'sentiment-ign',
            'title' => 'Duygu Analizi'
        ],
        'illegal' => [
            'types' => [
                'illegal-bet' => [
                    'per' => 30,
                    'title' => 'Bahis'
                ],
                'illegal-nud' => [
                    'per' => 30,
                    'title' => 'Çıplaklık'
                ],
                'illegal-nor' => [
                    'per' => 40,
                    'title' => 'Normal'
                ]
            ],
            'ignore' => 'illegal-ign',
            'title' => 'İllegal Analiz'
        ],
        'consumer' => [
            'types' => [
                'consumer-que' => [
                    'per' => 25,
                    'title' => 'Soru'
                ],
                'consumer-req' => [
                    'per' => 25,
                    'title' => 'İstek'
                ],
                'consumer-cmp' => [
                    'per' => 25,
                    'title' => 'Şikayet'
                ],
                'consumer-nws' => [
                    'per' => 25,
                    'title' => 'Haber'
                ],
            ],
            'ignore' => 'consumer-ign',
            'title' => 'Müşteri Analizi'
        ],
        'gender' => [
            'types' => [
                'gender-male' => [
                    'per' => 50,
                    'title' => 'Erkek'
                ],
                'gender-female' => [
                    'per' => 50,
                    'title' => 'Kadın'
                ],
            ],
            'title' => 'Cinsiyet Tespiti'
        ],
        'category' => [
            'types' => [
                'category-1' =>  [ 'title' => 'Teknoloji',             'per' => 0.1 ],
                'category-2' =>  [ 'title' => 'Kamu ve Siyaset',       'per' => 0.1 ],
                'category-3' =>  [ 'title' => 'Spor',                  'per' => 0.1 ],
                'category-4' =>  [ 'title' => 'Kültür & Sanat',        'per' => 0.1 ],
                'category-5' =>  [ 'title' => 'Popüler Medya',         'per' => 0.1 ],
                'category-6' =>  [ 'title' => 'Eğitim',                'per' => 0.1 ],
                'category-7' =>  [ 'title' => 'Finans',                'per' => 0.1 ],
                'category-8' =>  [ 'title' => 'İş',                    'per' => 0.1 ],
                'category-9' =>  [ 'title' => 'Sosyal Sorumluluk',     'per' => 0.1 ],
                'category-10' => [ 'title' => 'Güncel',                'per' => 0.1 ],
                'category-11' => [ 'title' => 'Eğlence & Yaşam',       'per' => 0.1 ],
                'category-12' => [ 'title' => 'İnanç',                 'per' => 0.1 ],
                'category-13' => [ 'title' => 'Anne & Bebek',          'per' => 0.1 ],
                'category-14' => [ 'title' => 'Kadın & Moda',          'per' => 0.1 ],
                'category-15' => [ 'title' => 'Erkek',                 'per' => 0.1 ],
                'category-16' => [ 'title' => 'Yemek',                 'per' => 0.1 ],
                'category-17' => [ 'title' => 'Seyahat',               'per' => 0.1 ],
                'category-18' => [ 'title' => 'Sağlık',                'per' => 0.1 ],
                'category-19' => [ 'title' => 'Ürün & Hizmet',         'per' => 0.1 ],
                'category-20' => [ 'title' => 'Markalar',              'per' => 0.1 ],
            ],
            'title' => 'Kategori Analizi'
        ],
    ],

    /**
     * Destek Konuları
     */
    'ticket' => [
        'types' => [
            'geri-bildirim' => 'Geri Bildirim',
            'kaynak-istegi' => 'Kaynak İsteği',
            'teknik' => 'Teknik Konular',
            'diger' => 'Diğer Konular',
            'odeme-bildirimi' => 'Ödeme Bildirimi',
            'hak-sahipligi' => 'Hak Sahipliği',
            'organisayon-teklifi' => 'Demo İsteği',
        ]
    ],

    /**
     * Intro Anahtarları
     */
    'intro' => [
        'keys' => [
            'welcome.create.organisation',
            'search.module',
            'driver.trend',
            'driver.search',
            'driver.stream',
            'driver.report.sortable'
        ]
    ],

    /**
     * E-posta Bildirimi Türleri
     */
    'notifications' => [
        'important' => 'Sadece Önemli Bildirimler',
        'login' => 'Giriş Bildirimleri',
        'forum' => 'Forum Takip ve Uyarı Bildirimleri',
        'badge' => 'Yeni Kazanılan Etiket Bildirimleri'
    ],

    /**
     * Trend Değerleri
     */
    'trends' => [
        'trend.status.google' => 'Google',
        'trend.status.youtube_video' => 'YouTube',
        'trend.status.sozluk' => 'Sözlük',
        'trend.status.news' => 'Haber',
        'trend.status.blog' => 'Blog & Forum',
        'trend.status.instagram_hashtag' => 'Instagram Hashtag',
        'trend.status.facebook' => 'Facebook',
        'trend.status.twitter_tweet' => 'Twitter Tweet',
        'trend.status.twitter_favorite' => 'Twitter Favori',
        'trend.status.twitter_hashtag' => 'Twitter Hashtag',
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

        'instagram.status' => 'string|in:on,off',
        'instagram.index.auto' => 'string|in:on,off',
        'instagram.index.status' => 'string|in:on,off',

        'trend.index' => 'string|in:on,off',
        'trend.status.google' => 'string|in:on,off',
        'trend.status.news' => 'string|in:on,off',
        'trend.status.twitter_tweet' => 'string|in:on,off',
        'trend.status.twitter_favorite' => 'string|in:on,off',
        'trend.status.twitter_hashtag' => 'string|in:on,off',
        'trend.status.sozluk' => 'string|in:on,off',
        'trend.status.blog' => 'string|in:on,off',
        'trend.status.youtube_video' => 'string|in:on,off',
        'trend.status.instagram_hashtag' => 'string|in:on,off',
        'trend.status.facebook' => 'string|in:on,off',

        'twitter.index.auto' => 'string|in:on,off',
        'twitter.status' => 'string|in:on,off',

        'data.learn' => 'string|in:on,off',
    ],

    /**
     * Uygulama Modülleri
     */
    'modules' => [
        'twitter' => 'Twitter',
        'sozluk' => 'Sözlük',
        'news' => 'Haber',
        'blog' => 'Blog & Forum',
        'instagram' => 'Instagram',
        'youtube_video' => 'YouTube Video',
        'youtube_comment' => 'YouTube Yorum',
        'shopping' => 'E-ticaret'
    ],

    /**
     * Aktif Modüller
     */
    'static_modules' => [
        'module_real_time' => 'Canlı Akış',
        'module_crm' => 'CRM',
        'module_search' => 'Arama',
        'module_trend' => 'Trend',
        'module_alarm' => 'Alarm',
        'module_compare' => 'Veri Kıyaslama',
        'module_borsa' => 'Kalabalığın Düşüncesi',
        'module_report' => 'Raporlama'
    ],

    /**
     * Slider Parametreleri
     */
    'carousel' => [
        'patterns' => [
            'sphere-1' => 'Sphere 1',
            'sphere-2' => 'Sphere 2',
            'sphere-3' => 'Sphere 3',
            'sphere-4' => 'Sphere 4',
            'sphere-5' => 'Sphere 5',
            'sphere-6' => 'Sphere 6',
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
            11 => [
                'name' => 'Partner',
                'image_src' => 'img/icons/badges/partner.png',
                'description' => 'Partner sistemine dahil olun.'
            ],
            12 => [
                'name' => 'Organizatör',
                'image_src' => 'img/icons/badges/organisation.png',
                'description' => 'Bir organizasyon oluşturun.'
            ],
            13 => [
                'name' => 'Analist',
                'image_src' => 'img/icons/badges/report.png',
                'description' => 'En az 1 rapor oluşturun.'
            ],
            14 => [
                'name' => 'Veri Gurusu',
                'image_src' => 'img/icons/badges/guru.png',
                'description' => '100 rapor oluşturun.'
            ],

            /* ... */

            996 => [
                'name' => 'Yönetici',
                'image_src' => 'img/icons/badges/admin.png',
                'description' => '8vz.net yönetimine katılın.'
            ],
            997 => [
                'name' => 'Moderatör',
                'image_src' => 'img/icons/badges/moderator.png',
                'description' => '8vz ekibiyle çalışın.'
            ],
            998 => [
                'name' => 'Sistem Sorumlusu',
                'image_src' => 'img/icons/badges/root.png',
                'description' => '8vz.net ofisinde çalışın.'
            ],
            999 => [
                'name' => 'Kurumsal',
                'image_src' => 'img/icons/badges/supporter.png',
                'description' => 'En az 1 ödeme yapın.'
            ],
        ],

        /**
         * Üyelik
         *
         * @return boolean
         */
        'registration' => env('REGISTRATION', true)
    ],

    /**
     * Arama Ayarları
     */
    'search' => [
        'modules' => [
            1 => [
                'name' => 'Canlı Akış',
                'route' => 'realtime.stream',
                'icon' => 'timeline'
            ],
            2 => [
                'name' => 'Arşivler',
                'route' => 'pin.groups',
                'icon' => 'group_work'
            ],
            3 => [
                'name' => 'Veri Havuzu',
                'route' => 'data_pool.dashboard',
                'icon' => 'streetview'
            ],
            4 => [
                'name' => 'Twitter Kullanıcı Havuzu',
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
                'name' => 'Alarmlar',
                'route' => 'alarm.dashboard',
                'icon' => 'alarm'
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
            /*
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
            */
            /*
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
            */
            12 => [
                'name' => 'Vekil Sunucu Yönetimi',
                'route' => 'admin.proxies',
                'root' => true,
                'icon' => 'vpn_key'
            ],
            13 => [
                'name' => 'E-ticaret Botları',
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
                'name' => 'Trend Ayarları',
                'route' => 'admin.trend.settings',
                'root' => true,
                'icon' => 'widgets'
            ],
            16 => [
                'name' => 'Twitter Ayarları',
                'route' => 'admin.twitter.settings',
                'root' => true,
                'icon' => 'widgets'
            ],
            18 => [
                'name' => 'Token Yönetimi',
                'route' => 'admin.twitter.tokens.json',
                'root' => true,
                'icon' => 'accessibility'
            ],
            19 => [
                'name' => 'Twitter Kelime Havuzu',
                'route' => 'admin.twitter.stream.keywords',
                'root' => true,
                'icon' => 'streetview'
            ],
            20 => [
                'name' => 'Twitter Kullanıcı Havuzu',
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
            /*
            28 => [
                'name' => 'E-posta Bülteni',
                'route' => 'admin.newsletter',
                'root' => true,
                'icon' => 'email'
            ],
            */
            29 => [
                'name' => 'Ziyaretçi Logları',
                'route' => 'admin.session.logs',
                'root' => true,
                'icon' => 'accessibility'
            ],
            30 => [
                'name' => 'YouTube Kelime Havuzu',
                'route' => 'admin.youtube.followed_keywords',
                'root' => true,
                'icon' => 'streetview'
            ],
            31 => [
                'name' => 'YouTube Kanal Havuzu',
                'route' => 'admin.youtube.followed_channels',
                'root' => true,
                'icon' => 'directions_walk'
            ],
            32 => [
                'name' => 'YouTube Video Havuzu',
                'route' => 'admin.youtube.followed_videos',
                'root' => true,
                'icon' => 'ondemand_video'
            ],
            33 => [
                'name' => 'Arama Motoru',
                'route' => 'search.dashboard',
                'icon' => 'search'
            ],
            34 => [
                'name' => 'Blog & Forum Botları',
                'route' => 'crawlers.blog.list',
                'root' => true,
                'icon' => 'widgets'
            ],
            35 => [
                'name' => 'Raporlar',
                'route' => 'report.dashboard',
                'icon' => 'pie_chart'
            ],
            36 => [
                'name' => 'Popüler Kaynaklar',
                'route' => 'trend.popular',
                'icon' => 'people'
            ],
            37 => [
                'name' => 'Veri Kıyasla',
                'route' => 'compare.dashboard',
                'icon' => 'pie_chart'
            ],
            38 => [
                'name' => 'Kalabalığın Düşüncesi (Borsa)',
                'route' => 'borsa.main',
                'icon' => 'money'
            ],
            39 => [
                'name' => 'Gazete Ayarları',
                'route' => 'crawlers.newspaper.settings',
                'root' => true,
                'icon' => 'public'
            ],
            40 => [
                'name' => 'Hosts Dosyası',
                'route' => 'admin.hosts.file',
                'root' => true,
                'icon' => 'dialer_sip'
            ],
            41 => [
                'name' => 'Instagram Ayarları',
                'route' => 'admin.instagram.settings',
                'root' => true,
                'icon' => 'widgets'
            ],
        ]
    ],
];
