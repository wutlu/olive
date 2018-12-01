<?php

namespace App\Http\Requests\RealTime;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\RealTime\KeywordGroup;

class RealTimeRequest extends FormRequest
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
            'group_owner' => 'Geçersiz bir grup seçtiniz. Lütfen sayfayı yenileyin.'
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

        Validator::extend('group_owner', function($attribute, $id) use ($user) {
            return KeywordGroup::where([
                'id' => $id,
                'organisation_id' => $user->organisation_id
            ])->exists();
        });

        return [
            'keyword_group' => 'required|array',
            'keyword_group.*' => 'required|integer|group_owner'
        ];
    }
}
