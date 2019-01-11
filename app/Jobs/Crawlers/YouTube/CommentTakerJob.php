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
        $chunk = [];

        if (count($this->ids))
        {
            foreach ($this->ids as $id)
            {
                try
                {
                    $commentThreads = Youtube::getCommentThreadsByVideoId($id, 100);

                    if (count($commentThreads))
                    {
                        foreach ($commentThreads as $comment)
                        {
                            $item = self::comment($comment);

                            echo $item->data['text'].PHP_EOL;

                            $chunk['body'][] = $this->index($item->data['id']);;
                            $chunk['body'][] = $item->data;

                            if ($item->replies)
                            {
                                foreach ($item->replies as $reply)
                                {
                                    $item = self::comment($reply, $item->data['id']);

                                    $chunk['body'][] = $this->index($item->data['id']);;
                                    $chunk['body'][] = $item->data;

                                    echo $item->data['text'].PHP_EOL;
                                }
                            }
                        }
                    }
                }
                catch (\Exception $e)
                {
                    FollowingVideos::where('video_id', $id)->update([ 'reason' => 'Video yorumlara kapalı!' ]);
                }
            }
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }

    /**
     * comment
     *
     * @return object
     */
    public static function comment($data, string $comment_id = '')
    {
        $term = new Term;
        $sentiment = new Sentiment;

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
            'text' => $term->convertAscii($snippet->textOriginal),
            'video_id' => $snippet->videoId,
            'channel' => [
                'id' => $snippet->authorChannelId->value,
                'title' => $snippet->authorDisplayName
            ],
            'created_at' => date('Y-m-d H:i:s', strtotime($snippet->publishedAt)),
            'called_at' => date('Y-m-d H:i:s'),
            'sentiment' => $sentiment->score($snippet->textOriginal)
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
    public static function index(string $id)
    {   
        return [
            'index' => [
                '_index' => Indices::name([ 'youtube', 'comments' ]),
                '_type' => 'comment',
                '_id' => $id
            ]
        ];
    }
}
