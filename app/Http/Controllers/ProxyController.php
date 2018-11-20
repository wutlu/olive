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

use Validator;

class ProxyController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy listesi view.
    # 
    public function proxies()
    {
        return view('proxy');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy listesi json çıktısı.
    # 
    public static function proxiesJson()
    {
        $proxies = new Proxy;
        $proxies = $proxies->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $proxies->get(),
            'total' => $proxies->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy bilgileri.
    # 
    public static function proxy(IdRequest $request)
    {
        $proxy = Proxy::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $proxy
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy oluştur.
    # 
    public static function proxyCreate(CreateRequest $request)
    {
        $proxy_health = self::getProxyHealth();

        if ($proxy_health <= $request->min_health)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'proxy' => [ 'Proxy çok yavaş.' ]
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy güncelle.
    # 
    public static function proxyUpdate(UpdateRequest $request)
    {
        $proxy_health = self::getProxyHealth();

        if ($proxy_health <= $request->min_health)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'proxy' => [ 'Proxy çok yavaş.' ]
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy sil.
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # proxy kontrol fonksiyonu.
    #
    public static function getProxyHealth(string $proxy = 'tcp://178.128.31.194:3128')
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

        if ($response->getStatusCode() == 200)
        {
            return $load_time;
        }
        else
        {
            return 0;
        }
    }
}
