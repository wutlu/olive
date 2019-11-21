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
                'historical_days' => 'required|integer|max:90|min:0',

                'pin_group_limit' => 'required|integer|max:12|min:0',
                'saved_searches_limit' => 'required|integer|max:12|min:0',

                'data_pool_youtube_channel_limit' => 'required|integer|max:100|min:0',
                'data_pool_youtube_video_limit' => 'required|integer|max:100|min:0',
                'data_pool_youtube_keyword_limit' => 'required|integer|max:100|min:0',
                'data_pool_instagram_follow_limit' => 'required|integer|max:100|min:0',
                'data_pool_twitter_keyword_limit' => 'required|integer|max:400|min:0',
                'data_pool_twitter_user_limit' => 'required|integer|max:1000000|min:0',
                'data_pool_instagram_follow_limit' => 'required|integer|max:1000000|min:0',

                'module_real_time' => 'nullable|string|in:on',
                'module_search' => 'nullable|string|in:on',
                'module_trend' => 'nullable|string|in:on',
                'module_compare' => 'nullable|string|in:on',
                'module_borsa' => 'nullable|string|in:on',
                'module_report' => 'nullable|string|in:on',
                'module_alarm' => 'nullable|string|in:on',
            ];

            if (ceil(abs(strtotime($user->organisation->created_at) - time()) / 86400) <= 30)
            {
                $validations['end_date'] = 'required|date_format:Y-m-d|before:'.date('Y-m-d', strtotime('+30 days', strtotime($user->organisation->created_at)));
            }

            /**
             * modules
             */
            foreach (config('system.modules') as $key => $module)
            {
                $validations['data_'.$key] = 'nullable|string|in:on';
            }

            $request->validate($validations);
        }

        return $base_validations;
    }
}
