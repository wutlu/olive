<?php

namespace App\Http\Requests\Crawlers\Media;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Crawlers\MediaCrawler;
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
            'es_index' => 'Çalıştırmadan önce index oluşturmanız gerekiyor!',
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
            return MediaCrawler::where('id', $id)
                               ->where(function ($query) {
                                   $query->orWhere(function ($query) {
                                       $query->where('status', true);
                                   });
                                   $query->orWhere(function ($query) {
                                       $query->where('status', false);
                                       $query->whereNotNull('elasticsearch_index_name');
                                   });
                               })
                               ->exists();
        });

        Validator::extend('test', function($attribute, $id) {
            return MediaCrawler::where([ 'id' => $id, 'test' => true ])->exists();
        });

        return [
            'id' => 'required|integer|exists:media_crawlers|es_index|test'
        ];
    }
}
