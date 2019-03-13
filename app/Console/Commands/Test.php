<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Crawlers\SozlukCrawler;
use App\Utilities\Crawler as CrawlerUtility;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test komutu.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sozluk = SozlukCrawler::where('id', 3)->first();

        $item = CrawlerUtility::entryDetection(
            $sozluk->site,
            $sozluk->url_pattern,
            204064979,
            $sozluk->selector_title,
            $sozluk->selector_entry,
            $sozluk->selector_author,
            $sozluk->proxy
        );

        print_r($item);
    }
}
