<?php

namespace App\Http\Requests\Twitter;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'consumer_key' => 'required|string|max:255|unique:twitter_tokens,consumer_key',
            'consumer_secret' => 'required|string|max:255',
            'access_token' => 'required|string|max:255',
            'access_token_secret' => 'required|string|max:255',
            'off_limit' => 'required|integer|min:10|max:100'
        ];
    }
}
