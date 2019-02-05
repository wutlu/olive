<?php

return [
	1 => [
		'name' => 'Başlangıç',
		'price' => 0,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 1
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => false
			]
		]
	],
	2 => [
		'name' => 'Yatırımcı',
		'description' => 'Olive, henüz geliştirme aşamasında olduğundan tüm planlar satışa sunulmamıştır. Sadece geliştirme süresince alabileceğiniz YATIRIM planını satın alarak, hazır ve hazırlanmakta olan tüm Olive özelliklerini tam erişim sağlayabilirsiniz. Bu plan ilerleyen zamanlarda kaldırılacaktır. Fakat siz her zaman bu planı bu fiyattan kullanmaya devam edebilecek ve yılda '.config('formal.currency').' 8.520 kâr edeceksiniz.',
		'price' => 270,
		'price_old' => 1080,
		'buy' => true,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 2
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			]
		]
	],
	3 => [
		'name' => 'Bireysel',
		'price' => 490,
		'price_old' => 540,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 1
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			]
		]
	],
	4 => [
		'name' => 'Organize',
		'price' => 1960,
		'price_old' => 2160,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 4
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			]
		]
	],
	5 => [
		'name' => 'Kurumsal',
		'price' => 4120,
		'price_old' => 4320,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 8
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			]
		]
	]
];
