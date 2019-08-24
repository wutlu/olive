<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Instagram;
use App\Utilities\DateUtility;
use App\Olive\Gender;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Models\Twitter\Token;

class TestController extends Controller
{
    public static function test()
    {
            $token = Token::where('id', 12)->first();
            $stack = HandlerStack::create();
            $oauth = new Oauth1([
                'consumer_key' => $token->consumer_key,
                'consumer_secret' => $token->consumer_secret,
                'token' => $token->access_token,
                'token_secret' => $token->access_token_secret
            ]);

            $stack->push($oauth);

            $client = new Client(
                [
                    'base_uri' => 'https://api.twitter.com/1.1/',
                    'handler' => $stack,
                    'auth' => 'oauth'
                ]
            );

            $response = $client->get('statuses/user_timeline.json?screen_name=ayembeko');


            echo json_encode(json_decode($response->getBody()), JSON_PRETTY_PRINT);
    }
}
