<?php

return [
	2 => [
		'name' => 'Yatırımcı',
		'description' => 'Olive, henüz geliştirme aşamasında olduğundan tüm planlar satışa sunulmamıştır. Sadece geliştirme süresince alabileceğiniz YATIRIM planını satın alarak, hazır ve hazırlanmakta olan tüm Olive özelliklerini tam erişim sağlayabilirsiniz. Bu plan ilerleyen zamanlarda kaldırılacaktır. Fakat siz her zaman bu planı bu fiyattan kullanmaya devam edebilecek ve yılda '.config('formal.currency').' 8.520 kâr edeceksiniz.',
		'price' => 270,
		'buy' => true,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 2
			],
			'keyword_groups' => [
				'text' => 'Kelime Grubu',
				'details' => '',
				'value' => 2
			],
			'keyword_lines' => [
				'text' => 'Kelime Satırı',
				'details' => '',
				'value' => 4
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			],
		],
		'class' => 'teal-text active'
	],
	3 => [
		'name' => 'Bireysel',
		'price' => 490,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 1
			],
			'keyword_groups' => [
				'text' => 'Kelime Grubu',
				'details' => '',
				'value' => 1
			],
			'keyword_lines' => [
				'text' => 'Kelime Satırı',
				'details' => '',
				'value' => 2
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			],
		],
		'class' => 'teal-text'
	],
	4 => [
		'name' => 'Organize',
		'price' => 1960,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 4
			],
			'keyword_groups' => [
				'text' => 'Kelime Grubu',
				'details' => '',
				'value' => 4
			],
			'keyword_lines' => [
				'text' => 'Kelime Satırı',
				'details' => '',
				'value' => 8
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			],
		],
		'class' => 'teal-text'
	],
	5 => [
		'name' => 'Kurumsal',
		'price' => 4120,
		'properties' => [
			'capacity' => [
				'text' => 'Plan Kapasitesi',
				'details' => 'Plan kapasitesi kadar kullanıcı aynı anda bu planı kullanabilir.',
				'value' => 8
			],
			'keyword_groups' => [
				'text' => 'Kelime Grubu',
				'details' => '',
				'value' => 8
			],
			'keyword_lines' => [
				'text' => 'Kelime Satırı',
				'details' => '',
				'value' => 16
			],
			'support' => [
				'text' => 'Destek',
				'details' => '7/24 Online Destek',
				'value' => true
			],
		],
		'class' => 'teal-text'
	]
];
