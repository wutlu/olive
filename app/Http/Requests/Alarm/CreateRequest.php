<?php

namespace App\Http\Requests\Alarm;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\User\User;
use App\Models\Alarm;

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
            'or_params' => 'En fazla 4 adet OR (||) parametresi kullanabilirsiniz.',
            'limit' => 'Alarm limitiniz doldu.'
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
            $total_alarm = Alarm::where('organisation_id', $user->organisation_id)->count();

            return $total_alarm < $user->organisation->alarm_limit;
        });

        Validator::extend('email_validation', function($key, $id) use ($user) {
            return User::where('organisation_id', $user->organisation_id)->where('id', $id)->exists();
        });

        Validator::extend('or_params', function($key, $query) {
            $or = substr_count($query, ' OR ');
            $pipe = substr_count($query, '||');

            $total = $or + $pipe;

            return $total <= 4;
        });

        return [
            'name' => 'required|string|max:100|limit',
            'text' => 'required|string|max:255|or_params',

            'hit' => 'required|integer|min:1|max:120',

            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

            'interval' => 'required|integer|min:1|max:120',

            'weekdays' => 'required|array|min:1',
            'weekdays.*' => 'required|string|in:day_1,day_2,day_3,day_4,day_5,day_6,day_7',

            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|email_validation',

            'sources' => 'required|array|min:1',
            'sources.*' => 'required|string|in:'.implode(',', array_keys(config('system.modules')))
        ];
    }
}
