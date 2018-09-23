<?php

namespace App\Http\Requests\Crawlers\Shopping;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Crawlers\ShoppingCrawler;
use Validator;

class StatusRequest extends FormRequest
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
            'es_index' => 'Index\'in oluşmasını bekleyin.',
            'test' => 'Çalıştırmadan önce test işlemini yapmanız gerekiyor.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('es_index', function($attribute, $id) {
            return ShoppingCrawler::where('id', $id)
                               ->where(function ($query) {
                                   $query->orWhere(function ($query) {
                                       $query->where('status', true);
                                   });
                                   $query->orWhere(function ($query) {
                                       $query->where('status', false);
                                       $query->where('elasticsearch_index', true);
                                   });
                               })
                               ->exists();
        });

        Validator::extend('test', function($attribute, $id) {
            return ShoppingCrawler::where([ 'id' => $id, 'test' => true ])->exists();
        });

        return [
            'id' => 'required|integer|exists:shopping_crawlers|test|es_index'
        ];
    }
}
