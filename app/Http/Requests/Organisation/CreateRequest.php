<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
        $validations = [
            'user_capacity' => 'required|integer|max:12|min:1',
            'historical_days' => 'required_with:module_search,module_real_time,module_crm,module_alarm|integer|max:90|min:0',

            'archive_limit' => 'nullable|integer|max:12|min:0',
            'saved_searches_limit' => 'required_with:module_search,module_real_time,module_crm,module_alarm,module_compare|integer|max:12|min:0',

            'data_pool_youtube_channel_limit' => 'nullable|integer|max:100|min:0',
            'data_pool_youtube_video_limit' => 'nullable|integer|max:100|min:0',
            'data_pool_youtube_keyword_limit' => 'nullable|integer|max:100|min:0',
            'data_pool_instagram_follow_limit' => 'nullable|integer|max:100|min:0',
            'data_pool_twitter_keyword_limit' => 'nullable|integer|max:100|min:0',
            'data_pool_twitter_user_limit' => 'nullable|integer|max:100|min:0',

            'module_real_time' => 'nullable|string|in:on',
            'module_crm' => 'nullable|string|in:on',
            'module_search' => 'required_with:module_real_time,module_crm,module_alarm|string|in:on',
            'module_trend' => 'nullable|string|in:on',
            'module_compare' => 'nullable|string|in:on',
            'module_borsa' => 'nullable|string|in:on',
            'module_report' => 'nullable|string|in:on',
            'module_alarm' => 'nullable|string|in:on',
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
