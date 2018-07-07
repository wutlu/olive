<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanCalculateRequest extends FormRequest
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
            'plan'     => 'required|integer|in:2,3,4',
            'month'    => 'required|integer|min:1|max:24',
            'coupon'   => 'nullable|string|max:16|exists:organisation_discount_coupons,key',

            'name'     => 'required|string|max:32',
            'lastname' => 'required|string|max:32',
            'address'  => 'required|string|max:255',
            'notes'    => 'nullable|string|max:255'
        ];
    }
}
