<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
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
            'plan_id'            => 'required|integer|in:2,3,4|check_email_verification',
            'month'              => 'required|integer|min:3|max:24',
            'coupon_code'        => 'nullable|string|max:16|coupon_exists',

            'type'               => 'required|string|in:individual,corporate,person',

            'person_name'        => 'nullable|string|max:55|required_unless:type,corporate',
            'person_lastname'    => 'nullable|string|max:55|required_unless:type,corporate',
            'person_tckn'        => 'nullable|integer|tckn|required_if:type,person',

            'tckn_without'       => 'nullable|in:on',

            'merchant_name'      => 'nullable|string|max:32|required_unless:type,individual',

            'tax_number'         => 'nullable|integer|max:9999999999|required_if:type,corporate',
            'tax_office'         => 'nullable|string|max:32|required_unless:type,individual',

            'country_id'         => 'required|integer|exists:countries,id',
            'state_id'           => 'required|integer|exists:states,id',
            'city'               => 'required|string|max:32',
            'address'            => 'required|string|max:255',
            'postal_code'        => 'required|numeric|max:99999',

            'tos'                => 'accepted'
        ];
    }
}
