<?php

namespace App\Http\Requests\Archive\Group;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Archive\Archive;

class CreateRequest extends FormRequest
{
    private $max_item;

    public function __construct()
    {
        $this->max_item = auth()->user()->organisation->archive_limit;
    }

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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'max_item' => 'En fazla '.$this->max_item.' adet arşiv oluşturabilirsiniz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('max_item', function($attribute) {
            return Archive::where('organisation_id', auth()->user()->organisation_id)->count() < $this->max_item;
        });

        return [
            'name' => 'required|string|max:32|max_item'
        ];
    }
}
