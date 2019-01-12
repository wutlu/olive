<?php

namespace App\Console\Commands\Crawlers;

use Illuminate\Console\Command;

use App\Models\Option;

use Carbon\Carbon;

class AutoIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:auto_index {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexleri içerikler alınmadan önce oluşturur.';

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
    	$type = $this->option('type');

        $types = [
            'twitter.tweets' => 'Twitter -> Tweet',
            'youtube.comments' => 'YouTube -> Comment',
        ];

        if (!$type)
        {
            $type = $this->choice('Çalışılacak indexi belirtin.', $types, $type);
        }

    	switch ($type)
    	{
    		case 'twitter.tweets':
    			$key = 'twitter.index.tweets';
    			$model = 'App\Jobs\Elasticsearch\CreateTwitterIndexJob';
    			$index = 'tweets';
    		break;
    		case 'youtube.comments':
    			$key = 'youtube.index.comments';
    			$model = 'App\Jobs\Elasticsearch\CreateYouTubeIndexJob';
    			$index = 'comments';
    		break;
    	}

        $last_month = Option::where('key', $key)->first();

        if (@$last_month)
        {
            $last_month = Carbon::createFromFormat('Y.m', $last_month->value)->format('Y.m');

            while ($last_month <= date('Y.m', strtotime('+1 month')))
            {
                $index_name = implode('-', [ $index, $last_month ]);

                $model::dispatch($index_name, $last_month)->onQueue('elasticsearch');

                echo $this->info($index_name);

                $last_month = Carbon::createFromFormat('Y.m', $last_month)->addMonth()->format('Y.m');
            }
        }
        else
        {
            $this->error('Ayar değeri bulunamadı.');
        }
    }
}
