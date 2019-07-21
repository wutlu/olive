<?php

namespace App\Console\Commands\Crawlers\Instagram;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use Carbon\Carbon;

use App\Models\Crawlers\Instagram\Selves;

class SelfCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:self:counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instagram bağlantılarının ne kadar veri topladığını sayar.';

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
        $crawlers = Selves::get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                $count = Document::count(
                    [
                        'instagram',
                        'medias',
                        '*'
                    ],
                    'media',
                    [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'self_id' => $crawler->id ] ],
                                ]
                            ]
                        ]
                    ]
                );

                $this->line($crawler->url);
                $this->info('data: ['.$count->data['count'].']');

                $crawler->update([ 'hit' => $count->data['count'] ]);
            }
        }
    }
}
