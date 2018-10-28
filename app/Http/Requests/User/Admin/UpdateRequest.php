<?php

namespace App\Http\Requests\User\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Requests\IdRequest;

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
    public function rules(IdRequest $request)
    {
        return [
            'name'     => 'required|string|max:100',
            'root'     => 'nullable|string|in:on',
            'password' => 'nullable|string|max:32',
            'email'    => 'required|email|unique:users,email,'.$request->id,
            'verified' => 'nullable|string|in:on',
            'avatar'   => 'nullable|string|in:on',
            'root'     => 'nullable|string|in:on'
        ];
    }
}
