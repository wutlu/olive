<?php

namespace App\Http\Requests\User\Partner;

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
            'organisation_name' => 'Sadece alfa-nümerik karakterler ve "." nokta kullanabilirsiniz.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('organisation_name', function($attribute, $value) {
            return !preg_match('/[^a-zA-Z0-9\.]/', $value);
        });

        return [
            'name' => 'required|string|max:16|unique:users,name|organisation_name',
            'email' => 'required|string|unique:users,email|confirmed'
        ];
    }
}
