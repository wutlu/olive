<?php

namespace App\Http\Requests\Twitter;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Twitter\Token;

use Validator;

class CreateTokenRequest extends FormRequest
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
            'private_unique' => 'Consumer key ve Access token daha önce kaydedildi.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('bail_consumer_key', function($attribute, $consumer_key) {
            session()->flash('consumer_key', $consumer_key);

            return true;
        });

        Validator::extend('private_unique', function($attribute, $access_token) {
            return !Token::where(
                [
                    'consumer_key' => session('consumer_key'),
                    'access_token' => $access_token
                ]
            )->exists();
        });

        return [
            'consumer_key' => 'required|string|max:255|bail_consumer_key',
            'consumer_secret' => 'required|string|max:255',
            'access_token' => 'required|string|max:255|private_unique',
            'access_token_secret' => 'required|string|max:255',
            'off_limit' => 'required|integer|min:10|max:100'
        ];
    }
}
