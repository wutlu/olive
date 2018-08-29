<?php

return [
    'currency' => '₺',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/veri.zone-logo.svg',
        'name' => 'veri.zone A.Ş.',
        'address' => [
            'Tomtom Mah. Nur-i Ziya Sok. 16/1',
            '34433 Çankaya Ankara'
        ],
        'contact' => [
            'www.veri.zone',
            'destek@veri.zone',
            '0 212 292 04 94'
        ],
        'taxOffice' => [
            'name' => 'Ankara',
            'no' => '0000'
        ],
        'tradeRegisterNo' => '0000'
    ],

    /*
     * vergi oranı
     */

    'tax' => env('TAX', 18),

    /*
     * yıllık alımlarda indirim oranı
     */

    'discount_with_year' => env('DISCOUNT_WITH_YEAR', 10),

    'banks' => [
        [
            'name' => 'veri.zone LTD.',
            'iban' => 'TR 1345 1561 2451 5112 51'
        ]
    ]
];
