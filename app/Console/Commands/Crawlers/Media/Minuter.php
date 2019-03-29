<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use Carbon\Carbon;

use App\Models\Crawlers\MediaCrawler;

class Minuter extends Command
{
    protected $time;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:minuter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Medya sitelerine gidilecek zaman aralığını belirler.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->time = Carbon::now()->subHours(12)->format('Y-m-d H');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $crawlers = MediaCrawler::where('status', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                $count = Document::count(
                    [
                        'media',
                        $crawler->elasticsearch_index_name
                    ],
                    'article',
                    [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'status' => 'ok' ] ],
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ],
                                'filter' => [
                                    [
                                        'range' => [
                                            'created_at' => [
                                                'format' => 'YYYY-MM-dd HH',
                                                'gte' => $this->time
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );

                $this->line($crawler->name);
                $this->info('data: ['.$count->data['count'].']');

                $division = $count->data['count'] ? $count->data['count'] : 1;
                $division = $division/310;
                $division = intval(1/$division);
                $division = $division > 120 ? 120 : ($division == 0 ? 1 : $division);

                $this->info('minute: ['.$division.']');

                $crawler->update([ 'control_interval' => $division ]);
            }
        }
    }
}
