<?php

namespace App\Http\Requests\Twitter;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Twitter\StreamingUsers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use System;

class CreateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'limit' => 'Maksimum hesap limitine ulaştınız.',
            'twitter_account' => 'Hesap, takip için uygun değil.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();

        Validator::extend('twitter_account', function($attribute, $screen_name) use ($user) {
            try
            {
                $stack = HandlerStack::create();

                $oauth = new Oauth1([
                    'consumer_key' => config('services.twitter.client_id'),
                    'consumer_secret' => config('services.twitter.client_secret'),
                    'token' => config('services.twitter.access_token'),
                    'token_secret' => config('services.twitter.access_token_secret')
                ]);

                $stack->push($oauth);

                $client = new Client(
                    [
                        'base_uri' => 'https://api.twitter.com/1.1/',
                        'handler' => $stack,
                        'auth' => 'oauth'
                    ]
                );

                $response = $client->get('users/show.json', [
                    'query' => [
                        'screen_name' => $screen_name
                    ],
                    'timeout' => 10,
                    'connect_timeout' => 5,
                    'headers' => [
                        'Accept' => 'application/json'
                    ]
                ]);

                $account = json_decode($response->getBody());

                session()->flash('account', $account);

                $stuser = StreamingUsers::where(
                    [
                        'organisation_id' => $user->organisation_id,
                        'user_id' => $account->id_str
                    ]
                )->exists();

                return $stuser ? false : true;
            }
            catch (\Exception $e)
            {
                System::log(
                    $e->getMessage(),
                    'App\Http\Requests\Twitter\CreateAccountRequest::rules('.$screen_name.')',
                    9
                );

                return false;
            }
        });

        Validator::extend('limit', function($attribute) use ($user) {
            return $user->organisation->streamingUsers()->count() < $user->organisation->data_pool_twitter_user_limit;
        });

        return [
            'string' => 'required|bail|string|max:48|limit|twitter_account'
        ];
    }
}
