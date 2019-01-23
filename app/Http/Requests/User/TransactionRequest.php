<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

class TransactionRequest extends FormRequest
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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'max_price' => 'Bakiyeniz yeterli değil.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('max_price', function($attribute, $price) {
            return $price <= intval(auth()->user()->balance()) ? true : false;
        });

        return [
            'price' => 'required|integer|min:100|max_price',
            'iban_name' => 'required|string|max:128|min:4',
            'iban' => 'required|string|iban',
        ];
    }
}
