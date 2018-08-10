<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;
use App\Ticket;

class TicketReplyRequest extends FormRequest
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
            'permission' => 'Destek konusu artık aktif değil.'
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

        Validator::extend('permission', function($attribute, $id, $parameters) use ($user) {
            $ticket = Ticket::where('user_id', $user->id)->whereNull('ticket_id')->where('status', 'open')->where('id', $id)->first();

            return @$ticket ? true : false;
        });

        return [
            'ticket_id' => 'required|integer|permission',
            'message' => 'required|string|min:10,max:500'
        ];
    }
}
