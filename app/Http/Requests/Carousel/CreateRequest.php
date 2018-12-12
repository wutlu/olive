<?php

namespace App\Http\Requests\Carousel;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'title' => 'required|string|max:48',
            'description' => 'required|string|max:128',
            'button_action' => 'nullable|url|max:255',
            'button_text' => 'required_with:button_action|nullable|string|max:32',
            'visibility' => 'nullable|boolean',
            'pattern' => 'nullable|string|in:'.implode(',', array_keys(config('app.carousel.patterns'))),
        ];
    }
}
