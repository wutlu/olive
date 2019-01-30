<?php

return [
    'currency' => '₺',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/veri.zone-logo.svg',
        'name' => 'veri.zone',
        'address' => [
            'Yeni Mah. Eti Cad. 78/B',
            'Polatlı/ANKARA',
            'Mustafa Toksöz'
        ],
        'contact' => [
            'www.veri.zone',
            'destek@veri.zone',
            '+90 850 302 1630'
        ],
        'taxOffice' => [
            'name' => 'Polatlı Vergi Dairesi',
            'no' => '8490002944'
        ],
        'tradeRegisterNo' => '11047286234'
    ],

    /**
     * vergi oranı
     *
     * @var integer
     */

    'tax' => env('TAX', 18),

    /**
     * partner payı
     *
     * @var integer
     */

    'reference_rate' => env('REFERENCE_RATE', 10),

    /**
     * yıllık alımlarda indirim oranı
     *
     * @var integer
     */
    'discount_with_year' => env('DISCOUNT_WITH_YEAR', 10),

    'banks' => [
        [
            'name' => 'Mustafa TOKSÖZ',
            'iban' => 'TR 04 0006 2000 8490 0006 2979 12'
        ]
    ]
];
