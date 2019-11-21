<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class AggsRequest extends FormRequest
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
            'title' => 'required|string|min:4|max:64',
            'subtitle' => 'nullable|string|min:10|max:128',
            'text' => 'nullable|string|max:2000',
            'type' => 'required|string|in:stats,chart,tr_map,twitterMentions,twitterInfluencers,twitterUsers,youtubeUsers,youtubeComments,sozlukSites,sozlukUsers,sozlukTopics,newsSites,blogSites,shoppingSites,shoppingUsers',
            'data' => 'required|json|max:50000'
        ];
    }
}
