<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

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
    public function rules(Request $request)
    {
        return [
            'name'     => 'required|string|max:100',
            'status'   => 'nullable|string|in:on',
            'capacity' => 'required|integer|max:12|min:1',
            'end_date' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:H:i'
        ];
    }
}
