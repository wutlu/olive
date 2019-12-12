<?php

namespace App\Http\Requests\Alarm;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

use App\Models\User\User;
use App\Models\Alarm;
use App\Models\SavedSearch;

use Illuminate\Http\Request;

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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email_validation' => 'E-posta adreslerinin organizasyonunuzda olması gerekir.',
            'private_exists' => 'Seçtiğiniz arama silinmiş olabilir. Lütfen sayfayı yenileyin ve tekrar deneyin!',
            'private_unique' => 'Seçtiğiniz arama için farklı bir alarm mevcut. Lütfen farklı bir arama seçin!',
            'min_interval_by_report' => 'Detaylı rapor seçiliyken aralık periyodu en az 60 dakika olabilir.'
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
            'id' => 'required|integer|exists:alarms,id'
        ]);

        $user = auth()->user();

        Validator::extend('email_validation', function($key, $id) use ($user) {
            return User::where('organisation_id', $user->organisation_id)->where('id', $id)->exists();
        });

        Validator::extend('private_exists', function($key, $id) use ($user) {
            return SavedSearch::where([ 'id' => $id, 'organisation_id' => $user->organisation_id ])->exists();
        });

        Validator::extend('private_unique', function($key, $id) use ($user, $request) {
            return !Alarm::where([
                'search_id' => $id,
                'organisation_id' => $user->organisation_id
            ])->where('id', '<>', $request->id)->exists();
        });

        Validator::extend('min_interval_by_report', function($key, $interval) use ($user) {
            return $interval >= 60 ? true : false;
        });

        $arr = [
            'search_id' => 'required|integer|private_exists|private_unique',

            'hit' => 'required|integer|min:1|max:120',

            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

            'interval' => 'required|integer|min:1|max:720',

            'report' => 'nullable|string|in:on',

            'weekdays' => 'required|array|min:1',
            'weekdays.*' => 'required|string|in:day_1,day_2,day_3,day_4,day_5,day_6,day_7',

            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|email_validation',
        ];

        if ($request->report)
        {
            $arr['interval'] = 'required|integer|min:1|max:720|min_interval_by_report';
        }

        return $arr;
    }
}
