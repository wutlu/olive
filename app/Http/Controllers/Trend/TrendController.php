<?php

namespace App\Http\Controllers\Trend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Elasticsearch\Document;

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
        $this->middleware([ 'auth', 'organisation:have', 'can:organisation-status' ]);
    }

    /**
     * Trend Analizi Ana Sayfa
     *
     * @return view
     */
    public function live()
    {
        return view('trends.live');
    }

    /**
     * Trend Analizi Redis Haber
     *
     * @return array
     */
    public function liveRedis(string $module)
    {
        $alias = str_slug(config('app.name'));

        return [
            'status' => 'ok',
            'data' => json_decode(RedisCache::get(implode(':', [ $alias, 'trends', $module ])))
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
}
