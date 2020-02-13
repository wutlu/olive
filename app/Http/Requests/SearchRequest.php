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
            'string' => 'nullable|string|min:2|max:500',
            'skip' => 'required|integer',
            'take' => 'required|integer|max:100',
            'status' => 'nullable|string|in:on,off,success,pending,cancelled',

            'id' => 'nullable|integer',

            'partner' => 'nullable|string|in:eagle,phoenix,gryphon,dragon',
            'sort' => 'nullable|string|in:asc,desc',
            'auth' => 'nullable|string|in:root,admin,moderator',
            'direction' => 'nullable|string|in:in,out',

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
