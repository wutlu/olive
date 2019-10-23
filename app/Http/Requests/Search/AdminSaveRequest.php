<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

use App\Http\Requests\Search\SaveRequest;

class AdminSaveRequest extends FormRequest
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

    public function __construct()
    {
        //
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $save_request = new SaveRequest;

        $rules = $save_request->rules();
        $rules['name'] = 'required|string|max:16';

        return $rules;
    }
}
