<?php

namespace App\Http\Requests\Twitter;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Twitter\StreamingKeywords;

class CreateKeywordRequest extends FormRequest
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
            'unique_keyword' => 'Bu kelime zaten mevcut.',
            'limit' => 'Maksimum kelime limitine ulaştınız.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();

        Validator::extend('unique_keyword', function($attribute, $keyword) use ($user) {
            return !StreamingKeywords::where('organisation_id', $user->organisation_id)->where('keyword', $keyword)->exists();
        });

        Validator::extend('limit', function($attribute) use ($user) {
            return $user->organisation->streamingKeywords()->count() < $user->organisation->data_pool_twitter_keyword_limit;
        });

        return [
            'string' => 'required|bail|string|min:3|max:32|except_list|limit|unique_keyword'
        ];
    }
}
