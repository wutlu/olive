<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

use Carbon\Carbon;

class SaveRequest extends FormRequest
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
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        return [
            'id' => 'sometimes|required|integer|exists:newsletters,id',
            'send_date' => 'required|date_format:Y-m-d|after:'.$yesterday,
            'send_time' => 'required|date_format:H:i',
            'subject' => 'required|string|max:64',
            'body' => 'required|string|max:10000',
            'email_list' => 'nullable|string|max:10000',
            'status' => 'nullable|string|in:on'
        ];
    }
}
