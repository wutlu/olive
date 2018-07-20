<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Validator;
use App\User;

class InviteRequest extends FormRequest
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
            'user_out_organisation' => 'Bu kullanıcı zaten bir organizasyona dahil.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('user_out_organisation', function($attribute, $email) {
            $user = User::where('email', $email)->first();

            return $user->organisation_id ? false : true;
        });

        return [
            'email' => 'required|string|email|exists:users,email|user_out_organisation',
        ];
    }
}
