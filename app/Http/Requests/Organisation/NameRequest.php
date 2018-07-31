<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class NameRequest extends FormRequest
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
            'owner' => 'Sadece organizasyon sahibi organizasyon adını güncelleyebilir.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('owner', function() {
            $user = auth()->user();

            return $user->id == $user->organisation->user_id ? true : false;
        });

        return [
            'organisation_name' => 'required|string|max:16|owner',
        ];
    }
}
