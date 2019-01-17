<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

use Validator;
use App\Models\Forum\Message;
use App\Models\Forum\Category;

class ThreadRequest extends FormRequest
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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'lock' => 'İlgili konu kapandığından, güncelleme yapamazsınız!',
            'lock_category' => 'Bu kategori için konu açma yetkiniz bulunmuyor.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('lock', function($attribute, $id) {
            return Message::where('id', $id)->whereNull('message_id')->value('closed') ? false : true;
        });

        Validator::extend('lock_category', function($attribute, $id) {
            $category = Category::where('id', $id)->first();

            if ($category->lock)
            {
                return auth()->user()->root();
            }
            else
            {
                return true;
            }
        });

        return [
            'id' => 'sometimes|required|integer|exists:forum_messages,id|lock',
            'subject' => 'required|string|max:64',
            'body' => 'required|string|max:5000|min:24',
            'category_id' => 'bail|required|integer|exists:forum_categories,id|lock_category',
            'question' => 'nullable|string|in:on'
        ];
    }
}
