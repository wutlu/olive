<?php

namespace App\Http\Requests\User\Admin;

use Illuminate\Foundation\Http\FormRequest;

use App\Http\Requests\IdRequest;

use Validator;

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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'only_root' => 'Sistem sorumlusu yetkisi sadece sistem sorumluları tarafından değiştirilebilir.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(IdRequest $request)
    {
        Validator::extend('only_root', function($attribute) {
            return auth()->user()->root ? true : false;
        });

        return [
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:100|unique:users,name,'.$request->id,
            'root' => 'nullable|string|in:on|only_root',
            'admin' => 'nullable|string|in:on',
            'password' => 'nullable|string|max:32',
            'email' => 'required|email|unique:users,email,'.$request->id,
            'verified' => 'nullable|string|in:on',
            'avatar' => 'nullable|string|in:on',
            'moderator' => 'nullable|string|in:on',
            'ban_reason' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:10000',
            'partner' => 'nullable|string|in:eagle,phoenix,gryphon,dragon',
            'gsm' => 'nullable|regex:/^\(5\d{2}\) \d{3} \d{2} \d{2}/i|unique:users,gsm,'.$request->id
        ];
    }
}
