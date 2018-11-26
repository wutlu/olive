<?php

namespace App\Http\Requests\Twitter\Stream;

use Illuminate\Foundation\Http\FormRequest;

class AccountReasonRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:twitter_streaming_users',
            'reason' => 'nullable|string|max:255'
        ];
    }
}
