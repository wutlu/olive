<?php

namespace App\Http\Requests\Crawlers\Media;

use Illuminate\Foundation\Http\FormRequest;
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'id'                   => 'required|integer|exists:media_crawlers',
            'name'                 => 'required|string|max:24|unique:media_crawlers,name,'.$request->id,
            'site'                 => 'required|string|max:255|active_url',
            'base'                 => 'required|string|max:255',
            'url_pattern'          => 'required|string|max:255',
            'selector_title'       => 'required|string|max:255',
            'selector_description' => 'required|string|max:255',
            'control_interval'     => 'required|integer|max:60|min:1',
            'off_limit'            => 'required|integer|max:100|min:10',
            'test_count'           => 'required|integer|max:100|min:1'
        ];
    }
}
