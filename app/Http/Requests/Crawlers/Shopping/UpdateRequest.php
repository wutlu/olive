<?php

namespace App\Http\Requests\Crawlers\Shopping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Requests\IdRequest;

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
            'id'                     => 'required|integer|exists:shopping_crawlers',
            'name'                   => 'required|string|max:24|unique:shopping_crawlers,name,'.$request->id,
            'site'                   => 'required|string|max:255|active_url',
            'google_search_query'    => 'required|string|max:255',
            'google_max_page'        => 'required|integer|max:10|min:1',
            'url_pattern'            => 'required|string|max:255',
            'selector_title'         => 'required|string|max:255',
            'selector_breadcrumb'    => 'required|string|max:255',
            'selector_address'       => 'required|string|max:255',
            'selector_seller_name'   => 'required|string|max:255',
            'selector_seller_phones' => 'required|string|max:255',
            'selector_price'         => 'required|string|max:255',
            'control_interval'       => 'required|integer|max:60|min:1',
            'off_limit'              => 'required|integer|max:100|min:10',
            'test_count'             => 'required|integer|max:100|min:1'
        ];
    }
}
