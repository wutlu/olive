<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
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
            'report_password' => 'nullable|string|max:32',
            'report_date_1' => 'nullable|date_format:Y-m-d|before:date_2',
            'report_date_2' => 'nullable|date_format:Y-m-d',
        ];
    }
}
