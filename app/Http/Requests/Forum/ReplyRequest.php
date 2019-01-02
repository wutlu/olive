<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

use Validator;
use App\Models\Forum\Message;

class ReplyRequest extends FormRequest
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
            'lock' => 'İlgili konu kapandığından, cevap ekleyemezsiniz.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('lock', function($attribute, $id) {
            $reply = Message::where('id', $id)->first();
            $reply = $reply->message_id ? $reply->thread : $reply;

            return $reply->closed ? false : true;
        });

        return [
            'reply_id' => 'bail|required|integer|exists:forum_messages,id|lock',
            'body' => 'required|string|max:5000|min:10'
        ];
    }
}
