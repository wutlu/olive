<?php

namespace App\Http\Requests\Pin;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Pin\Group as PinGroup;

use Validator;

class PinRequest extends FormRequest
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
            'group_have' => 'Seçtiğiniz arşiv artık yok.'
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

        Validator::extend('group_have', function($attribute, $id) use ($user) {
            return PinGroup::where([
                'id' => $id,
                'organisation_id' => $user->organisation_id
            ])->exists();
        });

        return [
            'id' => 'required|string|max:128',
            'type' => 'required|string|max:64',
            'index' => 'required|string|max:128',

            'archive_id' => 'required|integer|group_have'
        ];
    }
}
