<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Validator;
use App\User;

class LeaveRequest extends FormRequest
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
            'owner' => 'Organizasyon sahibiyken organizasyondan ayrılamazsınız!'
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

            return $user->id == $user->organisation->user_id ? false : true;
        });

        return [
            'leave_key' => 'required|in:organizasyondan ayrılmak istiyorum|owner'
        ];
    }
}
