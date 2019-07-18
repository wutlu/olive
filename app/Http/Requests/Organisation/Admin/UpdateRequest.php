<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

use Validator;

use App\Models\Option;

use System;

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
        $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

        Validator::extend('name', function($attribute, $value) {
            return !preg_match('/[^a-zA-Z0-9\.]/', $value);
        });

        $validations = [
            'name' => 'required|string|max:100|name',
            'status' => 'nullable|string|in:on',
            'user_capacity' => 'required|integer|max:12|min:1',
            'end_date' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:H:i',
            'historical_days' => 'required|integer|max:90|min:0',

            'real_time_group_limit' => 'required|integer|max:12|min:0',
            'alarm_limit' => 'required|integer|max:12|min:0',
            'pin_group_limit' => 'required|integer|max:12|min:0',
            'saved_searches_limit' => 'required|integer|max:12|min:0',

            'data_pool_youtube_channel_limit' => 'required|integer|max:100|min:0',
            'data_pool_youtube_video_limit' => 'required|integer|max:100|min:0',
            'data_pool_youtube_keyword_limit' => 'required|integer|max:100|min:0',
            'data_pool_twitter_keyword_limit' => 'required|integer|max:400|min:0',
            'data_pool_twitter_user_limit' => 'required|integer|max:1000000|min:0',
            'data_pool_instagram_follow_limit' => 'required|integer|max:1000000|min:0',

            'unit_price' => 'required|numeric|min:0|max:500000',

            'module_real_time' => 'nullable|string|in:on',
            'module_search' => 'nullable|string|in:on',
            'module_trend' => 'nullable|string|in:on',
            'module_alarm' => 'nullable|string|in:on',
            'module_pin' => 'nullable|string|in:on',
            'module_model' => 'nullable|string|in:on',
            'module_forum' => 'nullable|string|in:on'
        ];

        /**
         * modules
         */
        foreach (config('system.modules') as $key => $module)
        {
            $validations['data_'.$key] = 'nullable|string|in:on';
        }

        return $validations;
    }
}
