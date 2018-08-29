<?php

use Illuminate\Database\Seeder;

use App\Models\Crawlers\MediaCrawler;

class MediaCrawlersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       	$query = new MediaCrawler;
        $query->name = 'DHA';
        $query->site = 'https://www.dha.com.tr';
        $query->base = '/';
        $query->url_pattern = '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})\/haber-(\d{7,9})';
        $query->selector_title = 'h1';
        $query->selector_description = '.news-body > p.spot';
        $query->off_limit = 10;
        $query->control_interval = 10;
        $query->control_date = '2018-01-01 00:00:00';
        $query->save();
    }
}
