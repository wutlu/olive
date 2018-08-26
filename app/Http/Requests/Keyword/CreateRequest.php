<?php

namespace App\Http\Requests\Keyword;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'private_unique' => 'Bu kelime havuzunuzda zaten mevcut.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('private_unique', function($attribute, $keyword) {
            return auth()->user()->organisation->keywords()->where('keyword', $keyword)->doesntExist();
        });

        return [
            'keyword' => 'required|string|max:32|private_unique'
        ];
    }
}
