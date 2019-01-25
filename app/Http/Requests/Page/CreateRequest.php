<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'title'        => 'required|string|max:255',
            'slug'         => 'required|string|max:255|unique:pages',
            'keywords'     => 'nullable|string|max:255',
            'descriptions' => 'nullable|string|max:255',
            'body'         => 'required|string|max:20000'
        ];
    }
}
