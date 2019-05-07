<?php

namespace App\Http\Requests\Analysis;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

use App\Models\Analysis;

use Validator;

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
            'private_unique' => 'Kelime bu modülün bir grubunda mevcut.',
            'adaptive' => 'Sadece küçük harf ve sayı kullanabilirsiniz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:'.implode(',', array_keys(config('system.analysis')))
        ]);

        Validator::extend('private_unique', function($attribute, $word) use ($request) {
            return !Analysis::where('word', $word)->where('module', $request->module)->exists();
        });

        Validator::extend('adaptive', function($attribute, $word) {
            return !preg_match('/[^a-z0-9]/', $word);
        });

        return [
            'string' => 'required|string|min:1|max:48|private_unique|adaptive',
            'group' => 'required|string|in:'.implode(',', array_merge(array_keys(config('system.analysis')[$request->module]['types']), [ @config('system.analysis')[$request->module]['ignore'] ]))
        ];
    }
}
