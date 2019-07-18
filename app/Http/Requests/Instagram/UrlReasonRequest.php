<?php

namespace App\Http\Requests\Instagram;

use Illuminate\Foundation\Http\FormRequest;

class UrlReasonRequest extends FormRequest
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
            'id' => 'required|integer|exists:instagram_selves,id',
            'reason' => 'nullable|string|max:255'
        ];
    }
}
