<?php

namespace App\Http\Controllers\YouTube;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Socialite;
use App\Models\YouTube\FollowingChannels;
use App\Models\YouTube\FollowingVideos;
use App\Models\YouTube\FollowingKeywords;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\YouTube\CreateChannelRequest;
use App\Http\Requests\YouTube\CreateVideoRequest;
use App\Http\Requests\YouTube\CreateKeywordRequest;
use App\Http\Requests\IdRequest;

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
            'keywordCreate',
            'channelCreate',
            'videoCreate'
        ]);
    }

    /**
     * YouTube veri havuzu, takip edilen kelime listesi.
     *
     * @return view
     */
    public function keywordList()
    {
        return view('youtube.dataPool.keyword_list');
    }

    /**
     * YouTube veri havuzu, takip edilen kelime listesi.
     *
     * @return array
     */
    public function keywordListJson(SearchRequest $request)
    {
        $query = new FollowingKeywords;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);
        $query = $request->string ? $query->where(function($query) use ($request) {
            $query->orWhere('keyword', 'ILIKE', '%'.$request->string.'%');
        }) : $query;

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
     * YouTube veri havuzu, takip edilen kelime: oluştur.
     *
     * @return array
     */
    public function keywordCreate(CreateKeywordRequest $request)
    {
        $query = new FollowingKeywords;
        $query->keyword = $request->string;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * YouTube veri havuzu, takip edilen kelime: sil.
     *
     * @return array
     */
    public static function keywordDelete(IdRequest $request)
    {
        $query = FollowingKeywords::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $query->id
            ]
        ];

        $query->delete();

        return $arr;
    }

    ### ### ###

    /**
     * YouTube veri havuzu, takip edilen kanal listesi.
     *
     * @return view
     */
    public function channelList()
    {
        return view('youtube.dataPool.channel_list');
    }

    /**
     * YouTube veri havuzu, takip edilen kanal listesi.
     *
     * @return array
     */
    public function channelListJson(SearchRequest $request)
    {
        $query = new FollowingChannels;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);
        $query = $request->string ? $query->where(function($query)  use ($request) {
            $query->orWhere('channel_title', 'ILIKE', '%'.$request->string.'%');
            $query->orWhere('channel_id', 'ILIKE', '%'.$request->string.'%');
        }) : $query;

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
     * YouTube veri havuzu, takip edilen kanal: oluştur.
     *
     * @return array
     */
    public function channelCreate(CreateChannelRequest $request)
    {
        $channel = session('channel');

        $query = new FollowingChannels;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->channel_title = $channel->snippet->title;
        $query->channel_image = $channel->snippet->thumbnails->default->url;
        $query->channel_id = $channel->id;
        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'channel_id' => $query->channel_id
            ]
        ];
    }

    /**
     * YouTube veri havuzu, takip edilen kanal: sil.
     *
     * @return array
     */
    public static function channelDelete(IdRequest $request)
    {
        FollowingChannels::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }

    ### ### ###

    /**
     * YouTube veri havuzu, takip edilen video listesi.
     *
     * @return view
     */
    public function videoList()
    {
        return view('youtube.dataPool.video_list');
    }

    /**
     * YouTube veri havuzu, takip edilen video listesi.
     *
     * @return array
     */
    public function videoListJson(SearchRequest $request)
    {
        $query = new FollowingVideos;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);
        $query = $request->string ? $query->where(function($query) use ($request) {
            $query->orWhere('video_title', 'ILIKE', '%'.$request->string.'%');
            $query->orWhere('video_id', 'ILIKE', '%'.$request->string.'%');
        }) : $query;

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
     * YouTube veri havuzu, takip edilen video: oluştur.
     *
     * @return array
     */
    public function videoCreate(CreateVideoRequest $request)
    {
        $video = session('video');

        $query = new FollowingVideos;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->video_id = $video->id;
        $query->video_title = $video->snippet->title;
        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'channel_id' => $query->video_id
            ]
        ];
    }

    /**
     * YouTube veri havuzu, takip edilen video: sil.
     *
     * @return array
     */
    public static function videoDelete(IdRequest $request)
    {
        FollowingVideos::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }
}
