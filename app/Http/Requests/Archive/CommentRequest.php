<?php

namespace App\Http\Requests\Archive;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'archive_id' => 'required|integer|exists:archives,id',
            'id' => 'required|regex:/(^([a-zA-Z0-9-_.]+)?$)/u|max:128',
            'type' => 'required|string|max:64',
            'index' => 'required|string|max:128',
            'comment' => 'required|string|max:255'
        ];
    }
}
