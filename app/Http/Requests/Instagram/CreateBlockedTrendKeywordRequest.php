<?php

namespace App\Http\Requests\Instagram;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Instagram\BlockedTrendKeywords;

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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name' => 'Sadece alfa-nümerik karakterler ve ".-_" karakterlerini kullanabilirsiniz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('name', function($attribute, $value) {
            return !preg_match('/[^a-zğüşıöçA-ZĞÜŞİÖÇ0-9-_\.]/', $value);
        });

        return [
            'string' => 'required|string|min:2|max:32|name|unique:instagram_blocked_trend_keywords,keyword'
        ];
    }
}
