<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QRequest extends FormRequest
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
            'q' => 'nullable|string|max:255',
            's' => 'nullable|date_format:Y-m-d',
            'e' => 'nullable|required_with:s|date_format:Y-m-d',
        ];
    }
}
