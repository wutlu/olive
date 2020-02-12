<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use Carbon\Carbon;

use App\Http\Requests\StartEndDateRequest;

class ArchiveAggregationRequest extends FormRequest
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
            'date_limit' => 'Başlangıç tarihi '.$this->historical_days.' günden önce olamaz.',
            'date_limit_between' => 'Arama raporları için tarih aralığı 30 günden geniş olamaz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(StartEndDateRequest $request)
    {
        Validator::extend('date_limit', function($attribute, $value) {
            return Carbon::now()->diffInDays($value) <= $this->historical_days;
        });

        Validator::extend('date_limit_between', function($attribute, $value) use($request) {
            return Carbon::parse($request->start_date)->diffInDays($request->end_date) <= 30;
        });

        return [
            'type' => 'required|string|in:place,histogram,platform,author,hashtag,sentiment,consumer,gender,category,local_press',
            'string' => 'required|string|max:500',
            'start_date' => 'required|date|date_limit',
            'end_date' => 'required|date|after_or_equal:start_date|date_limit_between',
            'modules' => 'required|array|min:1',
            'modules.*' => 'required|string|in:'.implode(',',array_keys(config('system.modules'))),
            'category' => 'nullable|string|in:'.implode(',', array_keys(config('system.analysis.category.types'))),
            'state' => 'nullable|string|max:64',
            'sharp' => 'nullable|string|in:on',
            'gender' => 'required|string|in:all,male,female,unknown',
            'sentiment_pos' => 'nullable|integer|between:0,9',
            'sentiment_neg' => 'nullable|integer|between:0,9',
            'sentiment_neu' => 'nullable|integer|between:0,9',
            'sentiment_hte' => 'nullable|integer|between:0,9',
            'consumer_que' => 'nullable|integer|between:0,9',
            'consumer_req' => 'nullable|integer|between:0,9',
            'consumer_cmp' => 'nullable|integer|between:0,9',
            'consumer_nws' => 'nullable|integer|between:0,9',
        ];
    }
}
