<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\User\User;

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
            'organisation_name' => 'Sadece alfa-nümerik karakterler ve "-" tire kullanabilirsiniz.',
            'organisation_exists' => 'Bu kullanıcı zaten bir organizasyona dahil.'
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
            return !preg_match('/[^a-zA-Z0-9-]/', $value);
        });

        Validator::extend('organisation_exists', function($attribute, $name) {
            return User::where('name', $name)->whereNull('organisation_id')->exists();
        });

        return [
            'user_name' => 'required|bail|string|max:100|exists:users,name|organisation_exists',
            'organisation_name' => 'required|string|max:100|organisation_name'
        ];
    }
}
