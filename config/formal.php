<?php

return [
    'currency' => '₺',
    'currency_text' => 'TL',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/veri.zone_logo.svg',
        'name' => 'Veri Zone Bilişim Tek. ve Dan. Ltd. Şti.',
        'address' => [
            'İnönü Mahallesi, 1769. Sk. 1D/1, 06370',
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
     * stopaj oranı
     *
     * @var integer
     */
    'stoppage' => env('STOPPAGE', 20),

    'banks' => [
        'Enpara' => [
            'name' => 'VERİ ZONE BİL. TEK. VE DAN. LTD. ŞTİ.',
            'iban' => 'TR70 0011 1000 0000 0085 1234 41'
        ]
    ],

    'installment' => [
        'status' => true,
        'max' => 12
    ]
];
