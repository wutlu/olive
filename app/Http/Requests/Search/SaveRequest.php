<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\SavedSearch;

use Validator;

class SaveRequest extends FormRequest
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

    public function __construct()
    {
        //
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'limit' => 'Arama kaydetme üst limitine ulaştınız. Mevcut aramalardan silin ve tekrar deneyin.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('limit', function($attribute) {
            $user = auth()->user();

            $count = SavedSearch::where('organisation_id', $user->organisation_id)->count();

            return $count < $user->organisation->saved_searches_limit;
        });

        return [
            'name' => 'required|string|max:16|limit',
            'string' => 'required|string|max:500|min:2',
            'modules' => 'required|array|min:1',
            'modules.*' => 'required|string|in:'.implode(',', array_keys(config('system.modules'))),
            'reverse' => 'nullable|string|in:on',
            'take' => 'required|integer|min:10|max:100',
            'gender' => 'required|string|in:all,male,female,unknown',
            'sentiment_pos' => 'required|integer|between:0,9',
            'sentiment_neu' => 'required|integer|between:0,9',
            'sentiment_neg' => 'required|integer|between:0,9',
            'sentiment_hte' => 'required|integer|between:0,9',
            'consumer_que' => 'required|integer|between:0,9',
            'consumer_req' => 'required|integer|between:0,9',
            'consumer_cmp' => 'required|integer|between:0,9',
            'consumer_nws' => 'required|integer|between:0,9'
        ];
    }
}
