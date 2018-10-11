<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;
use App\Utilities\Term;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class Stream extends Command
{
    private $endpoint = "https://stream.twitter.com/1.1/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:stream {--key=} {--type=}';

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
        $type = $this->option('type');
        $types = [
            'keyword' => 'Keyword Stream',
            'user' => 'User Stream'
        ];

        if (!$type)
        {
            $type = $this->choice('What kind of a start streaming?', $types, $type);
        }

        if (array_key_exists($type, $types))
        {
            $stream = $this->{ $type }($this->option('key'));

            $i = 0;

            while (!$stream->eof())
            {
                $i++;

                echo "\033[5D";
                echo str_pad($i, 3, ' ', STR_PAD_LEFT);

                try
                {
                    $tweet = json_decode($this->readLine($stream), true);

                    $tweet['text'];
                    //echo Term::line($tweet['text']);
                }
                catch (\Exception $e)
                {
                    print_r($tweet);
                }
            }
        }
        else
        {
            $this->error('Entered value is not valid.');
        }
    }

    /*
     * stream header
     */
    public function header()
    {
        $stack = HandlerStack::create();

        $oauth = new Oauth1([
            'consumer_key' => 'k6VJe7V43CXCfEMnORY8h0aa2',
            'consumer_secret' => '5F2QAzIalmc6Y8HRCTny8r18zxRgqAQY78UrZOITS8IrmJAU8o',
            'token' => '1033402875119058946-0dZxL2GySHE2SnkKv6u1TcwhuyMxcS',
            'token_secret' => 'Awpp5lJkbEnm3vjc03D5aNRLRH54XWBn8BIE8qam5mH12'
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

    /*
     * keyword streaming function
     */
    public function keyword(string $key)
    {
        $this->header();

        $response = $this->client->post('statuses/filter.json', [
            'form_params' => [
                'language' => 'tr',
                'track' => $key
            ]
        ]);

        return $response->getBody();
    }

    /*
     * user streaming function
     */
    public function user()
    {
        echo Term::line('user stream');
    }

    /*
     * readline
     */
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
