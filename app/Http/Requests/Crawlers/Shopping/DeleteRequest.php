<?php

namespace App\Http\Requests\Crawlers\Shopping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

use Carbon\Carbon;

use Validator;

use App\Models\Crawlers\ShoppingCrawler;

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
            'private_exists' => 'Bu botu durdurun ve silmeyi 6 saat sonra tekrar deneyin.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        Validator::extend('private_exists', function($attribute, $id) {
            return ShoppingCrawler::where('id', $id)
                               ->where('status', false)
                               ->where(function ($query) {
                                    $query->orWhere(function ($query) {
                                        $query->where([
                                             'elasticsearch_index' => false,
                                             'test' => false
                                        ]);
                                    });
                                    $query->orWhere(function ($query) {
                                        $query->where('elasticsearch_index', true);
                                        $query->where('updated_at', '<=', Carbon::now()->subHours(6)->format('Y-m-d H:i:s'));
                                    });
                                })
                               ->exists();
        });

        return [
            'id' => 'required|integer|private_exists'
        ];
    }
}
