<?php

namespace App\Console\Commands\Crawlers\Social\YouTube;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Youtube;
use App\Utilities\Term;
use App\Elasticsearch\Indices;

use System;

class TrendDetect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:trends';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'YouTube trend listesini belirler.';

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

            $chunk = [ 'body' => [] ];

    		foreach ($videoList as $video)
    		{
    			try
    			{
	    			$commentThreads = Youtube::getCommentThreadsByVideoId($video->id, 100);
	                $comment_count = count($commentThreads);

    				$this->comment('['.$video->snippet->title.']');

	                $chunk['body'][] = [
	                    'create' => [
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
	                    'view' => $video->statistics->viewCount,
	                    'like' => $video->statistics->likeCount,
	                    'dislike' => $video->statistics->dislikeCount,
	                    'favorite' => $video->statistics->favoriteCount,
	                    'channel' => [
	                    	'id' => $video->snippet->channelId,
	                    	'title' => $video->snippet->channelTitle
	                    ],
	                ];

	                if (@$video->snippet->tags)
	                {
	                    $arr['tags'] = json_encode($video->snippet->tags);
	                }

	                if (@$video->snippet->description)
	                {
	                	$arr['description'] = Term::convertAscii($video->snippet->description);
	                }

	                $chunk['body'][] = $arr;

	    			if ($comment_count)
	    			{
	    				$this->info('comments['.$comment_count.']');

	    				foreach ($commentThreads as $comment)
	    				{
	    					$reply_count = 0;

			                $chunk['body'][] = [
			                    'create' => [
			                        '_index' => Indices::name([ 'youtube', 'comments' ]),
			                        '_type' => 'comment',
			                        '_id' => $comment->id
			                    ]
			                ];

			                $chunk['body'][] = [
			                    'id' => $comment->id,
			                    'text' => $comment->snippet->topLevelComment->snippet->textOriginal,
			                    'video_id' => $comment->snippet->videoId,
			                    'channel' => [
			                    	'id' => $comment->snippet->topLevelComment->snippet->authorChannelId->value,
			                    	'title' => $comment->snippet->topLevelComment->snippet->authorDisplayName
			                    ],
			                    'created_at' => date('Y-m-d H:i:s', strtotime($comment->snippet->topLevelComment->snippet->publishedAt)),
			                    'called_at' => date('Y-m-d H:i:s')
			                ];

	    					if (@$comment->replies->comments)
	    					{
	    						foreach ($comment->replies->comments as $reply)
	    						{
	    							$reply_count++;

					                $chunk['body'][] = [
					                    'create' => [
					                        '_index' => Indices::name([ 'youtube', 'comments' ]),
					                        '_type' => 'comment',
					                        '_id' => $reply->id
					                    ]
					                ];

					                $chunk['body'][] = [
					                    'id' => $reply->id,
					                    'text' => $reply->snippet->textOriginal,
					                    'video_id' => $comment->snippet->videoId,
					                    'comment_id' => $comment->id,
					                    'channel' => [
					                    	'id' => $reply->snippet->authorChannelId->value,
					                    	'title' => $reply->snippet->authorDisplayName
					                    ],
					                    'created_at' => date('Y-m-d H:i:s', strtotime($reply->snippet->publishedAt)),
					                    'called_at' => date('Y-m-d H:i:s')
					                ];
	    						}
	    					}
	    				}

	    				if (@$reply_count)
	    				{
	    					$this->info('replies['.$reply_count.']');
	    				}
	    			}
    			}
    			catch (\Exception $e)
    			{
    				$this->error($e->getMessage());

    				System::log($e->getMessage(), 'App\Console\Commands\Crawlers\Social\YouTube\TrendDetect::handle()', 2);
    			}
    		}

    		//BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
    	}
    }
}
