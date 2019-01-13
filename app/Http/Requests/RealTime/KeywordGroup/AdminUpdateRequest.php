<?php

namespace App\Http\Requests\RealTime\KeywordGroup;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

class AdminUpdateRequest extends FormRequest
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
            'id' => 'required|integer|exists:real_time_keyword_groups,id',
            'keywords' => 'nullable|string|max:256'
        ];
    }
}
