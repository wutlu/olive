<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SourceRequest extends FormRequest
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
            'name' => 'required|string|max:24',

            'sources_media'         => 'nullable|array|max:400',
            'sources_media.*'       => 'nullable|string|exists:media_crawlers,id',

            'sources_sozluk'        => 'nullable|array|max:400',
            'sources_sozluk.*'      => 'nullable|string|exists:sozluk_crawlers,id',

            'sources_blog'          => 'nullable|array|max:400',
            'sources_blog.*'        => 'nullable|string|exists:blog_crawlers,id',

            /*
            'sources_forum'         => 'nullable|array',
            'sources_forum.*'       => 'nullable|string|exists:forum_crawlers,id',
            */

            'sources_shopping'      => 'nullable|array|max:400',
            'sources_shopping.*'    => 'nullable|string|exists:shopping_crawlers,id',
        ];
    }
}
