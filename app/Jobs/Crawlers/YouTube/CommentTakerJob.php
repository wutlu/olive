<?php

namespace App\Jobs\Crawlers\YouTube;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use YouTube;
use Term;
use Sentiment;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

use App\Models\YouTube\FollowingVideos;

use App\Utilities\DateUtility;

use App\Olive\Gender;

class CommentTakerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (count($this->ids))
        {
            $term = new Term;

            $sentiment = new Sentiment;
            $sentiment->engine('sentiment');

            $gender = new Gender;
            $gender->loadNames();

            foreach ($this->ids as $id)
            {
                $chunk = [];

                try
                {
                    $commentThreads = Youtube::getCommentThreadsByVideoId($id, 100);

                    if (count($commentThreads))
                    {
                        foreach ($commentThreads as $comment)
                        {
                            $item = self::comment(
                                $comment,
                                [
                                    'term' => $term,
                                    'sentiment' => $sentiment,
                                    'gender' => $gender
                                ]
                            );

                            if (DateUtility::checkDate($item->data['created_at']))
                            {
                                //echo $item->data['text'].PHP_EOL;

                                $chunk['body'][] = $this->index($item->data['id'], date('Y.m', strtotime($item->data['created_at'])));
                                $chunk['body'][] = $item->data;
                            }

                            if ($item->replies)
                            {
                                foreach ($item->replies as $reply)
                                {
                                    $item = self::comment(
                                        $reply,
                                        [
                                            'term' => $term,
                                            'sentiment' => $sentiment,
                                            'gender' => $gender
                                        ],
                                        $item->data['id']
                                    );

                                    if (DateUtility::checkDate($item->data['created_at']))
                                    {
                                        $chunk['body'][] = $this->index($item->data['id'], date('Y.m', strtotime($item->data['created_at'])));
                                        $chunk['body'][] = $item->data;
                                    }

                                    //echo $item->data['text'].PHP_EOL;
                                }
                            }
                        }
                    }
                }
                catch (\Exception $e)
                {
                    FollowingVideos::where('video_id', $id)->update([ 'reason' => 'Video yorumlara kapalı!' ]);
                }

                if (count($chunk))
                {
                    BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
                }
            }
        }
    }

    /**
     * comment
     *
     * @return object
     */
    public static function comment($data, $function, string $comment_id = '')
    {
        $arr = [];

        if ($comment_id)
        {
            $snippet = $data->snippet;

            $arr['comment_id'] = $comment_id;
        }
        else
        {
            $snippet = $data->snippet->topLevelComment->snippet;
        }

        $arr = array_merge($arr, [
            'id' => $data->id,
            'text' => $function['term']->convertAscii($snippet->textOriginal),
            'video_id' => $snippet->videoId,
            'channel' => [
                'id' => $snippet->authorChannelId->value,
                'title' => $snippet->authorDisplayName,
                'gender' => $function['gender']->detector([ $snippet->authorDisplayName ])
            ],
            'created_at' => date('Y-m-d H:i:s', strtotime($snippet->publishedAt)),
            'called_at' => date('Y-m-d H:i:s'),
            'sentiment' => $function['sentiment']->score($snippet->textOriginal)
        ]);

        return (object) [
            'data' => $arr,
            'replies' => @$data->replies->comments ? $data->replies->comments : null
        ];
    }

    /**
     * index
     *
     * @return array;
     */
    public static function index(string $id, string $date)
    {   
        return [
            'create' => [
                '_index' => Indices::name([ 'youtube', implode('-', [ 'comments', $date ]) ]),
                '_type' => 'comment',
                '_id' => $id
            ]
        ];
    }
}
