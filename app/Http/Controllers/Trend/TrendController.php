<?php

namespace App\Http\Controllers\Trend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\Trend\TrendRequest;

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
            'liveRedis'
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
                'title' => 'Twitter, Tweet',
                'module' => 'twitter_tweet',
            ],
            [
                'title' => 'Twitter, Hashtag',
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
            ],
            [
                'title' => 'Blog, Makale',
                'module' => 'blog',
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
}
