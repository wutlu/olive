<?php

use Illuminate\Database\Seeder;

use App\Models\Crawlers\SozlukCrawler;

class SozlukCrawlersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'name' => 'EkşiSözlük',
                'site' => 'https://eksisozluk.com',
                'url_pattern' => 'entry/__id__',
                'selector_title' => 'h1#title',
                'selector_entry' => '.content',
                'selector_author' => '.entry-author',
                'max_attempt' => 10,
                'deep_try' => 100,
                'chunk' => 25
            ],
            [
                'name' => 'UludağSözlük',
                'site' => 'https://www.uludagsozluk.com',
                'url_pattern' => 'e/__id__',
                'selector_title' => 'h1.tekentry-baslik',
                'selector_entry' => 'li.li_capsul_entry > .entry > .entry-p',
                'selector_author' => 'a.yazar',
                'max_attempt' => 100,
                'deep_try' => 100,
                'chunk' => 25
            ],
            [
                'name' => 'İnciSözlük',
                'site' => 'http://www.incisozluk.com.tr',
                'url_pattern' => 'e/__id__',
                'selector_title' => 'h1.title',
                'selector_entry' => '.entry-list > .entry:first-child .entry-text-wrap',
                'selector_author' => '.entry-list > .entry:first-child .username',
                'max_attempt' => 100,
                'deep_try' => 100,
                'chunk' => 25
            ]

        ];

        foreach ($items as $item)
        {
            $query = SozlukCrawler::updateOrCreate(
                [
                    'name' => $item['name']
                ],
                [
                    'site' => $item['site'],
                    'url_pattern' => $item['url_pattern'],
                    'selector_title' => $item['selector_title'],
                    'selector_entry' => $item['selector_entry'],
                    'selector_author' => $item['selector_author'],
                    'max_attempt' => $item['max_attempt'],
                    'deep_try' => $item['deep_try'],
                    'chunk' => $item['chunk']
                ]
            );
        }
    }
}
