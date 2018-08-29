<?php

namespace App\Http\Requests\Discount\Coupon;

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
            'id'    => 'required|integer',
            'key'   => 'required|string|min:4|max:24',
            'rate'  => 'required|integer|min:0|max:100',
            'price' => 'required|numeric',
            'count' => 'required|integer|min:1|max:100'
        ];
    }
}
