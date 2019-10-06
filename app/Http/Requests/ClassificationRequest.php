<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassificationRequest extends FormRequest
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
            'id' => 'required|string|max:255',
            'type' => 'required|string|in:tweet,entry,media,article,document,video,comment,product',
            'index' => 'required|string|max:255',
            'sentiment' => 'required_without:consumer|string|in:pos,neu,neg,hte',
            'consumer' => 'required_without:sentiment|string|in:que,req,nws,cmp'
        ];
    }
}
