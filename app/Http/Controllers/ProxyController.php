<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\Proxy\CreateRequest;
use App\Http\Requests\Proxy\UpdateRequest;

use App\Models\Proxy;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class ProxyController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Listesi
     *
     * @return view
     */
    public function proxies()
    {
        return view('proxy');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Listesi
     *
     * @return array
     */
    public static function proxiesJson()
    {
        $proxies = Proxy::orderBy('id', 'DESC')->get();

        return [
            'status' => 'ok',
            'hits' => $proxies,
            'total' => count($proxies)
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Detay
     *
     * @return array
     */
    public static function proxy(IdRequest $request)
    {
        $proxy = Proxy::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $proxy
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Oluştur
     *
     * @return array
     */
    public static function proxyCreate(CreateRequest $request)
    {
        $proxy_health = self::getProxyHealth($request->proxy);

        if ($proxy_health <= 5)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'proxy' => [ 'Proxy çok geç yanıt veriyor.' ]
                    ]
                ],
                422
            );
        }

        $health = $proxy_health;

        $proxy = new Proxy;
        $proxy->fill($request->all());
        $proxy->health = $health;
        $proxy->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Güncelle
     *
     * @return array
     */
    public static function proxyUpdate(UpdateRequest $request)
    {
        $proxy_health = self::getProxyHealth($request->proxy);

        if ($proxy_health <= 5)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'proxy' => [ 'Proxy çok geç yanıt veriyor.' ]
                    ]
                ],
                422
            );
        }

        $health = $proxy_health;

        $proxy = Proxy::where('id', $request->id)->firstOrFail();
        $proxy->fill($request->all());
        $proxy->health = $health;
        $proxy->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Sil
     *
     * @return array
     */
    public static function proxyDelete(IdRequest $request)
    {
        $proxy = Proxy::where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $proxy->id
            ]
        ];

        $proxy->delete();

        return $arr;
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Proxy Kontrolü
     *
     * @return array
     */
    private static function getProxyHealth(string $proxy)
    {
        $starttime = microtime(true);

        $client = new Client([
            'base_uri' => 'https://www.google.com',
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $response = $client->get('/', [
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                ],
                'proxy' => $proxy
            ]);
        }
        catch (\Exception $e)
        {
            return 0;
        }

        $endtime = microtime(true);

        $load_time = intval($endtime - $starttime);
        $load_time = 10 - ($load_time > 10 ? 10 : $load_time);

        return $response->getStatusCode() == 200 ? $load_time : 0;
    }
}
