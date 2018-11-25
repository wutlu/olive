<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;
use App\Utilities\Term;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use Carbon\Carbon;

use App\Models\Crawlers\TwitterCrawler;

use App\Models\Option;
use App\Models\Organisation\Organisation;

use App\Models\Twitter\Token;

use App\Jobs\Crawlers\Social\Twitter\DeletedTweetJob;

use System;
use Storage;
use Mail;

use App\Mail\ServerAlertMail;

use App\Console\Commands\Nohup;

class StreamProcess extends Command
{
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
    protected $description = 'Twitter gerçek zamanlı tweet toplayıcı.';

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

        $token = Token::where('id', $token_id);

        if ($token->exists())
        {
            $token = $token->first();

            if (!$token->value)
            {
                $die = true; $this->error('Value not found.');
            }

            if (!$token->type)
            {
                $die = true; $this->error('Type not found.');
            }

            if (@$die)
            {
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
                    'handler' => $stack,
                    'auth' => 'oauth',
                    'stream' => true
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
                    10
                );
                $this->error($message);

                $token->error_count = $token->error_count+1;

                if ($token->error_count >= $token->off_limit)
                {
                    Mail::queue(new ServerAlertMail('Twitter [Token Durdu]', $message));

                    $token->off_reason = $message;
                    $token->pid = null;
                    $token->status = 'disabled';
                }

                $token->save();

                die();
            }

            $token->error_count = 0;
            $token->pid = getmypid();
            $token->save();

            $stream = $response->getBody();

            $this->info($token->value);

            $crawler = new TwitterCrawler;

            $bulk = [];

            while (!$stream->eof())
            {
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
                        if (@$obj['retweeted_status'])
                        {
                            $tweet = $crawler->pattern($obj['retweeted_status']);

                            $bulk = $crawler->chunk($tweet, $bulk);
                        }

                        if (@$obj['quoted_status'])
                        {
                            $tweet = $crawler->pattern($obj['quoted_status']);

                            $bulk = $crawler->chunk($tweet, $bulk);
                        }
                 
                        $tweet = $crawler->pattern($obj);

                        $bulk = $crawler->chunk($tweet, $bulk);

                        //$this->info($obj['text']);
                    }
                    else
                    {
                        System::log(
                            json_encode([ $obj, $form_params ]),
                            'App\Jobs\Crawlers\Twitter\StreamProcess::handle(Rate Limit, '.$token->id.')',
                            5
                        );
                    }
                }
            }
        }
        else
        {
            $this->error('Token not found.');
        }
    }

    # 
    # read line
    # 
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
