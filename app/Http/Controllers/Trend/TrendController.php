<?php

namespace App\Http\Controllers\Trend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\Trend\TrendRequest;
use App\Http\Requests\Trend\SaveRequest;
use App\Http\Requests\SearchRequest;

use App\Elasticsearch\Document;

use App\Models\TrendArchive;

use Carbon\Carbon;

use Illuminate\Support\Facades\Redis as RedisCache;

class TrendController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have' ]);
        $this->middleware([ 'can:organisation-status' ])->except([ 'live' ]);

        $this->middleware('organisation:have,module_trend')->only([
            'liveRedis',
            'archive',
            'archiveListJson'
        ]);
    }

    /**
     * Trend Analizi Ana Sayfa
     *
     * @return view
     */
    public function live()
    {
        $trends = [
            [
                'title' => 'Twitter Tweet',
                'module' => 'twitter_tweet',
            ],
            [
                'title' => 'Twitter Hashtag',
                'module' => 'twitter_hashtag',
            ],
            [
                'title' => 'Medya, Haber',
                'module' => 'news',
            ],
            [
                'title' => 'Sözlük, Entry',
                'module' => 'entry',
            ],
            [
                'title' => 'YouTube, Video',
                'module' => 'youtube_video',
            ],
            [
                'title' => 'Google, Arama',
                'module' => 'google',
            ]
        ];

        return view('trends.live', compact('trends'));
    }

    /**
     * Trend Analizi Redis
     *
     * @return array
     */
    public function liveRedis(TrendRequest $request)
    {
        return [
            'status' => 'ok',
            'data' => json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', $request->module ])))
        ];
    }

    /**
     * Trend Endex Ekranı
     *
     * @return view
     */
    public function index()
    {
        return view('trends.index');
    }

    /**
     * Trend Arşiv Ekranı
     *
     * @return view
     */
    public function archive()
    {
        return view('trends.archive');
    }

    /**
     * Trend Arşiv Sonuçları
     *
     * @return array
     */
    public static function archiveListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new TrendArchive;
        $query = $request->string ? $query->where('title', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->where(function($query) {
            $query->orWhere('organisation_id', auth()->user()->organisation_id);
            $query->orWhereNull('organisation_id');
        });
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }
}
