<?php

namespace App\Http\Requests\Analysis;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

class TestRequest extends FormRequest
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
            'engine' => 'required|string|in:'.implode(',', array_keys(config('system.analysis'))),
            'testarea' => 'required|string|max:10000'
        ];
    }
}
