<?php

namespace App\Http\Requests\Forum\Kategori;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\IdRequest;

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
    public function rules(IdRequest $request)
    {
        return [
            'name' => 'required|string|max:16',
            'slug' => 'required|slug|max:32|unique:forum_categories,slug,'.$request->id,
            'description' => 'required|string|max:255',
            'lock' => 'nullable|string|in:on'
        ];
    }
}
