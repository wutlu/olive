<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

use Validator;

use App\Models\Option;

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
    public function rules(Request $request)
    {
        $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

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
            'alarm_limit' => 'required|integer|max:12|min:1',
            'pin_group_limit' => 'required|integer|max:12|min:1',
            'saved_searches_limit' => 'required|integer|max:12|min:1',

            'data_pool_youtube_channel_limit' => 'required|integer|max:100|min:10',
            'data_pool_youtube_video_limit' => 'required|integer|max:100|min:10',
            'data_pool_youtube_keyword_limit' => 'required|integer|max:100|min:10',
            'data_pool_twitter_keyword_limit' => 'required|integer|max:400|min:10',
            'data_pool_twitter_user_limit' => 'required|integer|max:5000|min:10',

            'unit_price' => 'required|numeric|min:1',

            'module_real_time' => 'nullable|string|in:on',
            'module_search' => 'nullable|string|in:on',
            'module_trend' => 'nullable|string|in:on',
            'module_alarm' => 'nullable|string|in:on',
            'module_pin' => 'nullable|string|in:on',
            'module_model' => 'nullable|string|in:on',
            'module_forum' => 'nullable|string|in:on',
        ];

        /**
         * modules
         */
        foreach (config('system.modules') as $key => $module)
        {
            $arr['data_'.$key] = 'nullable|string|in:on';
        }

        $request->validate($arr);

        $arr = [
            'historical_days'                  => '*',
            'real_time_group_limit'            => '*',
            'alarm_limit'                      => '*',
            'pin_group_limit'                  => '*',
            'saved_searches_limit'             => '*',

            'module_real_time'                 => '+',
            'module_search'                    => '+',
            'module_trend'                     => '+',
            'module_alarm'                     => '+',
            'module_pin'                       => '+',
            'module_model'                     => '+',
            'module_forum'                     => '+',

            'data_pool_youtube_channel_limit'  => '*',
            'data_pool_youtube_video_limit'    => '*',
            'data_pool_youtube_keyword_limit'  => '*',
            'data_pool_twitter_keyword_limit'  => '*',
            'data_pool_twitter_user_limit'     => '*',
        ];

        foreach (config('system.modules') as $key => $module)
        {
            $arr['data_'.$key] = '+';
        }

        $math_prices = 0;

        foreach ($arr as $key => $group)
        {
            if ($group == '+' && $request->{$key} == 'on')
            {
                $math_prices = $math_prices + $prices['unit_price.'.$key]['value'];
            }
            else if ($group == '*')
            {
                $math_prices = $math_prices + ($request->{$key} * $prices['unit_price.'.$key]['value']);
            }
        }

        $math_prices = $math_prices * $request->user_capacity;

        return [
            'unit_price' => 'required|numeric|min:'.$math_prices
        ];
    }
}
