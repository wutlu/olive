<?php

namespace App\Http\Requests\Twitter\Stream;

use Illuminate\Foundation\Http\FormRequest;

class KeywordReasonRequest extends FormRequest
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
            'id' => 'required|integer|exists:twitter_streaming_keywords',
            'reason' => 'nullable|string|max:255'
        ];
    }
}
