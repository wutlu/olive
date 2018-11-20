<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\SetRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Twitter\CreateTokenRequest;
use App\Http\Requests\Twitter\UpdateTokenRequest;

use Carbon\Carbon;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Log;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;
use App\Models\Twitter\Account;
use App\Models\Twitter\Token;
use App\Models\Option;

use App\Utilities\Term;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Jobs\Elasticsearch\CreateTwitterIndexJob;

use Validator;

class TwitterController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # dashboard
    # 
    public static function dashboard()
    {
        $rows = Option::whereIn('key', [
            'twitter.trend.status',
            'twitter.status',
            'twitter.index.trends',
            'twitter.index.tweets'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.twitter.dashboard', compact('options'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index listesi view.
    # 
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'twitter.index.auto',
            'twitter.index.trends'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.twitter.indices', compact('options'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index listesi json çıktısı.
    # 
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.hosts')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/olive__twitter*?format=json&s=index:desc')->getBody();
        $source = json_decode($source);

        return [
            'status' => 'ok',
            'hits' => $source
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # log ekranı json çıktısı.
    # 
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%twitter%')
                   ->where('updated_at', '>', $date)
                   ->orderBy('updated_at', 'DESC')
                   ->get();

        return [
            'status' => 'ok',
            'data' => $logs
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # istatistikler
    # 
    public static function statistics()
    {
        return [
            'status' => 'ok',
            'data' => [
                'twitter' => [
                    'tweets' => Indices::stats([ 'twitter', 'tweets', '*' ]),
                    'trends' => Indices::stats([ 'twitter', 'trends' ]),
                    'size' => [
                        'tweet' => Indices::stats([ 'twitter', 'tweets', '*' ]),
                        'trend' => Indices::stats([ 'twitter', 'trends' ])
                    ]
                ]
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # ayar güncelle
    # 
    public static function set(SetRequest $request)
    {
        $option = Option::where('key', $request->key);
        
        $error = true;

        if ($option->exists())
        {
            $option = $option->first();

            if ($request->key == 'twitter.index.tweets')
            {
                if ($option->value == date('Y.m', strtotime('+ 1 month')))
                {
                    $error = false;
                }
            }
            else if ($request->key == 'twitter.index.trends')
            {
                if ($option->value == 'on')
                {
                    $error = false;
                }
            }
            else
            {
                $error = false;
            }
        }

        if ($error)
        {
            return response(
                [
                    'status' => 'err',
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'token' => [ 'Önce index oluşturmanız gerekiyor.' ]
                    ]
                ],
                422
            );
        }

        Option::updateOrCreate(
            [
                'key' => $request->key
            ],
            [
                'value' => $request->value
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # trend başlıklar için index oluştur.
    # 
    public static function indexCreate()
    {
        CreateTwitterIndexJob::dispatch('trends')->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # twitter index durumu.
    # 
    public static function indexStatus()
    {
        return [
            'trends' => Indices::stats([ 'twitter', 'trends' ])
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bağlı hesaplar view
    # 
    public static function accounts()
    {
        return view('crawlers.twitter.accounts');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bağlı hesaplar json
    # 
    public static function accountsViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new Account;
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%')
                                          ->orWhere('screen_name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token listesi view.
    # 
    public function tokens()
    {
        return view('crawlers.twitter.tokens');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token listesi json çıktısı.
    # 
    public static function tokensJson()
    {
        $tokens = new Token;
        $tokens = $tokens->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $tokens->get(),
            'total' => $tokens->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token bilgileri.
    # 
    public static function token(IdRequest $request)
    {
        $token = Token::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $token
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token oluştur.
    # 
    public static function tokenCreate(CreateTokenRequest $request)
    {
        self::tokenRequest($request);

        $validator = Validator::make($request->all(), [
            'consumer_key' => 'required|token_check'
        ]);

        if ($validator->fails())
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'token' => [ 'Token bilgileri geçerli değil.' ]
                    ]
                ],
                422
            );
        }

        $token = new Token;
        $token->fill($request->all());
        $token->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token güncelle.
    # 
    public static function tokenUpdate(UpdateTokenRequest $request)
    {
        self::tokenRequest($request);

        $validator = Validator::make($request->all(), [
            'consumer_key' => 'required|token_check'
        ]);

        if ($validator->fails())
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'token' => [ 'Token bilgileri geçerli değil.' ]
                    ]
                ],
                422
            );
        }

        $token = Token::where('id', $request->id)->firstOrFail();
        $token->fill($request->all());
        //$token->pid = null;
        //$token->status = 'off';
        $token->error_count = 0;
        $token->off_reason = null;
        $token->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token güncelle.
    # 
    public static function tokenDelete(IdRequest $request)
    {
        $token = Token::where('id', $request->id)->firstOrFail();

        if ($token->status == 'off')
        {
            $arr = [
                'status' => 'ok',
                'data' => [
                    'id' => $token->id
                ]
            ];

            $token->delete();

            return $arr;
        }
        else
        {
            return [
                'status' => 'err'
            ];
        }
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token kontrol fonksiyonu.
    #
    private static function tokenRequest(Request $request)
    {
        Validator::extend('token_check', function($attribute, $value, $parameters) use($request) {
            $stack = HandlerStack::create();

            $oauth = new Oauth1([
                'consumer_key' => $request->consumer_key,
                'consumer_secret' => $request->consumer_secret,
                'token' => $request->access_token,
                'token_secret' => $request->access_token_secret
            ]);

            $stack->push($oauth);

            $client = new Client(
                [
                    'base_uri' => 'https://api.twitter.com/1.1/',
                    'handler' => $stack,
                    'auth' => 'oauth'
                ]
            );

            try
            {
                $response = $client->get('statuses/show.json', [
                    'query' => [
                        'id' => 20
                    ],
                    'timeout' => 10,
                    'connect_timeout' => 5,
                    'headers' => [
                        'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                        'Accept' => 'application/json'
                    ]
                ]);

                $obj = json_decode($response->getBody());

                return true;
            }
            catch (\Exception $e)
            {
                return false;
            }
        });
    }
}
