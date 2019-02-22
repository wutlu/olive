<?php

namespace App\Http\Requests\RealTime\KeywordGroup;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\RealTime\KeywordGroup;

class CreateRequest extends FormRequest
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
            'limit' => 'Grup limitiniz doldu.',
            'empty_lines' => 'Her kelime satırı en az 3 karakter olabilir. (bir, ile... vb. kaçamak kelimeler kullanamazsınız!)',
            'except_list' => 'Bu kelimeyi kullanamazsınız.'
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
            $total_group = KeywordGroup::where('organisation_id', $user->organisation_id)->count();

            return $total_group < $user->organisation->capacity;
        });

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

        return [
            'name' => 'required|string|max:10|limit',
            'keywords' => 'bail|nullable|string|max:64|keyword_max_line|empty_lines',
            'modules' => 'nullable|array',
            'modules.*' => 'required|string|in:'.implode(',', array_keys(config('system.modules')))
        ];
    }
}
