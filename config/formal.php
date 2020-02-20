<?php

return [
    'currency' => '₺',
    'currency_text' => 'TL',
    'tax_name' => 'K.D.V.',
    'company' => [
        'logo' => 'img/8vz.net_logo.svg',
        'name' => 'Veri Zone Teknoloji',
        'address' => [
            'Mustafa Kemal Mh. Dumlupınar Blv.',
            'ODTÜ Teknokent Bilişim İnovasyon Merkezi',
            '280/G No:1260 Alt Zemin Kat Çankaya, Ankara'
        ],
        'contact' => [
            'www.8vz.net',
            'bilgi@8vz.net',
            '+90 850 302 1630'
        ],
        'tradeRegisterNo' => '10942289746',
        'taxOffice' => [
            'name' => 'Maltepe Vergi Dairesi',
            'no' => '8490296700'
        ]
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
        'VakıfBank' => [
            'name' => 'Alper Mutlu TOKSÖZ',
            'iban' => 'TR43 0001 5001 5800 7307 1577 72'
        ]
    ],

    'installment' => [
        'status' => true,
        'max' => 12
    ]
];
