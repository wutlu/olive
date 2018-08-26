<?php

namespace App\Http\Requests\Keyword;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Validator;

class UpdateRequest extends FormRequest
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
            'private_unique_id' => 'Bu kayda artık ulaşılamıyor.',
            'private_unique' => 'Bu kelime havuzunuzda zaten mevcut.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        Validator::extend('private_unique_id', function($attribute, $id) {
            return auth()->user()->organisation->keywords()->where('id', $id)->exists();
        });

        Validator::extend('private_unique', function($attribute, $keyword) use ($request) {
            return auth()->user()->organisation->keywords()->where('keyword', $keyword)->where('id', '<>', $request->id)->doesntExist();
        });

        return [
            'id'      => 'required|integer|private_unique_id',
            'keyword' => 'required|string|max:32|private_unique'
        ];
    }
}
