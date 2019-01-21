<?php

namespace App\Http\Requests\Crawlers\Media;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Crawlers\MediaCrawler;

use Validator;

use App\Elasticsearch\Indices;

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
            'es_index' => 'Bu botun çalışması için eksik indexlerin oluşturulması gerekiyor.',
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
            $crawler = MediaCrawler::where('id', $id)->first();

            if ($crawler->status == true)
            {
                return true;
            }
            else
            {
                if (@$crawler)
                {
                    $index = Indices::stats([ 'media', $crawler->elasticsearch_index_name ]);

                    return $index->status == 'ok' ? true : false;
                }
                else
                {
                    return false;
                }
            }
        });

        Validator::extend('test', function($attribute, $id) {
            return MediaCrawler::where([ 'id' => $id, 'test' => true ])->exists();
        });

        return [
            'id' => 'required|integer|exists:media_crawlers|test|es_index'
        ];
    }
}
