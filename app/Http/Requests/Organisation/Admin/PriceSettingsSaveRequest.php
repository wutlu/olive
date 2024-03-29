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
            'data_twitter'                    => 'required|integer|min:0',
            'data_sozluk'                     => 'required|integer|min:0',
            'data_news'                       => 'required|integer|min:0',
            'data_blog'                       => 'required|integer|min:0',
            'data_youtube_video'              => 'required|integer|min:0',
            'data_youtube_comment'            => 'required|integer|min:0',
            'data_shopping'                   => 'required|integer|min:0',
            'data_instagram'                  => 'required|integer|min:0',

            'archive_limit'                   => 'required|integer|min:0',
            'saved_searches_limit'            => 'required|integer|min:0',
            'historical_days'                 => 'required|integer|min:0',

            'data_pool_youtube_channel_limit'  => 'required|integer|min:0',
            'data_pool_youtube_video_limit'    => 'required|integer|min:0',
            'data_pool_youtube_keyword_limit'  => 'required|integer|min:0',
            'data_pool_twitter_keyword_limit'  => 'required|integer|min:0',
            'data_pool_twitter_user_limit'     => 'required|integer|min:0',
            'data_pool_instagram_follow_limit' => 'required|integer|min:0',

            'module_real_time'                => 'required|integer|min:0',
            'module_crm'                      => 'required|integer|min:0',
            'module_search'                   => 'required|integer|min:0',
            'module_trend'                    => 'required|integer|min:0',
            'module_compare'                  => 'required|integer|min:0',
            'module_borsa'                    => 'required|integer|min:0',
            'module_report'                   => 'required|integer|min:0',
            'module_alarm'                    => 'required|integer|min:0',

            'user_price'                      => 'required|integer|min:0',

            'eagle_percent'                   => 'required|integer|min:0',
            'phoenix_percent'                 => 'required|integer|min:0',
            'gryphon_percent'                 => 'required|integer|min:0',
            'dragon_percent'                  => 'required|integer|min:0',

            'root_password'                  => 'required|string|root_password',

            'discount_with_year'              => 'required|integer|min:0,max:100',
        ];
    }
}
