<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

use Validator;

use App\Models\SavedSearch;

use Carbon\Carbon;

class CompareRequest extends FormRequest
{
    private $historical_days;

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
        $this->historical_days = 31;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'private_exists' => 'Geçersiz bir arama seçtiniz.',
            'date_limit' => 'Başlangıç tarihi en fazla '.$this->historical_days.' gün öncesi olabilir.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $user = auth()->user();

        Validator::extend('private_exists', function($attribute, $id) use($user) {
            return SavedSearch::where('id', $id)->where('organisation_id', $user->organisation_id)->exists();
        });

        Validator::extend('date_limit', function($attribute, $value) {
            return Carbon::now()->diffInDays($value) <= $this->historical_days;
        });

        $request->validate([
            'searches.*' => 'required|string|private_exists'
        ]);

        $selected_values = @implode(',', $request->searches);

        return [
            'start_date' => 'required|date|date_limit',
            'end_date' => 'required_unless:metric,on|date|after_or_equal:start_date',

            'metric' => 'nullable|string|in:on',

            'currency' => 'nullable|string|in:USD,EUR,BTC',

            'normalize_1' => 'nullable|required_with:normalize_2|different:normalize_2|private_exists|in:'.$selected_values,
            'normalize_2' => 'nullable|required_with:normalize_1|different:normalize_1|private_exists|in:'.$selected_values,
        ];
    }
}
