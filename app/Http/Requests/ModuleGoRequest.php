<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

class ModuleGoRequest extends FormRequest
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
            'module_exists' => 'Seçtiğiniz işlem sonlandırılamıyor.',
            'in_route' => 'Rota sistem dışı.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('module_exists', function($attribute, $id) {
            return @config('system.search.modules')[$id] ? true : false;
        });

        Validator::extend('in_route', function($attribute, $route) {
            return url('/') == substr($route, 0, strlen(url('/')));
        });

        return [
            'search_input' => 'nullable|string|max:64',
            'module_id' => 'required|integer|module_exists',
            'route' => 'sometimes|required|url|in_route'
        ];
    }
}
