<?php

namespace App\Http\Requests\Twitter;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Twitter\BlockedTrendKeywords;

class CreateBlockedTrendKeywordRequest extends FormRequest
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
            'string' => 'required|string|min:2|max:32|unique:twitter_blocked_trend_keywords,keyword'
        ];
    }
}
