<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

class ShellRequest extends FormRequest
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
            'pid_have' => 'Seçtiğiniz işlem sonlandırılamıyor.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('pid_have', function($attribute, $pid) {
            $status = false;

            if (is_array(session('pids')))
            {
                if (array_key_exists($pid, session('pids')))
                {
                    $status = true;
                }
            }

            return $status;
        });

        return [
            'pid' => 'required|integer|pid_have'
        ];
    }
}
