<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

use Validator;

class PageRequest extends FormRequest
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
            'lines' => 'Satırlar boş kalamaz.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        Validator::extend('lines', function($attribute, $string) {
            $ok = false;

            foreach (json_decode($string) as $item)
            {
                if (@$item->text && @$item->position->top !== null && @$item->position->left !== null)
                {
                    $ok = true;
                }
            }

            return $ok;
        });

        return [
            'title' => 'required|string|min:4|max:64',
            'subtitle' => 'nullable|string|min:10|max:128',
            'text' => 'nullable|string|max:2000',
            'lines' => 'required_with:text|json|lines'
        ];
    }
}
