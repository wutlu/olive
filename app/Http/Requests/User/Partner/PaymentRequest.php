<?php

namespace App\Http\Requests\User\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

use Validator;

class PaymentRequest extends FormRequest
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
            'max_amount' => 'Yeterli bakiyeniz bulunmuyor.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        Validator::extend('max_amount', function($attribute, $value) {
            return $value <= auth()->user()->partnerWallet();
        });

        return [
            'iban' => 'required|string|iban',
            'name' => 'required|string|max:64',
            'amount' => 'required|numeric|max:100000|min:1000|max_amount'
        ];
    }
}
