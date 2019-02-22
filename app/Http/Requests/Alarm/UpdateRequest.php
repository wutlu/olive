<?php

namespace App\Http\Requests\Alarm;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $arr = new CreateRequest;
        $arr = $arr->rules();

        unset($arr['name']);

        $arr['name'] = 'required|string|max:100';

        $arr = array_merge($arr, [ 'id' => 'required|integer|exists:alarms,id' ]);

        return $arr;
    }
}
