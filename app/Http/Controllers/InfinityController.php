<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Trend\TrendRequest;

use Illuminate\Support\Facades\Redis as RedisCache;

class InfinityController extends Controller
{
    public function __construct()
    {

    }

    /**
     * dashboard
     *
     * @return view
     */
    public static function dashboard()
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
            ],
            [
                'title' => 'Instagram, Hashtag',
                'module' => 'instagram_hashtag',
            ]
        ];

        return view('infinity.dashboard', compact('trends'));
    }

    /**
     * Trend Analizi Redis
     *
     * @return array
     */
    public function live(TrendRequest $request)
    {
        $data = json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', $request->module ])));

        return [
            'status' => 'ok',
            'data' => count($data) ? array_slice($data, 3, 4) : null,
            'more' => count($data)
        ];
    }
}
