<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use Carbon\Carbon;

class ArchiveRequest extends FormRequest
{
    public $historical_days;

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
        $this->historical_days = auth()->user()->organisation->historical_days;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date_limit' => 'Başlangıç tarihi '.$this->historical_days.' günden önce olamaz.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('date_limit', function($attribute, $value) {
            return Carbon::now()->diffInDays($value) <= $this->historical_days;
        });

        return [
            'string' => 'nullable|string|max:255',
            'skip' => 'required|integer',
            'take' => 'required|integer|max:100',
            'start_date' => 'required|date|date_limit',
            'end_date' => 'required|date|after_or_equal:start_date',
            'modules' => 'required|array|min:1',
            'modules.*' => 'required|string|in:'.implode(',',array_keys(config('system.modules'))),
            'sort' => 'nullable|string|in:asc,desc',
            'illegal' => 'nullable|string|in:on',
            'reverse' => 'nullable|string|in:on',
            'gender' => 'nullable|string|in:male,female,unknown',
            'sentiment_pos' => 'required|integer|between:0,9',
            'sentiment_neg' => 'required|integer|between:0,9',
            'sentiment_neu' => 'required|integer|between:0,9',
            'sentiment_hte' => 'required|integer|between:0,9',
            'consumer_que' => 'required|integer|between:0,9',
            'consumer_req' => 'required|integer|between:0,9',
            'consumer_cmp' => 'required|integer|between:0,9',
            'consumer_nws' => 'required|integer|between:0,9',
        ];
    }
}
