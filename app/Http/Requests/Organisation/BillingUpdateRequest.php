<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Organisation\OrganisationInvoice;

class BillingUpdateRequest extends FormRequest
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
            'flood' => 'Henüz ödenmemiş bir faturanız mevcut.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();

        Validator::extend('flood', function() use ($user) {
            return !OrganisationInvoice::where('organisation_id', $user->organisation_id)->whereNull('paid_at')->exists();
        });

        return [
            'month'              => 'required|integer|min:3|max:48',

            'type'               => 'required|string|in:individual,corporate,person',

            'protected'          => 'nullable|in:on',

            'person_name'        => 'nullable|string|max:55|required_unless:type,corporate',
            'person_lastname'    => 'nullable|string|max:55|required_unless:type,corporate',
            'person_tckn'        => 'nullable|integer|tckn|required_if:type,person',

            'tckn_without'       => 'nullable|in:on',

            'merchant_name'      => 'nullable|string|max:128|required_unless:type,individual',

            'tax_number'         => 'nullable|integer|max:9999999999|required_if:type,corporate',
            'tax_office'         => 'nullable|string|max:32|required_unless:type,individual',

            'country_id'         => 'required|integer|exists:countries,id',
            'state_id'           => 'required|integer|exists:states,id',
            'city'               => 'required|string|max:32',
            'address'            => 'required|string|max:255',
            'postal_code'        => 'required|numeric|max:99999',

            'tos'                => 'required|in:on|flood'
        ];
    }
}
