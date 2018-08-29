<?php

namespace App\Http\Requests\Organisation\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class InvoiceApproveRequest extends FormRequest
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
            'no'       => 'required|string|max:100',
            'serial'   => 'required|string|max:100',
            'approve'  => 'sometimes|string|in:on'
        ];
    }
}
