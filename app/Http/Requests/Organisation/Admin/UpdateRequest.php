<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class UpdateRequest extends FormRequest
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
            'name' => 'Sadece alfa-nümerik karakterler ve "." nokta kullanabilirsiniz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('name', function($attribute, $value) {
            return !preg_match('/[^a-zA-Z0-9\.]/', $value);
        });

        return [
            'name'     => 'required|string|max:100|name',
            'status'   => 'nullable|string|in:on',
            'capacity' => 'required|integer|max:12|min:1',
            'end_date' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:H:i',
            'twitter_follow_limit_user' => 'required|integer|max:400',
            'twitter_follow_limit_keyword' => 'required|integer|max:4000',

            'youtube_follow_limit_channel' => 'required|integer|max:100',
            'youtube_follow_limit_keyword' => 'required|integer|max:100',
            'youtube_follow_limit_video' => 'required|integer|max:100',
        ];
    }
}
