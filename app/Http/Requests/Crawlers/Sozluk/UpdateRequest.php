<?php

namespace App\Http\Requests\Crawlers\Sozluk;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\IdRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(IdRequest $request)
    {
        return [
            'id'                   => 'required|integer|exists:sozluk_crawlers',
            'name'                 => 'required|string|max:24|unique:sozluk_crawlers,name,'.$request->id,
            'site'                 => 'required|string|max:255|active_url',
            'url_pattern'          => 'required|string|max:255',
            'selector_title'       => 'required|string|max:255',
            'selector_entry'       => 'required|string|max:255',
            'selector_author'      => 'required|string|max:255',
            'last_id'              => 'required|integer|min:0',
            'max_attempt'          => 'required|integer|max:1000|min:1',
            'deep_try'             => 'required|integer|max:100|min:1',
            'test_count'           => 'required|integer|max:100|min:1',
            'proxy'                => 'nullable|string|in:on',
            'cookie'               => 'nullable|json',
        ];
    }
}
