<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'               => 'required|string|max:64|min:4',
            'email'              => 'required|email|max:64|unique:users,email',
            'password'           => 'required|string|max:32|min:4',
            'terms'              => 'accepted',
            'gRecaptchaResponse' => 'required|recaptcha'
        ];
    }
}
