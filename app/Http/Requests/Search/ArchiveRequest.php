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
            'date_limit' => 'Başlangıç tarihi en fazla '.$this->historical_days.' gün öncesi olabilir.'
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
            'string' => 'nullable|string|max:500',
            'skip' => 'required|integer',
            'take' => 'required|integer|max:100',
            'start_date' => 'required|date|date_limit',
            'end_date' => 'required|date|after_or_equal:start_date',
            'modules' => 'required|array|min:1',
            'modules.*' => 'required|string|in:'.implode(',',array_keys(config('system.modules'))),
            'category' => 'nullable|string|in:'.implode(',', array_keys(config('system.analysis.category.types'))),
            'state' => 'nullable|string|max:64',
            'sort' => 'nullable|string|in:asc,desc',
            'reverse' => 'nullable|string|in:on',
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
            'aggs' => 'nullable|string|in:on',
            'twitter_sort' => 'nullable|string|in:counts.favorite,counts.retweet,counts.quote,counts.reply,user.counts.followers,user.counts.friends,user.counts.statuses,user.counts.listed',
            'twitter_sort_operator' => 'required_with:twitter_sort|string|in:asc,desc'
        ];
    }
}
