<?php

namespace App\Http\Controllers\Trend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\Trend\TrendRequest;

use Illuminate\Support\Facades\Redis as RedisCache;

use App\Models\TrendArchive;
use App\Models\PopTrend;

use App\Elasticsearch\Document;

class TrendController extends Controller
{
    private $modules;

    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have' ]);
        $this->middleware([ 'can:organisation-status' ])->except([ 'live', 'archive' ]);

        $this->middleware('organisation:have,module_trend')->only([
            'liveRedis',
            'archiveView',
            'popular'
        ]);

        $this->modules = [
            'google' => [
                'name' => 'Google',
                'icon' => asset('img/logos/google.svg')
            ],
            'blog' => [
                'name' => 'Blog',
                'icon' => asset('img/logos/blog.svg')
            ],
            'news' => [
                'name' => 'Haber',
                'icon' => asset('img/logos/news.svg')
            ],
            'youtube_video' => [
                'name' => 'YouTube, Video',
                'icon' => asset('img/logos/youtube.svg')
            ],
            'entry' => [
                'name' => 'Sözlük, Entry',
                'icon' => asset('img/logos/sozluk.svg')
            ],
            'instagram_hashtag' => [
                'name' => 'Instagram, Hashtag',
                'icon' => asset('img/logos/instagram.svg')
            ],
            'twitter_hashtag' => [
                'name' => 'Twitter, Hashtag',
                'icon' => asset('img/logos/twitter.svg')
            ],
            'twitter_tweet' => [
                'name' => 'Twitter, Tweet',
                'icon' => asset('img/logos/twitter.svg')
            ],
            'twitter_favorite' => [
                'name' => 'Twitter, Favori',
                'icon' => asset('img/logos/twitter.svg')
            ],
        ];
    }

    /**
     * Trend Analizi, Ana Sayfa
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
                'title' => 'Twitter, Favori',
                'module' => 'twitter_favorite',
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

        return view('trends.live', compact('trends'));
    }

    /**
     * Trend Analizi, Redis
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
     * Trend Analizi, Arşiv Sayfası
     *
     * @return view
     */
    public function archive(Request $request, int $pager = 20)
    {
        $modules = $this->modules;

        $request->validate([
            'day' => 'nullable|integer|max:31',
            'month' => 'nullable|integer|max:12',
            'year' => 'nullable|integer|max:'.date('Y'),
            'type' => 'nullable|string|in:monthly,weekly,daily,hourly',
            'module' => 'nullable|string|in:'.implode(',', array_keys($modules))
        ]);

        $data = new TrendArchive;

        $date_start = [ date('Y'), 1, 1 ];
        $date_end = [ date('Y'), 12, cal_days_in_month(CAL_GREGORIAN, 12, date('Y')) ];

        if ($request->year)
        {
            $date_start[0] = $request->year;
            $date_end[0] = $request->year;

            $date_start[1] = 1;
            $date_end[1] = 12;

            $date_start[2] = 1;
            $date_end[2] = cal_days_in_month(CAL_GREGORIAN, 12, $request->year);
        }

        if ($request->month)
        {
            $date_start[1] = $request->month;
            $date_end[1] = $request->month;

            $date_start[2] = 1;
            $date_end[2] = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year ? $request->year : date('Y'));
        }

        if ($request->day)
        {
            $max_day = cal_days_in_month(CAL_GREGORIAN, $date_start[1], $date_start[0]);
            $day = $request->day > $max_day ? $max_day : $request->day;

            $date_start[2] = $day;
            $date_end[2] = $day;
        }

        if ($request->module)
        {
            $data = $data->where('module', $request->module);
        }

        if ($request->type)
        {
            switch ($request->type)
            {
                case 'monthly':
                    $pattern = '^\d{4}\.\d{1,2}$';
                break;
                case 'weekly':
                    $pattern = '^\d{4}-\d{1,2}$';
                break;
                case 'daily':
                    $pattern = '^\d{4}\.\d{1,2}\.\d{1,2}$';
                break;
                case 'hourly':
                    $pattern = '^\d{4}\.\d{1,2}\.\d{1,2}-\d{1,2}$';
                break;
            }

            $data = $data->where('group', '~*', $pattern);
        }

        $data = $data->whereBetween('created_at', [ date(implode('-', $date_start)).' 00:00:00', date(implode('-', $date_end)).' 23:59:59' ]);

        $data = $data->orderBy('created_at', 'DESC')->paginate($pager);

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('trend.archive');
        }

        return view('trends.archive', compact('data', 'request', 'pager', 'modules'));
    }

    /**
     * Trend Analizi, Arşiv Görüntüleme Sayfası
     *
     * @return view
     */
    public function archiveView(int $id)
    {
        $query = TrendArchive::where('id', $id)->firstOrFail();
        $query->disabled_mutator = true;

        $module = $this->modules[$query->module];

        $documents = Document::search([ 'trend', 'titles' ], 'title', [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match' => [ 'group' => $query->group ] ],
                        [ 'match' => [ 'module' => $query->module ] ]
                    ]
                ]
            ],
            'sort' => [
                'hit' => 'desc'
            ],
            'size' => 100
        ]);

        return view('trends.archiveView', compact('query', 'module', 'documents'));
    }

    /**
     * Trend Analizi, Popüler Trendler
     *
     * @return view
     */
    public function popular(Request $request, int $pager = 100)
    {
        $modules = $this->modules;
        $modules['twitter_tweet']['name'] = 'Twitter, Kullanıcı';
        $modules['twitter_favorite']['name'] = 'Twitter, Kullanıcı (Fav)';
        $modules['youtube_video']['name'] = 'YouTube, Kullanıcı';
        $modules['entry']['name'] = 'Sözlük, Başlık';

        unset($modules['blog']);
        unset($modules['news']);

        $request->validate([
            'module' => 'nullable|string|in:'.implode(',', array_keys($modules)),
            'sort' => 'nullable|string|in:trend_hit,exp_trend_hit',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2019|max:'.date('Y'),
            'category' => 'nullable|string|max:155'
        ]);

        $data = new PopTrend;

        if ($request->module)
        {
            $data = $data->where('module', $request->module);
        }

        if ($request->category)
        {
            $data = $data->where('category', $request->category);
        }

        $year = $request->year ? $request->year : date('Y');
        $month = $request->month ? $request->month : date('m');

        $data = $data->where('month_key', $year.$month);
        $data = $data->where($request->sort ? $request->sort : 'trend_hit', '>=', 2);

        $data = $data->orderBy($request->sort ? $request->sort : 'trend_hit', 'DESC')->limit(10000)->paginate($pager);

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('trend.popular');
        }

        return view('trends.popular', compact('data', 'request', 'pager', 'modules'));
    }
}
