<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Term;

class TestController extends Controller
{
    public static function test()
    {
		$arr = [
'soyola',
'volvo',
'bp',
'akyağ',
'bmw',
'benetton',
'yuvam',
'chrysler',
'beymen',
'reebok',
'hes',
'bosch',
'özdilek',
'simfer',
'bossa',
'loris',
'azzaro',
'taç',
'burgerking',
'burger',
'sony',
'süsler',
'bürosit',
'marshall',
'vestel',
'camel',
'omega',
'ceyo',
'calvin',
'klein',
'altinyildiz',
'pioneer',
'gucci',
'eros',
'zorluteks',
'hacişakir',
'sana',
'giorgio',
'beverly',
'hills',
'rubinstein',
'hyundai',
'hewlett',
'packard',
'rover',
'giorgio',
'honda',
'lego',
'ibm',
'saray',
'bingo',
'jaguar',
'canon',
'jumbo',
'juvena',
'zippo',
'pinar',
'kodak',
'vectra',
'dmc',
'komili',
'kafkas',
'raks',
'ihlas',
'çokomilk',
'lancome',
'abbate',
'çokomel',
'lancaster',
'hüsrev',
'çokokrem',
'lassa',
'grundig',
'dido',
'cooper',
'peugeot',
'supermen',
'çokonat',
'vuitton',
'pelit',
'şipsevdi',
'lumberjack',
'driver',
'freshbol',
'maltepe',
'istikbal',
'first',
'man',
'boss',
'test',
'marlboro',
'lades',
'falim',
'marks',
'spencer',
'zenith',
'idaş',
'factor',
'pentax',
'arçelik',
'mcdonald s',
'motor',
'adidas',
'meysu',
'nutella',
'alarko',
'migros',
'nescafe',
'aeg',
'caterpillar',
'ülker',
'alfa',
'romeo',
'chanel',
'panda',
'alo',
'chevrolet',
'armani',
'dior',
'avon',
'cocacola',
'coca',
'bayer',
'levi s',
'çbs',
'ysl',
'cat',
'daewoo',
'ipana',
'varan',
'davidoff',
'johnnie',
'walker',
'audi',
'dyo',
'chivas',
'regal',
'erikli',
'fanta',
'artema',
'goodyear',
'toblerone',
'vitra',
'mudo',
'cinzano',
'ncr',
'uludağ',
'remy',
'nestle',
'jacobs',
'ysatis',
'nivea',
'mercedes',
'ülfet',
'opel',
'mintax',
'escort',
'panasonic',
'mobil',
'klassis',
'parliament',
'singer',
'sanko',
'pepsi',
'pepsicola',
'epson',
'lebsan',
'pereja',
'solo',
'permatik',
'ipek',
'selpak',
'persil',
'omo',
'kent',
'pfizer',
'mazda',
'mohr',
'philips',
'toshiba',
'ciba',
'pirelli',
'swatch',
'tadelle',
'polisan',
'rolex',
'asgold',
'renault',
'pierre',
'cardin',
'atasay',
'sağra',
'toyota',
'altinbaş',
'salem',
'paşabahçe',
'yimpaş',
'samsun',
'roadstar',
'yörsan',
'sharp',
'gillette',
'aytaç',
'shell',
'microsoft',
'creation',
'slazenger',
'visa',
'3m',
'tekel',
'ford',
'7up',
'timberland',
'tursil',
'puma',
'hayat',
'vakko',
'torun',
'ikbal',
'wendy s',
'soley',
'luna',
'westinghouse',
'seğmen',
'ona',
'ykm',
'genesis',
'ray-ban',
'yataş',
'bisse',
'sabah',
'eti',
'ustam',
'ariel',
'giorgio',
'aire',
'ace',
'beko',
'yuva',
'compaq',
'kinder',
'ferrari',
'toto',
'karper',
'seray',
'tank',
'kinder',
'acibadem',
'hilfiger',
'surprise',
'kurtsan',
'tommy',
'gezer',
'avea',
'goldaş',
'iskender',
'bifa',
'güllüoğlu',
'ay-yildiz',
'atlas',
'colgate',
'glasurit',
'dizayn',
'doğadan',
'unesco',
'mopak',
'dalan',
'valentino',
'petlas',
'kaşmir',
'eca',
'hillside',
'next nextstar',
'iceberg',
'marriott',
'tadim',
'redbull',
'pologarage',
'bacardi',
'ören',
'lcw',
'altinbaşak',
'lcwaikiki',
'next',
'sinangil',
'nextstar',
'teknosa',
'aselsan',
'yachting',
'hidromek',
'yapikredi',
'polaris',
'dedeman',
'canbebe',
'kiğili',
'aymar',
'nokia',
'ramsey',
'polo',
'bayindir',
'sunny',
'tatlises',
'campari',
'mado',
'maggi',
'vivident',
'wrangler',
'mke',
'lays',
'ipekyol',
'firatpen',
'indesit',
'dimes',
'tamek',
'bağdat',
'iskender',
'otaci',
'sütaş',
'intel',
'rodi',
'roman',
'orkide',
'tema',
'sarar',
'şekil',
'dove',
'aygaz',
'nike',
'hamidiye',
'mavi',
'ford',
'desa',
'sarelle',
'ford',
'hotiç',
'eczacibaşi',
'izocam',
'amd',
'bellona',
'thy',
'collezione',
'tween',
'şekil',
'dardanel',
'tofaş',
'turyap',
'casa',
'viagra',
'bjk',
'azzaro',
'zara',
'ykk',
'kamilkoç',
'koç',
'formula',
'derby',
'abercrombie',
'florence',
'nightingale',
'cnn',
'kinetix',
'milka',
'akbank',
'pritt',
'bmc',
'merinos',
'google',
'triko',
'kom',
'alfemo',
'viko',
'scotch',
'calgon',
'banca',
'centrala',
'europeana',
'tübitak',
'atiker',
'bim',
'piyale',
'genpa',
'serel',
'nobel',
'dalin',
'twigy',
'bio-der',
'şölen',
'filli',
'seranit',
'kiler',
'kurukahveci',
'binboa',
'zaman',
'ttnet',
'metro',
'molfix',
'gameboy',
'balparmak',
'molped',
'roberto',
'cavalli',
'sirma',
'brillant',
'zade',
'olin',
'botox',
'temsa',
'toki',
'dufy',
'converse',
'keskinoğlu',
'caldion',
'converse',
'vileda',
'jagler',
'sabanci',
'tokai',
'koton',
'arkas',
'arzum',
'uno',
'çaykur',
'superfresh',
'şekil',
'medline',
'colaturka',
'post-it',
'biofarma',
'cafecrown',
'apple',
'turkcell',
'igdaş',
'eti',
'çilek',
'ezinç',
'seiko',
'yudum',
'soyak',
'alpella',
'yahoo!',
'derimod',
'ricci',
'tiffany',
'evkur',
'vakifbank',
'tiffany co',
'kumtel',
'calgonit',
'yurtiçi',
'biskrem',
'penguen',
'winston',
'ipod',
'baycan',
'hanimeller',
'kappa',
'toefl',
'egepen',
'deceuninck',
'igs',
'emsan',
'ugg',
'eriş',
'şekil',
'mondial',
'novartis',
'penti',
'lazzoni',
'absolut',
'eker',
'bioxcin',
'bilfen',
'garanti',
'arko',
'çalik',
'efes',
'pilsen',
'makro',
'hsbc',
'residences',
'muya',
'ziraat',
'pegasus',
'güral',
'duru',
'jacquard',
'bridgestone',
'westin',
'facebook',
'kristal',
'lexus',
'kenzo',
'chic',
'papia',
		];

		foreach ($arr as $a)
		{
			$a = Term::convertAscii($a, [ 'lowercase' => true ]);
			//$a = str_replace([], [], $a)

			if (strlen($a) == 3)
			{
				echo $a.PHP_EOL;
			}
		}
	}
}
