<?php

namespace App\Http\Requests\YouTube\Reason;

use Illuminate\Foundation\Http\FormRequest;

class KeywordRequest extends FormRequest
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
            'id' => 'required|integer|exists:youtube_following_keywords,id',
            'reason' => 'nullable|string|max:255'
        ];
    }
}
