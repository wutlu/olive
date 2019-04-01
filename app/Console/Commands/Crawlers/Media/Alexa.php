<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use Carbon\Carbon;

use App\Models\Crawlers\MediaCrawler;

class Alexa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:alexa_ranker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Medya siteleri için alexa durumunu kontrol eder.';

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
        $crawlers = MediaCrawler::get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                $this->line($crawler->site);

                $xml = simplexml_load_file('http://data.alexa.com/data?cli=10&dat=snbamz&url='.$crawler->site);

                $stats = isset($xml->SD[1]->COUNTRY) ? $xml->SD[1]->COUNTRY->attributes() : null;

                if ($stats)
                {
                    $rank = ((array) $stats->RANK)[0];

                    $this->info(number_format($rank));

                    $crawler->status = ($rank < 20000) ? true : false;
                    $crawler->alexa_rank = $rank;
                    $crawler->off_reason = $crawler->off_reason ? $crawler->off_reason : 'Alexa sıralaması çok düşük olduğundan görev sonlandırıldı.';
                    $crawler->update();
                }
                else
                {
                    $this->error('Alexa verisi alınamadı.');
                }
            }
        }
    }
}
