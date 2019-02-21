<?php

namespace App\Http\Requests\RealTime\KeywordGroup;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

class UpdateRequest extends FormRequest
{
    private $max_line;

    public function __construct()
    {
        $this->max_line = auth()->user()->organisation->capacity * 2;
    }

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
            'keyword_max_line' => 'Her grup için en fazla '.$this->max_line.' satır girebilirsiniz.',
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
        Validator::extend('keyword_max_line', function($attribute, $value) {
            return count(explode(PHP_EOL, $value)) <= $this->max_line ? true : false;
        });

        Validator::extend('empty_lines', function($attribute, $value) {
            $return = true;

            foreach (explode(PHP_EOL, $value) as $line)
            {
                if (strlen($line) < 3)
                {
                    $return = false;
                }

                $return = !in_array(str_slug($line, ' '), config('services.twitter.unaccepted_keywords'));
            }

            return $return;
        });

        $arr = [
            'id' => 'required|integer|exists:real_time_keyword_groups,id',

            'name' => 'required|string|max:10',
            'keywords' => 'bail|nullable|required_with:module_twitter|string|max:64|keyword_max_line|empty_lines'
        ];

        foreach (config('system.modules') as $key => $module)
        {
            $arr[implode('_', [ 'module', $key ])] = 'sometimes|boolean';
        }

        return $arr;
    }
}
