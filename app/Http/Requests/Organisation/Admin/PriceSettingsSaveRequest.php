<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PriceSettingsSaveRequest extends FormRequest
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
            'data_twitter'         => 'required|integer|min:0',
            'data_sozluk'          => 'required|integer|min:0',
            'data_news'            => 'required|integer|min:0',
            'data_youtube_video'   => 'required|integer|min:0',
            'data_youtube_comment' => 'required|integer|min:0',
            'data_shopping'        => 'required|integer|min:0',

            'real_time_group_limit' => 'required|integer|min:0',
            'alarm_limit'           => 'required|integer|min:0',
            'pin_group_limit'       => 'required|integer|min:0',
            'saved_searches_limit'  => 'required|integer|min:0',
            'historical_days'       => 'required|integer|min:0',

            'data_pool_youtube_channel_limit' => 'required|integer|min:0',
            'data_pool_youtube_video_limit'   => 'required|integer|min:0',
            'data_pool_youtube_keyword_limit' => 'required|integer|min:0',
            'data_pool_twitter_keyword_limit' => 'required|integer|min:0',
            'data_pool_twitter_user_limit'    => 'required|integer|min:0',

            'module_real_time' => 'required|integer|min:0',
            'module_search'    => 'required|integer|min:0',
            'module_trend'     => 'required|integer|min:0',
            'module_alarm'     => 'required|integer|min:0',
            'module_pin'       => 'required|integer|min:0',
            'module_forum'     => 'required|integer|min:0',

            'discount_with_year' => 'required|integer|min:0,max:100',
        ];
    }
}
