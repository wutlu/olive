<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;
use App\Models\Ticket;

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
            if ($user->root())
            {
                return true;
            }
            else
            {
                $ticket = Ticket::where([
                    'id' => $id,
                    'user_id' => $user->id,
                    'status' => 'open'
                ])->whereNull('ticket_id')->first();

                return @$ticket ? true : false;
            }
        });

        return [
            'ticket_id' => 'required|integer|exists:tickets,id|permission',
            'message'   => 'required|string|min:10,max:500'
        ];
    }
}
