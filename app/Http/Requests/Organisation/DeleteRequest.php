<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Validator;
use App\User;

class DeleteRequest extends FormRequest
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
            'owner' => 'Bu işlemi sadece organizasyon sahipleri yapabilir.'
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

        Validator::extend('owner', function() use ($user) {
            return $user->id == $user->organisation->user_id ? true : false;
        });

        return [
            'delete_key' => 'required|in:organizasyonu silmek istiyorum|owner',
            'password' => 'required|password_check'
        ];
    }
}
