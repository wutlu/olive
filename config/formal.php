<?php

return [
    'currency' => '$',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/veri.zone-logo.svg',
        'name' => 'Mutlu Toksöz Ticaret, Veri ve Yazılım Hizmetleri',
        'address' => [
            'Tomtom Mah. Nur-i Ziya Sok. 16/1',
            '06900 Polatlı/ANKARA'
        ],
        'contact' => [
            'www.veri.zone',
            'destek@veri.zone',
            '+90 850 302 1630'
        ],
        'taxOffice' => [
            'name' => 'Ankara',
            'no' => '0000'
        ],
        'tradeRegisterNo' => '0000'
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
            'name' => 'veri.zone',
            'iban' => 'TR 0000 0000 0000 0000'
        ]
    ]
];
