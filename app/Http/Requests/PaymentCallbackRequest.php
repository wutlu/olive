<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentCallbackRequest extends FormRequest
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
            'merchant_oid' => 'required|integer',
            'status' => 'required|string|max:100',
            'total_amount' => 'nullable',
            'hash' => 'required|string|max:255',

            'failed_reason_msg' => 'nullable|string|max:255',
            'failed_reason_code' => 'nullable|integer|max:100',
        ];
    }
}
