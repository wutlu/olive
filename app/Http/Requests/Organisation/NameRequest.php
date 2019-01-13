<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class NameRequest extends FormRequest
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
            'name' => 'Sadece alfa-nümerik karakterler ve "." nokta kullanabilirsiniz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('name', function($attribute, $value) {
            return !preg_match('/[^a-zA-Z0-9\.]/', $value);
        });

        return [
            'organisation_name' => 'required|name|max:16',
        ];
    }
}
