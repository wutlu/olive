<?php

namespace App\Http\Requests\RealTime\Group;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\RealTime\Group;

class CreateRequest extends FormRequest
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
            'limit' => 'Grup limitiniz doldu.'
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

        Validator::extend('limit', function($attribute) use ($user) {
            $total_group = Group::where('organisation_id', $user->organisation_id)->count();

            return $total_group < $user->organisation->capacity;
        });

        return [
            'name' => 'required|string|max:16|limit',

            'module_youtube' => 'sometimes|boolean',
            'module_twitter' => 'sometimes|boolean',
            'module_sozluk' => 'sometimes|boolean',
            'module_news' => 'sometimes|boolean',
            'module_shopping' => 'sometimes|boolean',
        ];
    }
}
