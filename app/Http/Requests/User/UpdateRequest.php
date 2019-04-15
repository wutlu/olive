<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = auth()->user()->id;

        return [
            'email' => 'required|email|max:64|unique:users,email,'.$id,
            'password' => 'nullable|string|max:32|min:4',
            'name' => 'required|string|max:48|min:4|unique:users,name,'.$id,
            'about' => 'nullable|string|max:10000',
        ];
    }
}
