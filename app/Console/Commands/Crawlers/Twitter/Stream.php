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
use App\Models\Twitter\StreamingUsers;
use App\Models\Twitter\Token;

use App\Jobs\Crawlers\Social\Twitter\DeletedTweetJob;

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
    protected $signature = 'twitter:stream {--type=} {--tokenId=}';

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
            'user'      => 'User Stream',
            'keyword'   => 'Keyword Stream',
            'trend'     => 'Trend Stream'
        ];

        if (!$type)
        {
            $type = $this->choice('What kind of a start streaming?', $types, $type);
        }

        if (array_key_exists($type, $types))
        {
            try
            {
                if ($type == 'trend')
                {
                    $stream = $this->trend();
                }
                else
                {
                    $stream = $this->stream_header($type);
                }
            }
            catch (\Exception $e)
            {
                System::log(
                    json_encode($e->getMessage()),
                    'App\Jobs\Crawlers\Twitter\Stream::handle()',
                    10
                );

                $this->error($e->getMessage());

                die();
            }

            $bulk = [];

            while (!$stream->eof())
            {
                try
                {
                    $obj = json_decode($this->read_line($stream), true);

                    if (@$obj['delete'])
                    {
                        DeletedTweetJob::dispatch($obj['delete'])->onQueue('crawler')->delay(now()->addMinutes(1));
                    }

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
                }
                catch (\Exception $e)
                {
                    System::log(
                        json_encode($e->getMessage()),
                        'App\Jobs\Crawlers\Twitter\Stream::handle('.$type.')',
                        5
                    );
                    $this->error($e->getMessage());
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

        if (@$query->data['aggregations']['unique']['buckets'])
        {
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
                $this->error('Trend list not found.');

                exit();
            }
        }
    }

    # 
    # read_line
    # 
    public function read_line($stream, $buffer = '', $size = 0)
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

    # 
    # stream header
    # 
    protected function stream_header(string $type)
    {
        $option = Option::where('key', 'twitter.status')->first();

        if (@$option)
        {
            $stream_id = $this->option('tokenId');

            if ($stream_id)
            {
                echo Term::line('Using '.$type.' stream:');

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

                    $form_params = [];

                    switch ($type)
                    {
                        case 'keyword':
                            $form_params = [
                                'language' => 'tr',
                                'track' => $token->value
                            ];
                        break;
                        case 'user':
                            $form_params = [
                                'follow' => $token->value
                            ];
                        break;
                    }

                    $response = $this->client->post('statuses/filter.json', [
                        'form_params' => $form_params
                    ]);

                    return $response->getBody();
                }
                else
                {
                    $this->error('Token bulunamadı.');

                    die();
                }
            }
            else
            {
                $tokens = Token::whereNotNull('pid')->where('sh', 'ILIKE', '%--type='.$type.'%');
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

                        $t->status  = 'off';
                        $t->pid     = null;
                        $t->sh      = null;
                        $t->value   = null;
                        $t->save();
                    }
                }

                if ($option->value == 'on')
                {
                    $this->info('Generating '.$type.' stream operations:');

                    sleep(1);

                    if ($type == 'keyword')
                    {
                        $kquery = new StreamingKeywords;
                        $chunk = 400;
                        $value_column = 'keyword';
                    }
                    else // if ($type == 'user')
                    {
                        $kquery = new StreamingUsers;
                        $chunk = 5000;
                        $value_column = 'user_id';
                    }

                    $kquery = $kquery->with('organisation')
                                     ->whereNull('reasons')
                                     ->whereHas('organisation', function ($query) {
                                        $query->where('status', true);
                                     })
                                     ->distinct();

                    if ($kquery->count())
                    {
                        foreach ($kquery->get()->chunk($chunk) as $query)
                        {
                            $bucket = [];

                            foreach ($query as $row)
                            {
                                $bucket[] = $row->{$value_column};
                            }

                            $token = Token::whereNull('pid')
                                          ->where('status', 'off')
                                          ->orderBy('updated_at', 'ASC');

                            $this->info('Bucket: ['.count($bucket).']');

                            if ($token->exists())
                            {
                                $token = $token->first();

                                $sh = 'twitter:stream --tokenId='.$token->id.' --type='.$type.'';

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
                                $token->value = implode(',', $bucket);
                                $token->status = 'on';
                                $token->pid = $pid;
                                $token->save();
                            }
                            else
                            {
                                $message = 'Twitter '.$type.' akışı için yeterli token bulunamadı.';

                                $this->error($message);

                                System::log(
                                    json_encode($message),
                                    'App\Console\Commands\Crawlers\Twitter\Stream::stream_header('.$type.')',
                                    10
                                );
                            }
                        }
                    }
                    else
                    {
                        $this->error(''.title_case($type).' list not found.');
                    }
                }
                else if ($option->value == 'off')
                {
                    $this->error('Twitter status off.');
                }

                die();
            }
        }
        else
        {
            $this->error('Twitter status not found.');

            die();
        }
    }
}
