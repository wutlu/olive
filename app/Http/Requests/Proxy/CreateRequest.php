<?php

namespace App\Http\Requests\Proxy;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'proxy' => 'required|string|max:255|unique:proxies,proxy',
            'min_health' => 'required|integer|min:1|max:10'
        ];
    }
}
