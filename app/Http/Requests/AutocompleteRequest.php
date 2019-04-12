<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutocompleteRequest extends FormRequest
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
            'string' => 'nullable|string|max:50',
            'limit' => 'required|integer|max:10'
        ];
    }
}
