<?php

namespace App\Console\Commands\Crawlers\YouTube;

use Illuminate\Console\Command;

use YouTube;
use System;
use Sentiment;
use Term;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;
use App\Jobs\Crawlers\YouTube\CommentTakerJob;

use App\Models\YouTube\FollowingVideos;

class VideoDetect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:video_detect {--type=} {--count=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'YouTube video toplayıcı.';

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
            'trend' => 'Trend Videolar',
            'followed_channels' => 'Takip Edilen Kanal Videoları',
            'followed_videos' => 'Takip Edilen Videolar'
        ];

        if (!$type)
        {
            $type = $this->choice('Hangi grubu takip etmek istersiniz?', $types, $type);
        }

        switch ($type)
        {
            case 'trend':
                $count = $this->option('count') ? $this->option('count') : 10;

                if ($count > 50)
                {
                    $this->error('En fazla 50 video sorgulayabilirsiniz.');

                    die;
                }

                $item_chunk = [
                    YouTube::getPopularVideos('tr', $count)
                ];

                print_r($item_chunk);
                exit();
            break;
            case 'followed_videos':
                $ids = FollowingVideos::select('video_id')->whereNull('reason')->get();

                $item_chunk = array_map(function($obj) {
                    return $obj->video_id;
                }, $ids);

                $ids->chunk(50)->toArray()

                print_r($item_chunk);
                exit;
            break;
        }

        foreach ($item_chunk as $items)
        {
            $chunk = [];

            foreach ($items as $item)
            {
                $ids = [];

                $video = self::video($item);

                if ($video->status == 'ok')
                {
                    $ids[] = $video->data['id'];

                    $chunk['body'][] = $this->index($video->data['id']);;
                    $chunk['body'][] = $video->data;

                    $this->info($video->data['title']);

                    /*** related videos ***/

                    $relatedVideos = Youtube::getRelatedVideos($video->data['id'], $count);

                    foreach ($relatedVideos as $item)
                    {
                        $video = self::video($item);

                        if ($video->status == 'ok')
                        {
                            $ids[] = $video->data['id'];

                            $chunk['body'][] = $this->index($video->data['id']);
                            $chunk['body'][] = $video->data;

                            $this->info('[related]'.$video->data['title']);
                        }
                    }

                    /*** ************** ***/
                }

                if (count($ids))
                {
                    CommentTakerJob::dispatch($ids)->onQueue('power-crawler');
                }
            }

            if (count($chunk))
            {
                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }
        }
    }

    /**
     * video
     *
     * @return object;
     */
    public static function video($item)
    {   
        $term = new Term;
        $sentiment = new Sentiment;

        $arr = [
            'id' => @$item->id->videoId ? $item->id->videoId : $item->id,
            'title' => $term->convertAscii($item->snippet->title),
            'created_at' => date('Y-m-d H:i:s', strtotime($item->snippet->publishedAt)),
            'called_at' => date('Y-m-d H:i:s'),
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

        if ($term->languageDetector([ $arr['title'], @$arr['description'] ], 'tr'))
        {
            return (object) [
                'status' => 'ok',
                'data' => $arr
            ];
        }
        else
        {
            return (object) [
                'status' => 'err',
                'data' => $arr
            ];
        }
    }

    /**
     * index
     *
     * @return array;
     */
    public static function index(string $id)
    {   
        return [
            'index' => [
                '_index' => Indices::name([ 'youtube', 'videos' ]),
                '_type' => 'video',
                '_id' => $id
            ]
        ];
    }
}
