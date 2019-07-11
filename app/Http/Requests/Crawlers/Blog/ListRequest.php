<?php

namespace App\Http\Requests\Crawlers\Blog;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
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
            'string' => 'nullable|string|min:2|max:255',
            'skip' => 'required|integer',
            'take' => 'required|integer|max:100',
            'status' => 'nullable|string|in:on,off',
            'sort' => 'nullable|string|in:error,interval,hit-up,hit-down,alexa-up,alexa-down'
        ];
    }
}
