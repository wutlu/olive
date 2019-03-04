<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveAggregationRequest extends FormRequest
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
            'type' => 'required|string|in:hourly,daily,location,platform,source,mention,hashtag',
            'string' => 'required|string|max:255',
            'start_date' => 'required|date_format:d.m.Y',
            'end_date' => 'required|date_format:d.m.Y|after_or_equal:start_date',
            'sentiment' => 'required|string|in:pos,neu,neg,all'
        ];
    }
}
