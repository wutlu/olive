<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'id' => 'nullable|integer',

            'partner' => 'nullable|string|in:eagle,phoenix,gryphon,dragon',
            'sort' => 'nullable|string|in:asc,desc'
        ];
    }
}
