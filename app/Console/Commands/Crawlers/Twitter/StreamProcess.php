<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Models\Twitter\Token;

use App\Jobs\Crawlers\Social\Twitter\DeletedTweetJob;

use System;
use Sentiment;

use Mail;
use App\Mail\ServerAlertMail;
use App\Models\Crawlers\TwitterCrawler;

use App\Models\Twitter\StreamingUsers;

use App\Utilities\DateUtility;

use App\Olive\Gender;

class StreamProcess extends Command
{
    /**
     * Twitter Api Adresi
     *
     * @var string
     */
    private $endpoint = "https://stream.twitter.com/1.1/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:stream:process {--tokenId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter, gerçek zamanlı akışa başla.';

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
        $token_id = $this->option('tokenId');

        $token_id = $token_id ? $token_id : $this->ask('Enter a token id');

        $token = Token::where('id', $token_id)->first();

        $gender = new Gender;
        $gender->loadNames();

        if (@$token)
        {
            if (!$token->value)
            {
                $this->error('Value not found.');

                die();
            }

            if (!$token->type)
            {
                $this->error('Type not found.');

                die();
            }

            $this->table(
                [
                    'Token Id',
                    'Type'
                ],
                [
                    [
                        $token_id,
                        $token->type
                    ]
                ]
            );

            $stack = HandlerStack::create();

            switch ($token->type)
            {
                case 'track':
                    $form_params = [
                        'language' => 'tr',
                        'track' => $token->value
                    ];
                break;
                case 'follow':
                    $form_params = [
                        'follow' => $token->value
                    ];
                break;
                case 'locations':
                    $form_params = [
                        'locations' => $token->value
                    ];
                break;
            }

            $oauth = new Oauth1([
                'consumer_key' => $token->consumer_key,
                'consumer_secret' => $token->consumer_secret,
                'token' => $token->access_token,
                'token_secret' => $token->access_token_secret
            ]);

            $stack->push($oauth);

            $this->client = new Client(
                [
                    'base_uri' => $this->endpoint,
                    'handler'  => $stack,
                    'auth'     => 'oauth',
                    'stream'   => true
                ]
            );

            try
            {
                $response = $this->client->post('statuses/filter.json', [
                    'form_params' => $form_params
                ]);
            }
            catch (\Exception $e)
            {
                $message = json_encode([ $form_params, $e->getMessage() ]);

                System::log(
                    $message,
                    'App\Jobs\Crawlers\Twitter\StreamProcess::handle('.$token->type.', '.$token_id.')',
                    9
                );
                $this->error($message);

                $token->error_count = $token->error_count+1;

                if ($token->error_count >= $token->off_limit)
                {
                    Mail::queue(new ServerAlertMail('Twitter [Token Durdu]', $message));

                    $token->off_reason = $message;
                    $token->pid        = null;
                    $token->status     = 'disabled';
                }

                $token->save();

                die();
            }

            $token->error_count = 0;
            $token->pid = getmypid();
            $token->save();

            $stream = $response->getBody();

            $this->info($token->value);

            $crawler     = new TwitterCrawler;
            $dateUtility = new DateUtility;
            $sentiment   = new Sentiment;

            $bulk = [];
            $tracked_users = [];

            $i=0;

            while (!$stream->eof())
            {
                $i++;
                if ($i==100)
                    break;
                $obj = json_decode($this->read_line($stream), true);

                if (@$obj['delete'])
                {
                    DeletedTweetJob::dispatch($obj['delete'])->onQueue('crawler')->delay(now()->addMinutes(5));

                    $this->error('deleted: ['.$obj['delete']['status']['id_str'].']');
                }
                else
                {
                    if (@$obj['id_str'])
                    {
                        $store = null;

                        //file_put_contents('tweets.txt', json_encode($obj, JSON_PRETTY_PRINT).PHP_EOL, FILE_APPEND | LOCK_EX);

                        if (@$obj['retweeted_status'])
                        {
                            if ($dateUtility->checkDate($obj['retweeted_status']['created_at']))
                            {
                                $obj['retweeted_status']['user']['gender'] = $gender->detector([ $obj['retweeted_status']['user']['screen_name'], $obj['retweeted_status']['user']['name'] ]);
                                $tweet = $crawler->pattern($obj['retweeted_status']);
                                $tweet['sentiment'] = $sentiment->score($tweet['text']);
                                $tweet = (object) $tweet;

                                $store = true;

                                $bulk = $crawler->chunk($tweet, $bulk);

                                //$this->info('tweetledi [rt] ['.$tweet->user->screen_name.']');
                            }
                            else
                            {
                                //$this->error('eski tarih ['.$obj['created_at'].']');
                            }
                        }

                        if (@$obj['quoted_status'])
                        {
                            if ($dateUtility->checkDate($obj['quoted_status']['created_at']))
                            {
                                $obj['quoted_status']['user']['gender'] = $gender->detector([ $obj['quoted_status']['user']['screen_name'], $obj['quoted_status']['user']['name'] ]);
                                $tweet = $crawler->pattern($obj['quoted_status']);
                                $tweet['sentiment'] = $sentiment->score($tweet['text']);
                                $tweet = (object) $tweet;

                                $store = true;

                                $bulk = $crawler->chunk($tweet, $bulk);

                                //$this->info('tweetledi [qt] ['.$tweet->user->screen_name.']');
                            }
                            else
                            {
                                //$this->error('eski tarih ['.$obj['created_at'].']');
                            }
                        }

                        if ($store === null || $store === true)
                        {
                            $obj['user']['gender'] = $gender->detector([ $obj['user']['screen_name'], $obj['user']['name'] ]);
                            $tweet = $crawler->pattern($obj);
                            $tweet['sentiment'] = $sentiment->score($tweet['text']);
                            $tweet = (object) $tweet;

                            if (@$tweet->user->verified && $tweet->user->lang == 'tr')
                            {
                                $tracked_users[] = [
                                    'id' => $tweet->user->id,
                                    'screen_name' => $tweet->user->screen_name
                                ];
                            }

                            $bulk = $crawler->chunk($tweet, $bulk);

                            //$this->info('tweetledi [tw] ['.$tweet->user->screen_name.']');

                            $g = $gender->detector([ $tweet->user->screen_name, $tweet->user->name ]);
                        }
                        else
                        {
                            //$this->error('eski tarih ['.$obj['created_at'].']');
                        }

                        //$this->info($obj['text']);
                    }
                    else
                    {
                        if ($obj != null)
                        {
                            System::log(
                                json_encode([ $obj, $form_params ]),
                                'App\Jobs\Crawlers\Twitter\StreamProcess::handle(Rate Limit, '.$token->id.')',
                                5
                            );
                        }
                    }

                    if (count($tracked_users))
                    {
                        foreach ($tracked_users as $tu)
                        {
                            try
                            {
                                StreamingUsers::updateOrCreate(
                                    [
                                        'user_id' => $tu['id'],
                                        'organisation_id' => config('app.organisation_id_root')
                                    ],
                                    [
                                        'screen_name' => $tu['screen_name'],
                                        'verified' => true
                                    ]
                                );

                                echo $tu['screen_name'].PHP_EOL;
                            }
                            catch (\Exception $e)
                            {
                                System::log(
                                    $e->getMessage(),
                                    'App\Jobs\Crawlers\Twitter\StreamProcess::handle(Verified UPSERT, '.$tu['id'].')',
                                    10
                                );
                            }
                        }

                        unset($tracked_users);

                        $tracked_users = [];
                    }
                }
            }
        }
        else
        {
            $this->error('Token not found.');
        }
    }

    /**
     * Akış Satır Okuyucu
     *
     * @return mixed
     */
    private function read_line($stream, string $buffer = '', int $size = 0)
    {
        while (!$stream->eof())
        {
            if (false === ($byte = $stream->read(1)))
            {
                return $buffer;
            }

            $buffer .= $byte;

            if (++$size == null || substr($buffer, -strlen(PHP_EOL)) === PHP_EOL)
            {
                break;
            }
        }

        return $buffer;
    }
}
