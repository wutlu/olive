<?php

namespace App\Console\Commands\Crawlers\YouTube;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Youtube;

use App\Utilities\Term;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

use System;
use Sentiment;

use Mail;
use App\Mail\ServerAlertMail;

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
    protected $description = 'YouTube trend videoları ve videolara atılan yorumları alır.';

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
        $videoList = Youtube::getPopularVideos('tr', 50);

        if (count($videoList))
        {
            $this->info('list['.count($videoList).']');

            $videoChunk = [];
            $commentChunk = [];

            $totalComment = 0;
            $totalVideo = 0;

            $sentiment = new Sentiment;

            foreach ($videoList as $video)
            {
                try
                {
                    $totalVideo++;

                    $this->comment('['.$video->snippet->title.']');

                    $videoChunk['body'][] = [
                        'index' => [
                            '_index' => Indices::name([ 'youtube', 'videos' ]),
                            '_type' => 'video',
                            '_id' => $video->id
                        ]
                    ];

                    $arr = [
                        'id' => $video->id,
                        'title' => $video->snippet->title,
                        'created_at' => date('Y-m-d H:i:s', strtotime($video->snippet->publishedAt)),
                        'called_at' => date('Y-m-d H:i:s'),
                        'counts' => [
                            'view' => intval(@$video->statistics->viewCount),
                            'like' => intval(@$video->statistics->likeCount),
                            'dislike' => intval(@$video->statistics->dislikeCount),
                            'favorite' => intval(@$video->statistics->favoriteCount),
                            'comment' => intval(@$video->statistics->commentCount)
                        ],
                        'channel' => [
                            'id' => $video->snippet->channelId,
                            'title' => $video->snippet->channelTitle
                        ]
                    ];

                    if (@$video->snippet->tags)
                    {
                        $arr['tags'][] = [
                            'tag' => $video->snippet->tags
                        ];
                    }

                    if (@$video->snippet->description)
                    {
                        //$arr['description'] = Term::convertAscii($video->snippet->description);

                        $arr['description'] = $video->snippet->description;
                        $arr['sentiment'] = $sentiment->score($video->snippet->description);
                    }

                    $videoChunk['body'][] = $arr;

                    if (@$video->statistics->commentCount)
                    {
                        $commentThreads = Youtube::getCommentThreadsByVideoId($video->id, 100);
                        $commentCount = count($commentThreads);

                        if ($commentCount)
                        {
                            $this->info('comments['.$commentCount.']');

                            foreach ($commentThreads as $comment)
                            {
                                $totalComment++;

                                $replyCount = 0;

                                $commentChunk['body'][] = [
                                    'create' => [
                                        '_index' => Indices::name([ 'youtube', 'comments' ]),
                                        '_type' => 'comment',
                                        '_id' => $comment->id
                                    ]
                                ];

                                $commentChunk['body'][] = [
                                    'id' => $comment->id,
                                    'text' => $comment->snippet->topLevelComment->snippet->textOriginal,
                                    'video_id' => $comment->snippet->videoId,
                                    'channel' => [
                                        'id' => $comment->snippet->topLevelComment->snippet->authorChannelId->value,
                                        'title' => $comment->snippet->topLevelComment->snippet->authorDisplayName
                                    ],
                                    'created_at' => date('Y-m-d H:i:s', strtotime($comment->snippet->topLevelComment->snippet->publishedAt)),
                                    'called_at' => date('Y-m-d H:i:s'),
                                    'sentiment' => $sentiment->score($comment->snippet->topLevelComment->snippet->textOriginal)
                                ];

                                if (@$comment->replies->comments)
                                {
                                    foreach ($comment->replies->comments as $reply)
                                    {
                                        $replyCount++;
                                        $totalComment++;

                                        $commentChunk['body'][] = [
                                            'create' => [
                                                '_index' => Indices::name([ 'youtube', 'comments' ]),
                                                '_type' => 'comment',
                                                '_id' => $reply->id
                                            ]
                                        ];

                                        $commentChunk['body'][] = [
                                            'id' => $reply->id,
                                            'text' => $reply->snippet->textOriginal,
                                            'video_id' => $comment->snippet->videoId,
                                            'comment_id' => $comment->id,
                                            'channel' => [
                                                'id' => $reply->snippet->authorChannelId->value,
                                                'title' => $reply->snippet->authorDisplayName
                                            ],
                                            'created_at' => date('Y-m-d H:i:s', strtotime($reply->snippet->publishedAt)),
                                            'called_at' => date('Y-m-d H:i:s'),
                                            'sentiment' => $sentiment->score($reply->snippet->textOriginal)
                                        ];
                                    }
                                }
                            }

                            if (@$replyCount)
                            {
                                $this->info('replies['.$replyCount.']');
                            }
                        }

                        if (count($commentChunk))
                        {
                            BulkInsertJob::dispatch($commentChunk)->onQueue('elasticsearch');
                        }

                        $commentChunk = [];
                    }
                }
                catch (\Exception $e)
                {
                    $this->error($e->getMessage());

                    System::log($e->getMessage(), 'App\Console\Commands\Crawlers\YouTube\TrendDetect::handle()', 2);
                }
            }

            if (count($videoChunk))
            {
                BulkInsertJob::dispatch($videoChunk)->onQueue('elasticsearch');
            }

            if ($totalComment <= 100)
            {
                Mail::queue(new ServerAlertMail('YouTube Yorum [Düşük Verim]', 'YouTube yorum toplama verimliliğinde yoğun bir düşüş yaşandı. Lütfen logları inceleyin.'));
            }

            if ($totalVideo <= 20)
            {
                Mail::queue(new ServerAlertMail('YouTube İçerik [Düşük Verim]', 'YouTube içerik toplama verimliliğinde yoğun bir düşüş yaşandı. Lütfen logları inceleyin.'));
            }
        }
    }
}
