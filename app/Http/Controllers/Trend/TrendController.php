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
        $this->middleware([ 'auth', 'organisation:have', 'can:organisation-status' ]);

        ### [ 10 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:10,1')->only('archiveSave');
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
    public function liveRedis(TrendRequest $request)
    {
        $alias = config('system.db.alias');

        return [
            'status' => 'ok',
            'data' => json_decode(RedisCache::get(implode(':', [ $alias, 'trends', $request->module ])))
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

    /**
     * Trend Arşiv Kayıt
     *
     * @return array
     */
    public function archiveSave(SaveRequest $request)
    {
        $user = auth()->user();

        $alias = config('system.db.alias');

        $redis_data = RedisCache::get(implode(':', [ $alias, 'trends', $request->key ]));

        if ($redis_data)
        {
            $name = config('system.trends')[implode('.', [ 'trend', 'status', $request->key ])];

            TrendArchive::updateOrCreate(
                [
                    'group' => implode(':', [ 'olive', 'trends', $request->key, date('Y:m:d:H.i') ]),
                    'organisation_id' => $user->organisation_id
                ],
                [
                    'title' => 'Anlık Trend, '.$name.': '.date('Y.m.d H:i'),
                    'data' => json_decode($redis_data)
                ]
            );

            return [
                'status' => 'ok'
            ];
        }
        else
        {
            return [
                'status' => 'err'
            ];
        }
    }
}
