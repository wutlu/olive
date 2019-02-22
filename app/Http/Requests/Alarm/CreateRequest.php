<?php

namespace App\Http\Requests\Alarm;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\User\User;

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
            'email_validation' => 'E-posta adreslerinin organizasyonunuzda olması gerekir.',
            'or_params' => 'En fazla 4 adet OR (||) parametresi kullanabilirsiniz.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('email_validation', function($key, $emails) {
            $user = auth()->user();

            $return = true;

            $emails = explode(PHP_EOL, $emails);

            foreach($emails as $email)
            {
                $has_org = User::where('organisation_id', $user->organisation_id)->where('email', $email)->exists();

                if (!$has_org)
                {
                    $return = false;
                }
            }

            return $return;
        });

        Validator::extend('or_params', function($key, $query) {
            $or = substr_count($query, ' OR ');
            $pipe = substr_count($query, '||');

            $total = $or + $pipe;

            return $total <= 4;
        });

        $arr = [
            'name' => 'required|string|max:100',
            'text' => 'required|string|max:255|or_params',

            'hit' => 'required|integer|min:1|max:120',

            'day_1' => 'nullable|string|in:on',
            'day_2' => 'nullable|string|in:on',
            'day_3' => 'nullable|string|in:on',
            'day_4' => 'nullable|string|in:on',
            'day_5' => 'nullable|string|in:on',
            'day_6' => 'nullable|string|in:on',
            'day_7' => 'nullable|string|in:on',

            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

            'interval' => 'required|integer|min:1|max:120',

            'emails' => 'required|bail|string|email_validation'
        ];

        foreach (config('system.modules') as $key => $module)
        {
            $arr[implode('_', [ 'module', $key ])] = 'sometimes|boolean';
        }

        return $arr;
    }
}
