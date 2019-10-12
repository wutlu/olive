<?php

namespace App\Http\Requests\Crawlers\Media;

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
            'state'                => 'nullable|string|max:255',
            'name'                 => 'required|string|max:32|unique:media_crawlers,name,'.$request->id,
            'site'                 => 'required|string|max:255|url',
            'base'                 => 'required|string|max:255',
            'url_pattern'          => 'nullable|required_without:standard|string|max:255',
            'selector_title'       => 'nullable|required_without:standard|string|max:255',
            'selector_description' => 'nullable|required_without:standard|string|max:255',
            'control_interval'     => 'required|integer|max:1440|min:1',
            'off_limit'            => 'required|integer|max:255|min:10',
            'test_count'           => 'required|integer|max:100|min:1',
            'proxy'                => 'nullable|string|in:on',
            'standard'             => 'nullable|string|in:on',
        ];
    }
}
