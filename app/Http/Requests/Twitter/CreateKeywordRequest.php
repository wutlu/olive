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
            'except_list' => 'Bu kelimeyi kullanamazsınız.',
            'limit' => 'Maksimum kelime limitine ulaştınız.',
            'organisation_status' => 'Organizasyonunuz henüz aktif değil.'
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

        Validator::extend('except_list', function($attribute, $keyword) {
            return !in_array(str_slug($keyword, ' '), config('services.twitter.unaccepted_keywords'));
        });

        Validator::extend('limit', function($attribute) use ($user) {
            return count($user->organisation->streamingKeywords) < $user->organisation->twitter_follow_limit_keyword;
        });

        Validator::extend('organisation_status', function($attribute) use ($user) {
            return $user->organisation->status == true;
        });

        return [
            'keyword' => 'required|bail|string|min:3|max:32|except_list|limit|unique_keyword|organisation_status'
        ];
    }
}
