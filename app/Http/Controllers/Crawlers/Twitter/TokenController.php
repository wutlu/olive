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
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Token listesi.
     *
     * @return view
     */
    public function tokens()
    {
        return view('crawlers.twitter.tokens');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Token listesi.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Token bilgileri.
     *
     * @return array
     */
    public static function token(IdRequest $request)
    {
        $token = Token::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $token
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Token oluştur.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Token güncelle.
     *
     * @return view
     */
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
        $token->status = 'off';
        $token->error_count = 0;
        $token->off_reason = null;
        $token->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Token sil.
     *
     * @return view
     */
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

    /**
     ********************
     ******* ROOT *******
     ****** SYSTEM ******
     ********************
     *
     * Twitter, Token kontrolü.
     *
     * @return view
     */
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
                $response = $client->get('account/verify_credentials.json');

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
