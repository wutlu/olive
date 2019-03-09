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

use App\Models\YouTube\FollowingChannels;
use App\Models\YouTube\FollowingVideos;
use App\Models\YouTube\FollowingKeywords;

use App\Utilities\DateUtility;

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
    protected $description = 'YouTube video topla.';

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
        print_r(YouTube::getPopularVideos('tr', 1));
        exit();
        $type = $this->option('type');

        $types = [
            'trends' => 'Trend Videolar',
            'followed_channels' => 'Takip Edilen Kanal Videoları',
            'followed_videos' => 'Takip Edilen Videolar',
            'followed_keywords' => 'Takip Edilen Kelimeler',
        ];

        if (!$type)
        {
            $type = $this->choice('Hangi eylemi uygulamak istiyorsunuz?', $types, $type);
        }

        try
        {
            switch ($type)
            {
                case 'trends':
                    $item_chunk = array_chunk(YouTube::getPopularVideos('tr', 50), 10);
                break;
                case 'followed_videos':
                    $ids = FollowingVideos::select('video_id')->whereNull('reason')->get();

                    $item_chunk = array_map(function($chunk) {
                        return Youtube::getVideoInfo(array_map(function($chunk) {
                            return $chunk['video_id'];
                        }, $chunk));
                    }, $ids->chunk(10)->toArray());
                break;
                case 'followed_keywords':
                    $keywords = FollowingKeywords::select('keyword')->whereNull('reason')->get()->toArray();

                    $item_chunk = array_map(function ($item) {
                        return Youtube::searchAdvanced([
                            'q' => $item['keyword'],
                            'type' => 'video',
                            'part' => 'id, snippet',
                            'maxResults' => 50
                        ]);
                    }, $keywords);

                    $item_chunk = array_flatten($item_chunk);
                    $item_chunk = array_chunk($item_chunk, 10);
                break;
                case 'followed_channels':
                    $channels = FollowingChannels::select('channel_id')->whereNull('reason')->get()->toArray();

                    $item_chunk = array_map(function ($item) {
                        return Youtube::listChannelVideos($item['channel_id'], 50);
                    }, $channels);

                    $item_chunk = array_flatten($item_chunk);
                    $item_chunk = array_chunk($item_chunk, 10);
                break;
            }
        }
        catch (\Exception $e)
        {
            $this->error($e->getMessage());

            System::log(
                json_encode($e->getMessage()),
                'App\Console\Commands\Crawler\YouTube\VideoDetect::handle('.$type.')',
                2
            );

            die();
        }

        if (count($item_chunk))
        {
            foreach ($item_chunk as $items)
            {
                $ids = [];
                $chunk = [];

                foreach ($items as $item)
                {
                    $video = self::video($item);

                    if ($video->status == 'ok')
                    {
                        $ids[] = $video->data['id'];

                        $chunk['body'][] = $this->index($video->data['id']);;
                        $chunk['body'][] = $video->data;

                        $this->info($video->data['title']);

                        ### [ ilişkili videolar ] ###

                        try
                        {
                            $relatedVideos = Youtube::getRelatedVideos($video->data['id'], 1);

                            if ($relatedVideos)
                            {
                                foreach (array_chunk($relatedVideos, 10) as $relatedChunk)
                                {
                                    $relatedIds = [];

                                    foreach ($relatedChunk as $relatedItem)
                                    {
                                        $relatedVideo = self::video($relatedItem);

                                        if ($relatedVideo->status == 'ok' && DateUtility::checkDate($relatedVideo->data['created_at']))
                                        {
                                            $relatedIds[] = $relatedVideo->data['id'];

                                            $chunk['body'][] = $this->index($relatedVideo->data['id']);
                                            $chunk['body'][] = $relatedVideo->data;

                                            $this->info('[related]'.$relatedVideo->data['title']);
                                        }
                                    }

                                    if (count($relatedIds))
                                    {
                                        $this->info('CommentTakerJob ['.count($relatedIds).']');

                                        CommentTakerJob::dispatch($relatedIds)->onQueue('power-crawler');
                                    }
                                }
                            }
                        }
                        catch (\Exception $e)
                        {
                            $this->error($e->getMessage());

                            System::log(
                                json_encode($e->getMessage()),
                                'App\Console\Commands\Crawler\YouTube\VideoDetect::handle('.$type.')',
                                5
                            );
                        }

                        ### ### ###
                    }
                }

                if (count($ids))
                {
                    $this->info('CommentTakerJob ['.count($ids).']');

                    CommentTakerJob::dispatch($ids)->onQueue('power-crawler');
                }

                if (count($chunk))
                {
                    BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
                }
            }
        }
        else
        {
            $this->error('Taranacak içerik bulunamadı.');
        }
    }

    /**
     * Video Objesi
     *
     * @return object;
     */
    public static function video($item)
    {   
        $term = new Term;
        $sentiment = new Sentiment;

        $arr = [
            'id' => @$item->id->videoId ? $item->id->videoId : $item->id,
            'title' => $item->snippet->title,
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
     * Video İçin Index
     *
     * @return array;
     */
    public static function index(string $id)
    {   
        return [
            'create' => [
                '_index' => Indices::name([ 'youtube', 'videos' ]),
                '_type' => 'video',
                '_id' => $id
            ]
        ];
    }
}
