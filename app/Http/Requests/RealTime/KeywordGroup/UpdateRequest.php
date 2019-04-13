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
            'empty_lines' => 'Her kelime satırı en az 3 karakter olabilir. (bir, ile... vb. kaçamak kelimeler kullanamazsınız!)'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('empty_lines', function($attribute, $value) {
            $return = true;

            foreach (explode(PHP_EOL, $value) as $line)
            {
                if (strlen($line) < 3)
                {
                    $return = false;
                }
                else
                {
                    $return = !in_array(str_slug($line, ' '), config('services.twitter.unaccepted_keywords'));
                }
            }

            return $return;
        });

        return [
            'id' => 'required|integer|exists:real_time_keyword_groups,id',
            'name' => 'required|string|max:10',
            'keywords' => 'bail|nullable|string|max:255|empty_lines',
            'modules' => 'nullable|array',
            'modules.*' => 'required|string|in:'.implode(',', array_keys(config('system.modules')))
        ];
    }
}
