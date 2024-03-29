<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\User\User;

class TransferAndRemoveRequest extends FormRequest
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
            'user_in_my_organisation' => 'Seçtiğiniz kullanıcıyla aynı organizasyonda olmalısınız.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();

        Validator::extend('user_in_my_organisation', function($attribute, $user_id, $parameters) use ($user) {
            $friend = User::where('id', $user_id)->first();

            if (@$friend)
            {
                return ($user->organisation_id == $friend->organisation_id) ? true : false;
            }
            else
            {
                return false;
            }
        });

        return [
            'user_id' => 'required|user_in_my_organisation|not_in:'.$user->id
        ];
    }
}
