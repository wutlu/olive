<?php

namespace App\Http\Requests\AnalysisTools;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;

use Validator;

use App\Models\AnalysisTool;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;

use App\Elasticsearch\Document;

class CreateRequest extends FormRequest
{
    private $max_item;

    public function __construct()
    {
        $this->max_item = auth()->user()->organisation->analysis_tools_limit;
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
            'max_item' => 'En fazla '.$this->max_item.' adet analiz oluşturabilirsiniz.',
            'private_unique' => 'Bu hesabı zaten takip ediyorsunuz.',
            'private_source' => 'İçerik kaynağına ulaşılamadı. Lütfen tekrar deneyin.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'id' => 'required|string|max:128',
            'type' => 'required|string|in:tweet,media,video,comment',
            'index' => 'required|string|max:128'
        ]);

        Validator::extend('max_item', function($attribute) use($user) {
            return AnalysisTool::where('organisation_id', $user->organisation_id)->count() < $this->max_item;
        });

        Validator::extend('private_source', function($attribute) use($request) {
            $document = Document::get($request->index, $request->type, $request->id);

            session()->flash('document', $document);

            return $document->status == 'ok';
        });

        Validator::extend('private_unique', function($attribute) use($user, $request) {
            $document = session('document');

            switch ($document->data['_type'])
            {
                case 'tweet':
                    $platform = 'twitter';
                    $social_id = $document->data['_source']['user']['id'];
                break;
                case 'video':
                case 'comment':
                    $platform = 'youtube';
                    $social_id = $document->data['_source']['channel']['id'];
                break;
                case 'media':
                    $platform = 'instagram';
                    $social_id = $document->data['_source']['user']['id'];
                break;
            }

            session()->flash('document', $document);
            session()->flash('social_id', $social_id);
            session()->flash('platform', $platform);

            return !AnalysisTool::where([
                'social_id' => $social_id,
                'platform' => $platform,
                'organisation_id' => $user->organisation_id
            ])->exists();
        });

        return [
            'id' => 'bail|max_item|private_source|private_unique'
        ];
    }
}
