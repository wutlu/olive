<?php

namespace App\Console\Commands\Crawlers\YouTube;

use Illuminate\Console\Command;

use YouTube;
use System;
use Sentiment;
use Term;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

class TrendDetect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:trend_detect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'YouTube trend videoları tespit eder.';

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
        $items = Youtube::getPopularVideos('tr', 50);

        $chunk = [];

        $sentiment = new Sentiment;
        $term = new Term;

        foreach ($items as $item)
        {
                $arr = [
                    'id' => $item->id,
                    'title' => $item->snippet->title,
                    'created_at' => date('Y-m-d H:i:s', strtotime($item->snippet->publishedAt)),
                    'called_at' => date('Y-m-d H:i:s'),
                    'counts' => [
                        'view' => intval(@$item->statistics->viewCount),
                        'like' => intval(@$item->statistics->likeCount),
                        'dislike' => intval(@$item->statistics->dislikeCount),
                        'favorite' => intval(@$item->statistics->favoriteCount),
                        'comment' => intval(@$item->statistics->commentCount)
                    ],
                    'channel' => [
                        'id' => $item->snippet->channelId,
                        'title' => $item->snippet->channelTitle
                    ]
                ];

                if (@$item->snippet->tags)
                {
                    $arr['tags'] = array_map(function($m) {
                        return [ 'tag' => $m ];
                    }, $item->snippet->tags);
                }

                if (@$item->snippet->description)
                {
                    $arr['description'] = $term->convertAscii($item->snippet->description);
                    $arr['sentiment'] = $sentiment->score($arr['description']);
                }

                $this->info($arr['title']);

                $chunk['body'][] = [
                    'index' => [
                        '_index' => Indices::name([ 'youtube', 'videos' ]),
                        '_type' => 'video',
                        '_id' => $arr['id']
                    ]
                ];

                $chunk['body'][] = $arr;
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }
}
