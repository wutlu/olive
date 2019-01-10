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
        $this->middleware('auth');
        $this->middleware('organisation:have,source');
        $this->middleware([ 'can:organisation-status' ])->only([
            'keywordCreate',
            'channelCreate',
            'videoCreate'
        ]);
    }

    # youtube veri havuzu kelime listesi view.
    public function keywordList()
    {
        return view('youtube.dataPool.keyword_list');
    }

    # youtube veri havuzu kelime listesi json.
    public function keywordListJson(int $skip = 0, int $take = 27)
    {
        $query = FollowingKeywords::where('organisation_id', auth()->user()->organisation_id)->skip($skip)->take($take)->orderBy('updated_at', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # youtube veri havuzu kelime oluşturma.
    public function keywordCreate(CreateKeywordRequest $request)
    {
        $query = new FollowingKeywords;
        $query->keyword = $request->keyword;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    # youtube veri havuzu kelime silme.
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

    /* ********** */
    /* ************************* */
    /* ************************************************** */
    /* ************************* */
    /* ********** */

    # youtube veri havuzu kanal listesi view.
    public function channelList()
    {
        return view('youtube.dataPool.channel_list');
    }

    # youtube veri havuzu kanal listesi json.
    public function channelListJson(int $skip = 0, int $take = 27)
    {
        $query = FollowingChannels::where('organisation_id', auth()->user()->organisation_id)->skip($skip)->take($take)->orderBy('updated_at', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # youtube veri havuzu kanal oluşturma.
    public function channelCreate(CreateChannelRequest $request)
    {
        $channel = session('channel');

        $query = new FollowingChannels;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->channel_name = $channel->snippet->title;
        $query->channel_image = $channel->snippet->thumbnails->high->url;
        $query->channel_id = $channel->id;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    # youtube veri havuzu kanal silme.
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

    /* ********** */
    /* ************************* */
    /* ************************************************** */
    /* ************************* */
    /* ********** */

    # youtube veri havuzu video listesi view.
    public function videoList()
    {
        return view('youtube.dataPool.video_list');
    }

    # youtube veri havuzu video listesi json.
    public function videoListJson(int $skip = 0, int $take = 27)
    {
        $query = FollowingVideos::where('organisation_id', auth()->user()->organisation_id)->skip($skip)->take($take)->orderBy('updated_at', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # youtube veri havuzu video oluşturma.
    public function videoCreate(CreateVideoRequest $request)
    {
        $video = session('video');

        $query = new FollowingVideos;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->video_id = $video->id;
        $query->video_title = $video->snippet->title;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    # youtube veri havuzu video silme.
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
