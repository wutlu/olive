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

        $arr = [
            'name' => 'required|string|max:100|name',
            'status' => 'nullable|string|in:on',
            'user_capacity' => 'required|integer|max:12|min:1',
            'end_date' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:H:i',
            'historical_days' => 'required|integer|max:90|min:1',

            'real_time_group_limit' => 'required|integer|max:12|min:1',
            'search_limit' => 'required|integer|max:1000|min:20',
            'alarm_limit' => 'required|integer|max:12|min:1',
            'pin_group_limit' => 'required|integer|max:12|min:1',

            'data_pool_youtube_channel_limit' => 'required|integer|max:100|min:10',
            'data_pool_youtube_video_limit' => 'required|integer|max:100|min:10',
            'data_pool_youtube_keyword_limit' => 'required|integer|max:100|min:10',
            'data_pool_twitter_keyword_limit' => 'required|integer|max:400|min:10',
            'data_pool_twitter_user_limit' => 'required|integer|max:5000|min:10',

            'unit_price' => 'required|numeric',
        ];

        /**
         * modules
         */
        foreach (config('system.modules') as $key => $module)
        {
            $arr['data_'.$key] = 'nullable|string|in:on';
        }

        return $arr;
    }
}
