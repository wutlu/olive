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
            'user_out_organisation' => 'Bu kullanıcı zaten bir organizasyona dahil.',
            'organisation_capacity_control' => 'Organizasyon kapasitesi dolu.'
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

            return @$user ? ($user->organisation_id ? false : true) : true;
        });

        Validator::extend('organisation_capacity_control', function() {
            $user = auth()->user();

            return count($user->organisation->users) >= $user->organisation->capacity ? false : true;
        });

        return [
            'email' => 'required|string|email|exists:users,email|user_out_organisation|organisation_capacity_control',
        ];
    }
}
