<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateRequest extends FormRequest
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
    public function rules(Request $request)
    {
        return [
            'id'           => 'required|integer|exists:pages,id',
            'title'        => 'required|string|max:255',
            'slug'         => 'required|string|max:255|unique:pages,slug,'.$request->id,
            'keywords'     => 'nullable|string|max:255',
            'descriptions' => 'nullable|string|max:255',
            'body'         => 'required|string|max:10000'
        ];
    }
}
