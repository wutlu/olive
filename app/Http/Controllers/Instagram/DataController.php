<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Instagram\CreateUrlRequest;

use App\Http\Controllers\Controller;

use App\Models\Crawlers\Instagram\Selves;
use App\Models\Proxy;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Utilities\DateUtility;

use App\Instagram;

use App\Elasticsearch\Indices;
use App\Jobs\Elasticsearch\BulkInsertJob;

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
     * Instagram, kullanıcı bilgileri güncelleme.
     *
     * @return array
     */
    public function userSync(IdRequest $request)
    {
        $client = new Client([
            'base_uri' => 'https://i.instagram.com',
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents_mobile')[array_rand(config('crawler.user_agents_mobile'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'verify' => false
            ];

            $proxy = Proxy::where('health', '>', 7)->inRandomOrder();

            if ($proxy->exists())
            {
                $arr['proxy'] = $proxy->first()->proxy;
            }

            $client = $client->get('api/v1/users/'.$request->id.'/info', $arr);

            $array = json_decode($client->getBody(), true);

            $dateUtility = new DateUtility;

            $instagram = new Instagram;
            $connect = $instagram->connect('https://www.instagram.com/'.$array['user']['username'].'/');

            if ($connect->status == 'ok')
            {
                $data = $instagram->data('user');

                if ($data->status == 'ok')
                {
                    $bulk = [
                        'body' => []
                    ];

                    $bulk['body'][] = [
                        'create' => [
                            '_index' => Indices::name([ 'instagram', 'users' ]),
                            '_type' => 'user',
                            '_id' => $data->user['id']
                        ]
                    ];
                    $bulk['body'][] = $data->user;

                    if (count($data->data))
                    {
                        foreach ($data->data as $item)
                        {
                            if ($dateUtility->checkDate($item['created_at']))
                            {
                                $bulk['body'][] = [
                                    'create' => [
                                        '_index' => Indices::name([ 'instagram', 'medias', date('Y.m', strtotime($item['created_at'])) ]),
                                        '_type' => 'media',
                                        '_id' => $item['id']
                                    ]
                                ];
                                $bulk['body'][] = $item;
                            }
                        }
                    }

                    if (count($bulk['body']))
                    {
                        BulkInsertJob::dispatch($bulk)->onQueue('elasticsearch');
                    }
                }
                else
                {
                    return [
                        'status' => 'err',
                        'message' => 'Kullanıcı profiline ulaşılamıyor. (1)',
                        'kill' => true
                    ];
                }
            }
            else
            {
                return [
                    'status' => 'err',
                    'message' => 'Bağlantı hatası! Tekrar deneniyor.',
                    'retry' => true
                ];
            }

            return [
                'status' => 'ok',
                'wait' => (intval(config('database.elasticsearch.instagram.user.settings.refresh_interval')))+5
            ];
        }
        catch (\Exception $e)
        {
            return [
                'status' => 'err',
                'message' => 'Kullanıcı profiline ulaşılamıyor. (2)'.$e->getMessage(),
                'kill' => true
            ];
        }
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

        try
        {
            $query = new Selves;
            $query->url = $url;
            $query->method = $method;
            $query->status = true;
            $query->organisation_id = auth()->user()->organisation_id;
            $query->save();
        }
        catch (\Exception $e)
        {
            //
        }

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
