<?php

namespace App\Console\Commands\Crawlers\Instagram;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use Carbon\Carbon;

use App\Models\Crawlers\Instagram\Selves;

class SelfMinuter extends Command
{
    protected $time;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:self:minuter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instagram bağlantılarına gidilecek zaman aralığını belirler.';

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
        $crawlers = Selves::where('status', true)->get();

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

                $division = $count->data['count'] ? $count->data['count'] : 1;
                $division = $division/1440;
                $division = intval(1/$division);
                $division = $division > 1440 ? 1440 : ($division == 0 ? 1 : $division);

                $this->info('minute: ['.$division.']');

                $crawler->update([ 'control_interval' => $division ]);
            }
        }
    }
}
