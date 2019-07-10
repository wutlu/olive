<?php

namespace App\Http\Requests\User\Partner;

use Illuminate\Foundation\Http\FormRequest;

class ActionRequest extends FormRequest
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
            'name' => 'required|string|exists:users,name',
            'message' => 'nullable|string|max:255',
            'status' => 'required|string|in:success,pending,cancelled',
            'amount' => 'required|numeric|min:-500000|max:500000'
        ];
    }
}
