<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BorsaRequest extends FormRequest
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
            'sk' => 'required|string|in:name,hour,value,buy,sell,diff,max,min,lot,tl,total_pos,total_neg,pos-neg',
            'sv' => 'required|string|in:asc,desc',
            'group' => 'required|string|in:xu030-bist-30,xu100-bist-100'
        ];
    }
}
