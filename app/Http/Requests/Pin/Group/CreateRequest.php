<?php

namespace App\Http\Requests\Pin\Group;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\Pin\Group as PinGroup;

class CreateRequest extends FormRequest
{
    private $max_item;

    public function __construct()
    {
        $this->max_item = auth()->user()->organisation->capacity * 2;
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
            'max_item' => 'En fazla '.$this->max_item.' grup oluşturabilirsiniz.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();

        Validator::extend('max_item', function($attribute) use ($user) {
            return PinGroup::where('organisation_id', $user->organisation_id)->count() <= $this->max_item;
        });

        return [
            'name' => 'required|string|max:32|max_item'
        ];
    }
}
