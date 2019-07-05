<?php

namespace App\Http\Requests\User\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

use Validator;

use App\Models\User\User;
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
        return auth()->user()->partner ? true : false;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'partner_user' => 'Bu kullanıcı farklı bir partnerin referansını kullanıyor.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        Validator::extend('partner_user', function($attribute, $user_id) {
            $user = User::find($user_id);

            return @$user->partner_user_id == auth()->user()->id;
        });

        $base_validations = [
            'user_id' => 'required|integer|exists:users,id|partner_user'
        ];

        $request->validate($base_validations);

        $user = User::find($request->user_id);

        if ($user->organisation_id)
        {
            $validations = [
                'user_capacity' => 'required|integer|max:12|min:1',
                'end_date' => 'required|date_format:Y-m-d|before:'.date('Y-m-d', strtotime('+30 days', strtotime($user->organisation->created_at))),
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

                'unit_price' => 'required|numeric|min:10|max:500000',

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

            $request->validate($validations);

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

            $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

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

            $partner_percent = System::option('formal.partner.'.auth()->user()->partner.'.percent');

            $math_prices = $math_prices * $request->user_capacity;
            $math_prices = ($math_prices / 100 * $partner_percent) + $math_prices;
            $math_prices = ($math_prices / 100 * $partner_percent) + $math_prices;
            $math_prices = intval($math_prices);

            return [
                'unit_price' => 'required|numeric|min:'.$math_prices
            ];
        }

        return $base_validations;
    }
}
