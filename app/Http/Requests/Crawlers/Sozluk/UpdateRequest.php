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
            'id'                   => 'required|integer|exists:media_crawlers',
            'name'                 => 'required|string|max:24|unique:media_crawlers,name,'.$request->id,
            'site'                 => 'required|string|max:255|active_url',
            'url_pattern'          => 'required|string|max:255',
            'selector_title'       => 'required|string|max:255',
            'selector_entry'       => 'required|string|max:255',
            'selector_author'      => 'required|string|max:255',
            'last_id'              => 'required|integer|min:0',
            'max_attempt'          => 'required|integer|max:10000|min:10',
            'off_limit'            => 'required|integer|max:100|min:10',
            'test_count'           => 'required|integer|max:100|min:1'
        ];
    }
}
