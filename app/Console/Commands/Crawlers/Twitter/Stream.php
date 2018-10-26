<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;
use App\Utilities\Term;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;
use App\Models\Crawlers\TwitterCrawler;

use Carbon\Carbon;

use App\Models\Option;

class Stream extends Command
{
    private $endpoint = "https://stream.twitter.com/1.1/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:stream {--id=} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter üzerinden gerçek zamanlı tweetleri toplar.';

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
        $option = Option::where('key', 'twitter.index.tweets')->first();

        if ($option)
        {
            $return = Carbon::createFromFormat('Y.m', $option->value)->format('Y.m') > Carbon::now()->format('Y.m');
        }
        else
        {
            $return = false;
        }

        if (!$return)
        {
            $this->error('Tweet index not yet created.');

            exit();
        }

        $type = $this->option('type');

        $types = [
            'keyword' => 'Keyword Stream',
            'user' => 'User Stream',
            'trend' => 'Trend Stream'
        ];

        if (!$type)
        {
            $type = $this->choice('What kind of a start streaming?', $types, $type);
        }

        if (array_key_exists($type, $types))
        {
            if ($type == 'user' || $type == 'keyword')
            {
                $id = $this->option('id');

                if (!$id)
                {
                    $id = $this->ask('User ID?');
                }

                $stream = $this->{ $type }($id);
            }
            else if ($type == 'trend')
            {
                $stream = $this->{ $type }();
            }

            $bulk = [];

            while (!$stream->eof())
            {
                try
                {
                    $obj = json_decode($this->readLine($stream), true);

                    if (@$obj['id_str'])
                    {
                        if (@$obj['retweeted_status'])
                        {
                            $tweet = TwitterCrawler::pattern($obj['retweeted_status']);

                            $bulk = TwitterCrawler::chunk($tweet, $bulk);
                        }

                        if (@$obj['quoted_status'])
                        {
                            $tweet = TwitterCrawler::pattern($obj['quoted_status']);

                            $bulk = TwitterCrawler::chunk($tweet, $bulk);
                        }
     
                        $tweet = TwitterCrawler::pattern($obj);

                        $bulk = TwitterCrawler::chunk($tweet, $bulk);
                    }
                    else
                    {
                        print_r($obj);
                    }
                }
                catch (\Exception $e)
                {
                    $this->error($e->getMessage());

                    print_r($e->getMessage());
                }
            }
        }
        else
        {
            $this->error('Entered value is not valid.');
        }
    }

    # 
    # stream header
    # 
    public function header(array $keys)
    {
        $stack = HandlerStack::create();

        $oauth = new Oauth1([
            'consumer_key' => $keys['client_id'],
            'consumer_secret' => $keys['client_secret'],
            'token' => $keys['access_token'],
            'token_secret' => $keys['access_token_secret']
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
    }

    /*******************************\
     * 
     * keyword streaming function
     * 
    \*******************************/
    public function keyword(int $id)
    {
        echo Term::line('Using keyword stream:');

        $this->header();

        $response = $this->client->post('statuses/filter.json', [
            'form_params' => [
                'language' => 'tr',
                'track' => 'ile'
            ]
        ]);

        return $response->getBody();
    }

    /*******************************\
     * 
     * user streaming function
     * 
    \*******************************/
    public function user(int $id)
    {
        echo Term::line('user stream');
    }

    /*******************************\
     * 
     * trend streaming function
     * 
    \*******************************/
    public function trend()
    {
        echo Term::line('Using trend stream:');

        $query = Document::list(
            [ 'twitter', 'trends' ],
            'trend',
            [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => Carbon::now()->subDays(1)->format('Y-m-d')
                                ]
                            ]
                        ]
                    ]
                ],
                'aggs' => [
                    'unique' => [
                        'terms' => [
                            'field' => 'title',
                            'size' => 4000,
                            'order' => [
                                '_count' => 'DESC'
                            ]
                        ]
                    ]
                ]
            ]
        );

        $filtered = array_map(function ($q) {
            return $q['key'];
        }, $query->data['aggregations']['unique']['buckets']);

        $keywords = implode(',', $filtered);

        if (count($filtered))
        {
            echo Term::line($keywords);

            $this->header(
                [
                    'client_id' => config('services.twitter.client_id'),
                    'client_secret' => config('services.twitter.client_secret'),
                    'access_token' => config('services.twitter.access_token'),
                    'access_token_secret' => config('services.twitter.access_token_secret')
                ]
            );

            $response = $this->client->post('statuses/filter.json', [
                'form_params' => [
                    'language' => 'tr',
                    'track' => $keywords
                ]
            ]);

            return $response->getBody();
        }
        else
        {
            echo Term::line('Trend list not found.');

            exit();
        }
    }

    # 
    # readline
    # 
    public function readline($stream, $buffer = '', $size = 0)
    {
        $negEolLen = -strlen(PHP_EOL);

        while (!$stream->eof())
        {
            if (false === ($byte = $stream->read(1)))
            {
                return $buffer;
            }

            $buffer .= $byte;

            if (++$size == null|| substr($buffer, $negEolLen) === PHP_EOL)
            {
                break;
            }
        }

        return $buffer;
    }
}
