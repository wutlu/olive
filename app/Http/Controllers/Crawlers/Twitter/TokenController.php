<?php

namespace App\Http\Controllers\Crawlers\Twitter;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\Twitter\CreateTokenRequest;
use App\Http\Requests\Twitter\UpdateTokenRequest;

use App\Models\Twitter\Token;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use Validator;

class TokenController extends Controller
{
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
        self::tokenRequest(
            $request->consumer_key,
            $request->consumer_secret,
            $request->access_token,
            $request->access_token_secret
        );

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
        self::tokenRequest(
            $request->consumer_key,
            $request->consumer_secret,
            $request->access_token,
            $request->access_token_secret
        );

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
        $token->error_count = 0;
        $token->off_reason = null;
        $token->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # token sil.
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
    private static function tokenRequest(string $consumer_key, string $consumer_secret, string $access_token, string $access_token_secret)
    {
        Validator::extend('token_check', function($attribute) use($consumer_key, $consumer_secret, $access_token, $access_token_secret) {
            $stack = HandlerStack::create();

            $oauth = new Oauth1([
                'consumer_key' => $consumer_key,
                'consumer_secret' => $consumer_secret,
                'token' => $access_token,
                'token_secret' => $access_token_secret
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
