<?php

use Illuminate\Database\Seeder;

use App\Models\Crawlers\ShoppingCrawler;

class ShoppingCrawlersTableSeeder extends Seeder
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
                'name' => 'SAHİBİNDEN',
                'site' => 'https://www.sahibinden.com',
                'google_search_query' => 'site:sahibinden.com/ilan',
                'url_pattern' => 'ilan\/([a-z0-9-]{4,128})-(\d{9,10})\/detay',
                'selector_title' => 'h1',
                'selector_description' => '#classifiedDescription',
                'selector_address' => 'h2 > a',
                'selector_breadcrumb' => '.classifiedBreadCrumb .trackId_breadcrumb',
                'selector_seller_name' => '.username-info-area',
                'selector_seller_phones' => '.pretty-phone-part',
                'selector_price' => '.classifiedInfo > h3',
                'google_max_page' => 1,
                'control_interval' => 5
            ],
            [
                'name' => 'MİLLİYETEMLAK',
                'site' => 'https://www.milliyetemlak.com',
                'google_search_query' => 'site:milliyetemlak.com',
                'url_pattern' => 'ilan\/(\d{8,9})',
                'selector_title' => 'h1',
                'selector_description' => '.editorInside',
                'selector_address' => '.location > a',
                'selector_breadcrumb' => '.breadcrumb li',
                'selector_seller_name' => '.estateInformations > p',
                'selector_seller_phones' => '.pretty-phone-part',
                'selector_price' => '.gallery-price',
                'google_max_page' => 2,
                'control_interval' => 15
            ],
        ];

        foreach ($items as $item)
        {
            $query = ShoppingCrawler::updateOrCreate(
                [
                    'name' => $item['name']
                ],
                [
                    'site' => $item['site'],
                    'google_search_query' => $item['google_search_query'],
                    'url_pattern' => $item['url_pattern'],
                    'selector_title' => $item['selector_title'],
                    'selector_description' => $item['selector_description'],
                    'selector_address' => $item['selector_address'],
                    'selector_breadcrumb' => $item['selector_breadcrumb'],
                    'selector_seller_name' => $item['selector_seller_name'],
                    'selector_seller_phones' => $item['selector_seller_phones'],
                    'google_max_page' => $item['google_max_page'],
                    'control_interval' => $item['control_interval'],
                    'selector_price' => $item['selector_price']
                ]
            );
        }
    }
}
