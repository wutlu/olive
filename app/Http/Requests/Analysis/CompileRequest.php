<?php

namespace App\Http\Requests\Analysis;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

class CompileRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'module' => 'required|string|in:'.implode(',', array_keys(config('system.analysis'))),
            'group' => 'nullable|string|in:'.implode(',', array_merge(array_keys(config('system.analysis')[$request->module]['types']), [ @config('system.analysis')[$request->module]['ignore'] ]))
        ];
    }
}
