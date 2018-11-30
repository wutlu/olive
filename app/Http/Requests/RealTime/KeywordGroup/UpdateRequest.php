<?php

namespace App\Http\Requests\RealTime\KeywordGroup;

use Illuminate\Foundation\Http\FormRequest;

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
            'keyword_max_line' => 'Kelime satırı çok fazla.',
            'empty_lines' => 'Her kelime satırı en az 3 karakter olabilir.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('keyword_max_line', function($attribute, $value) {
            return count(explode(PHP_EOL, $value)) <= 10;
        });

        Validator::extend('empty_lines', function($attribute, $value) {
            $return = true;

            foreach (explode(PHP_EOL, $value) as $line)
            {
                if (strlen($line) < 3)
                {
                    $return = false;
                }
            }

            return $return;
        });

        return [
            'id' => 'required|integer|exists:real_time_keyword_groups,id',

            'name' => 'required|string|max:10',
            'keywords' => 'bail|required|string|max:64|keyword_max_line|empty_lines',

            'module_youtube' => 'sometimes|boolean',
            'module_twitter' => 'sometimes|boolean',
            'module_sozluk' => 'sometimes|boolean',
            'module_news' => 'sometimes|boolean',
            'module_shopping' => 'sometimes|boolean',
        ];
    }
}
