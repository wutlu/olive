<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketSubmitRequest extends FormRequest
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
            'invoice_id' => 'nullable|integer|exists:organisation_invoices,id',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|min:10,max:500',
            'type' => 'required|string|in:'.implode(',', array_keys(config('app.ticket.types')))
        ];
    }
}
