<?php

return [
    'currency' => '$',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/veri.zone-logo.svg',
        'name' => 'TEST',
        'address' => [
            'TEST'
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
     * referans payı
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
            'name' => 'veri.zone',
            'iban' => 'TR 0000 0000 0000 0000'
        ]
    ]
];

/*
 *   M. Toksöz Ticaret
 *   Yeni Mahalle, Eti Cad. No: 76/B
 *   Polatlı Vergi Dairesi
 *   11047286234
 */
