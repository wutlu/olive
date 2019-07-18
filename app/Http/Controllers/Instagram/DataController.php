<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Instagram\CreateUrlRequest;

use App\Http\Controllers\Controller;

use App\Models\Crawlers\Instagram\Selves;

class DataController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         * -- source
         */
        $this->middleware([ 'auth', 'organisation:have' ]);

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Onayı
         */
        $this->middleware([ 'can:organisation-status' ])->only([
        ]);
    }

    /**
     * Instagram veri havuzu, takip edilen url listesi.
     *
     * @return view
     */
    public function urlList()
    {
        return view('instagram.dataPool.url_list');
    }

    /**
     * Instagram veri havuzu, takip edilen url listesi.
     *
     * @return array
     */
    public function urlListJson(SearchRequest $request)
    {
        $query = new Selves;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);

        $query = $request->string ? $query->where('url', 'ILIKE', '%'.$request->string.'%') : $query;

        $total = $query->count();

        $query = $query->skip($request->skip)
                       ->take($request->take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }

    /**
     * Instagram veri havuzu, url oluşturma.
     *
     * @return array
     */
    public function urlCreate(CreateUrlRequest $request)
    {
    	$method = session('method');

    	$url = str_replace([
    		'https://www.instagram.com/',
    		'http://www.instagram.com/',
    		'https://instagram.com/',
    		'http://instagram.com/'
    	], '', $request->string);

    	$url = 'https://www.instagram.com/'.$url;

        $query = new Selves;
        $query->url = $url;
        $query->method = $method;
        $query->status = true;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Instagram veri havuzu, url silme.
     *
     * @return array
     */
    public static function urlDelete(IdRequest $request)
    {
        $query = Selves::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $query->id
            ]
        ];

        $query->delete();

        return $arr;
    }
}
