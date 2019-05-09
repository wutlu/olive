<?php

namespace App\Http\Requests\Crawlers\Media;

use Illuminate\Foundation\Http\FormRequest;

use Carbon\Carbon;

use Validator;

use App\Models\Crawlers\MediaCrawler;

class DeleteRequest extends FormRequest
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
            'private_exists' => 'Bu botu durdurun ve silmeyi 2 saat sonra tekrar deneyin.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('private_exists', function($attribute, $id) {
            return MediaCrawler::where('id', $id)
                               ->where('status', false)
                               ->where(function ($query) {
                                    $query->orWhere(function ($query) {
                                        $query->where([
                                             'test' => false
                                        ]);
                                    });
                                    $query->orWhere(function ($query) {
                                        $query->where('updated_at', '<=', Carbon::now()->subHours(2)->format('Y-m-d H:i:s'));
                                    });
                                })
                               ->exists();
        });

        return [
            'id' => 'required|integer|private_exists'
        ];
    }
}
