<?php

namespace App\Http\Requests\User\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\IdRequest;

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
            'id'           => 'required|integer|exists:users,id',
            'name'         => 'required|string|max:100|unique:users,name,'.$request->id,
            'root'         => 'nullable|string|in:on',
            'password'     => 'nullable|string|max:32',
            'email'        => 'required|email|unique:users,email,'.$request->id,
            'verified'     => 'nullable|string|in:on',
            'avatar'       => 'nullable|string|in:on',
            'moderator'    => 'nullable|string|in:on',
            'ban_reason'   => 'nullable|string|max:255',
            'about'        => 'nullable|string|max:10000',
            'partner_for_once_percent' => 'required|integer|max:100',
        ];
    }
}
