<?php

namespace App\Http\Requests\RealTime;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\RealTime\KeywordGroup;
use App\Models\Pin\Group as PinGroup;

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
            'keyword_group_owner' => 'Geçersiz bir grup seçtiniz. Lütfen sayfayı yenileyin.'
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

        Validator::extend('keyword_group_owner', function($attribute, $id) use ($user) {
            return KeywordGroup::where([
                'id' => $id,
                'organisation_id' => $user->organisation_id
            ])->exists();
        });

        return [
            'keyword_group' => 'required|array',
            'keyword_group.*' => 'required|integer|keyword_group_owner',
            'sentiment' => 'required|string|in:pos,neu,neg,all'
        ];
    }
}
