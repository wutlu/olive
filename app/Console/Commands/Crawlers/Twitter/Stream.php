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

use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\Token;

use System;
use Storage;

use App\Console\Commands\Nohup;

class Stream extends Command
{
    private $endpoint = "https://stream.twitter.com/1.1/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:stream {--type=} {--streamId=}';

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
            $stream = $this->{ $type }();

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

                        $this->info($tweet->text);
                    }
                    else
                    {
                        print_r($obj);
                    }
                }
                catch (\Exception $e)
                {
                    System::log(
                        json_encode($e->getMessage()),
                        'App\Jobs\Crawlers\Twitter\Stream::handle('.$type.')',
                        5
                    );
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
    public function keyword()
    {
        $stream_id = $this->option('streamId');

        if ($stream_id)
        {
            echo Term::line('Using keyword stream:');

            $this->info($stream_id);

            $token = Token::where('id', $stream_id);

            if ($token->exists())
            {
                $token = $token->first();

                $this->info($token->value);

                $this->header(
                    [
                        'client_id' => $token->consumer_key,
                        'client_secret' => $token->consumer_secret,
                        'access_token' => $token->access_token,
                        'access_token_secret' => $token->access_token_secret
                    ]
                );

                $response = $this->client->post('statuses/filter.json', [
                    'form_params' => [
                        'language' => 'tr',
                        'track' => $token->value
                    ]
                ]);

                return $response->getBody();
            }
        }
        else
        {
            echo Term::line('Generating keyword stream operations:');

            $kquery = StreamingKeywords::whereNull('reasons');

            if ($kquery->count())
            {
                $tokens = Token::whereNotNull('pid')->where('sh', 'ILIKE', '%--type=keyword%');

                if ($tokens->count())
                {
                    foreach ($tokens->get() as $t)
                    {
                        $cmd = implode(' ', [
                            'kill',
                            '-9',
                            $t->pid,
                            '>>',
                            '/dev/null',
                            '2>&1',
                            '&',
                            'echo $!'
                        ]);

                        $pid = trim(shell_exec($cmd));

                        $this->error('Process Killed: ['.$t->pid.']');

                        $t->pid = null;
                        $t->sh = null;
                        $t->value = null;
                        $t->status = 'off';
                        $t->save();
                    }

                    sleep(1);
                }

                foreach ($kquery->get()->chunk(400) as $query)
                {
                    $pkeyword = [];

                    foreach ($query as $keyword)
                    {
                        $pkeyword[] = $keyword->keyword;
                    }

                    $token = Token::whereNull('pid')
                                  ->where('status', 'off')
                                  ->orderBy('updated_at', 'ASC');

                    $this->info('Keywords: ['.count($pkeyword).']');

                    if ($token->exists())
                    {
                        $token = $token->first();

                        $sh = 'twitter:stream --streamId='.$token->id.' --type=keyword';

                        $key = implode('/', [ 'processes', md5($sh) ]);

                        $cmd = implode(' ', [
                            'nohup',
                            'php',
                            base_path('artisan'),
                            $sh,
                            '>>',
                            '/dev/null',
                            '2>&1',
                            '&',
                            'echo $!'
                        ]);

                        $pid = trim(shell_exec($cmd));

                        Storage::put($key, json_encode([ 'pid' => trim($pid), 'command' => $sh ]));

                        $this->info('['.$sh.'] process started.');

                        $token->sh = $sh;
                        $token->value = implode(',', $pkeyword);
                        $token->status = 'on';
                        $token->pid = $pid;
                        $token->save();
                    }
                    else
                    {
                        $message = 'Twitter kelime akışı için yeterli token bulunamadı.';

                        $this->error($message);

                        System::log(
                            json_encode($message),
                            'App\Console\Commands\Crawlers\Twitter\Stream::keyword()',
                            10
                        );
                    }
                }
            }
            else
            {
                echo Term::line('Keyword list not found.');
            }

            exit();
        }
    }

    /*******************************\
     * 
     * user streaming function
     * 
    \*******************************/
    public function user()
    {
        echo Term::line('user stream');

        exit();
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
                            'size' => 400,
                            'order' => [
                                '_count' => 'DESC'
                            ]
                        ]
                    ]
                ]
            ]
        );

        $filtered = array_map(function ($q) {
            return Term::convertAscii($q['key']);
        }, $query->data['aggregations']['unique']['buckets']);

        $keywords = implode(',', $filtered);

        if (count($filtered))
        {
            echo Term::line($keywords);
            echo Term::line('('.count($filtered).') keyword following!');

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
