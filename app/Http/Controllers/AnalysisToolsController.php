<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AnalysisTool;

use App\Http\Requests\AnalysisTools\CreateRequest;

use App\Models\Proxy;
use App\Models\Twitter\StreamingUsers;
use App\Models\YouTube\FollowingChannels;
use App\Models\Crawlers\Instagram\Selves;

use YouTube;
use System;

use App\Instagram;

use App\Http\Controllers\Instagram\DataController;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Http\Requests\IdRequest;

class AnalysisToolsController extends Controller
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
        $this->middleware([ 'can:organisation-status' ]);
    }

    /**
     * Analiz Araçları, Ana Sayfa
     *
     * @return view
     */
    public static function dashboard(Request $request, int $pager = 5)
    {
        $request->validate([
            'q' => 'nullable|string|max:100'
        ]);

        $user = auth()->user();

        $data = new AnalysisTool;

        if ($request->q)
        {
            $data = $data->where('social_title', 'ILIKE', '%'.$request->q.'%');
        }

        $data = $data->where('organisation_id', $user->organisation_id)->orderBy('created_at', 'DESC')->paginate($pager);

        $q = $request->q;

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('analysis_tools.dashboard');
        }

        return view('analysisTools.dashboard', compact('user', 'data', 'q', 'pager'));
    }

    /**
     * Analiz Araçları, Analiz Formu
     *
     * @return view
     */
    public static function analysis(int $id)
    {
        $analysis = AnalysisTool::where([ 'organisation_id' => auth()->user()->id, 'id' => $id ])->firstOrFail();

        return view('analysisTools.analysis', compact('analysis'));
    }

    /**
     * Analiz Araçları, Analiz Oluştur
     *
     * @return array
     */
    public static function create(CreateRequest $request)
    {
        $user = auth()->user();

        $query = new AnalysisTool;
        $query->platform = session('platform');
        $query->social_id = session('social_id');
        $query->organisation_id = auth()->user()->organisation_id;

        $data_pool = null;

        $document = session('document');

        switch ($query->platform)
        {
            case 'twitter':
                $query->social_title = $document->data['_source']['user']['name'];

                if ($request->data_pool)
                {
                    $count = StreamingUsers::where('organisation_id', $user->organisation_id)->count();

                    if ($count < $user->organisation->data_pool_twitter_user_limit)
                    {
                        $dp = StreamingUsers::firstOrNew([
                            'user_id' => $document->data['_source']['user']['id'],
                            'organisation_id' => $user->organisation_id
                        ]);

                        $dp->screen_name = $document->data['_source']['user']['screen_name'];
                        $dp->user_id = $document->data['_source']['user']['id'];
                        $dp->status = true;
                        $dp->organisation_id = $user->organisation_id;
                        $dp->verified = @$document->data['_source']['user']['verified'] ? true : false;
                        $dp->save();

                        $data_pool = true;
                    }
                    else
                    {
                        $data_pool = false;
                    }
                }
            break;
            case 'youtube':
                $query->social_title = $document->data['_source']['channel']['title'];

                if ($request->data_pool)
                {
                    $count = FollowingChannels::where('organisation_id', $user->organisation_id)->count();

                    if ($count < $user->organisation->data_pool_youtube_channel_limit)
                    {
                        try
                        {
                            $channel = YouTube::getChannelById($document->data['_source']['channel']['id']);

                            $dp = FollowingChannels::firstOrNew([
                                'channel_id' => $channel->id,
                                'organisation_id' => $user->organisation_id
                            ]);
                            $dp->channel_image = $channel->snippet->thumbnails->default->url;
                            $dp->channel_title = $channel->snippet->title;
                            $dp->channel_id = $channel->id;
                            $dp->status = true;
                            $dp->organisation_id = $user->organisation_id;
                            $dp->save();

                            $data_pool = true;
                        }
                        catch (\Exception $e)
                        {
                            System::log(
                                json_encode(
                                    $e->getMessage()
                                ),
                                'App\Http\Controllers\AnalysisToolsController::create(youtube)',
                                10
                            );

                            return [
                                'status' => 'err',
                                'message' => 'Kullanıcının canlı profiline ulaşılamadı.'
                            ];
                        }
                    }
                    else
                    {
                        $data_pool = false;
                    }
                }
            break;
            case 'instagram':
                $count = Selves::where('organisation_id', $user->organisation_id)->count();

                if ($count < $user->organisation->data_pool_instagram_follow_limit)
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
                                'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                                'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                            ],
                            'verify' => false
                        ];

                        $proxy = Proxy::where('health', '>', 7)->inRandomOrder();

                        if ($proxy->exists())
                        {
                            $arr['proxy'] = $proxy->first()->proxy;
                        }

                        $client = $client->get('api/v1/users/'.$document->data['_source']['user']['id'].'/info', $arr);

                        $array = json_decode($client->getBody(), true);

                        $url = 'https://www.instagram.com/'.$array['user']['username'];

                        if ($request->data_pool)
                        {
                            $dp = Selves::firstOrNew([
                                'url' => $url,
                                'method' => 'user',
                                'organisation_id' => $user->organisation_id
                            ]);
                            $dp->url = $url;
                            $dp->method = 'user';
                            $dp->status = true;
                            $dp->organisation_id = $user->organisation_id;
                            $dp->save();

                            $data_pool = true;
                        }

                        $query->social_title = $array['user']['username'];
                    }
                    catch (\Exception $e)
                    {
                        System::log(
                            json_encode(
                                $e->getMessage()
                            ),
                            'App\Http\Controllers\AnalysisToolsController::create(instagram)',
                            10
                        );

                        return [
                            'status' => 'err',
                            'message' => 'Kullanıcının canlı profiline ulaşılamadı.'
                        ];
                    }
                }
                else
                {
                    $data_pool = false;
                }
            break;
        }

        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'route' => route('analysis_tools.analysis', $query->id),
                'data_pool' => $data_pool
            ]
        ];
    }

    /**
     * Analiz Araçları, Analiz Sil
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        $query = AnalysisTool::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }
}
