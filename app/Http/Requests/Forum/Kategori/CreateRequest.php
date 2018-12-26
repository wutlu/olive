<?php

namespace App\Http\Requests\Forum\Kategori;

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
            'name' => 'required|string|max:16',
            'slug' => 'required|slug|max:32|unique:forum_categories,slug',
            'description' => 'required|string|max:255',
        ];
    }
}
