<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplicaRequest extends FormRequest
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
            'string' => 'required|string|min:24|max:255',
            'skip' => 'required|integer',
            'take' => 'required|integer|max:100',
            'smilarity' => 'required|integer|in:100,90,80,70,60,50,40',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}
