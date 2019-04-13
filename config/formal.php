<?php

return [
    'currency' => '₺',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/veri.zone_logo.svg',
        'name' => 'Veri Zone Bilişim Tek. ve Dan. Ltd. Şti.',
        'address' => [
            'İnönü Mahallesi, 1769. Sk. 1D, 06370',
            'Ostim Osb',
            'Yenimahalle/ANKARA'
        ],
        'contact' => [
            'www.veri.zone',
            'bilgi@veri.zone',
            '+90 850 302 1630'
        ],
        'taxOffice' => [
            'name' => 'Ostim Vergi Dairesi',
            'no' => '9240813158'
        ],
        //'tradeRegisterNo' => '0957521689'
    ],

    /**
     * vergi oranı
     *
     * @var integer
     */

    'tax' => env('TAX', 18),

    /**
     * yıllık alımlarda indirim oranı
     *
     * @var integer
     */
    'discount_with_year' => env('DISCOUNT_WITH_YEAR', 10),

    'banks' => [
        [
            'name' => 'Veri Zone Bilişim Teknolojileri ve Danışmanlık Ltd. Şti.',
            'iban' => 'TR 04 0006 2000 8490 0006 2979 12'
        ]
    ]
];
