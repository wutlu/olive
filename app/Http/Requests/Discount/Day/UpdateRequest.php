<?php

namespace App\Http\Requests\Discount\Day;

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
        return [
            'id'             => 'required|integer',
            'first_day'      => 'required|date_format:Y-m-d|before:last_day',
            'last_day'       => 'required|date_format:Y-m-d',
            'discount_rate'  => 'required|integer|min:0|max:100',
            'discount_price' => 'required|numeric'
        ];
    }
}
