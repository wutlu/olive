<?php

namespace App\Http\Requests\Trend;

use Illuminate\Foundation\Http\FormRequest;

class TrendRequest extends FormRequest
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
            'module' => 'required|string|in:news,entry,youtube_video,google,twitter_tweet,twitter_favorite,twitter_hashtag,blog,instagram_hashtag'
        ];
    }
}
