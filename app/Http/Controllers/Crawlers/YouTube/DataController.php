<?php

namespace App\Http\Controllers\Crawlers\YouTube;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\YouTube\FollowingKeywords;
use App\Models\YouTube\FollowingChannels;
use App\Models\YouTube\FollowingVideos;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;

use App\Http\Requests\YouTube\Reason\KeywordRequest as KeywordReasonRequest;
use App\Http\Requests\YouTube\Reason\ChannelRequest as ChannelReasonRequest;
use App\Http\Requests\YouTube\Reason\VideoRequest as VideoReasonRequest;

class DataController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu kelime listesi view.
    # 
    public function keywordList()
    {
        return view('crawlers.youtube.dataPool.keyword_list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu kelime listesi json.
    # 
    public function keywordListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        preg_match('/(?<=\@)[([a-zA-Z0-9-_\.]+(?=)/', $request->string, $matches);

        $org_name = @$matches[0];
        $string = trim(preg_replace('/\@[([a-zA-Z0-9-_\.]+/', '', $request->string));

        $query = new FollowingKeywords;
        $query = $query->with('organisation');

    if ($string)
    {
        $query->where('keyword', 'ILIKE', '%'.$string.'%');
    }

    if ($org_name)
    {
        $query->whereHas('organisation', function($q) use($org_name) {
            $q->where('name', $org_name);
        });
    }

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu sorunlu kelime.
    # 
    public function keywordReason(KeywordReasonRequest $request)
    {
        $query = FollowingKeywords::where('id', $request->id)->firstOrFail();

        FollowingKeywords::where('keyword', $query->keyword)->update([ 'reason' => $request->reason ]);

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id,
                'keyword' => $query->keyword,
                'reason' => $request->reason
            ]
        ];
    }

    /* ********** */
    /* ************************* */
    /* ************************************************** */
    /* ************************* */
    /* ********** */

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu kanal listesi view.
    # 
    public function channelList()
    {
        return view('crawlers.youtube.dataPool.channel_list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu kanal listesi json.
    # 
    public function channelListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        preg_match('/(?<=\@)[([a-zA-Z0-9-_\.]+(?=)/', $request->string, $matches);

        $org_name = @$matches[0];
        $string = trim(preg_replace('/\@[([a-zA-Z0-9-_\.]+/', '', $request->string));

        $query = new FollowingChannels;
        $query = $query->with('organisation');

    if ($string)
    {
        $query->where('channel_title', 'ILIKE', '%'.$string.'%');
    }

    if ($org_name)
    {
        $query->whereHas('organisation', function($q) use($org_name) {
            $q->where('name', $org_name);
        });
    }

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu sorunlu kanal.
    # 
    public function channelReason(ChannelReasonRequest $request)
    {
        $query = FollowingChannels::where('id', $request->id)->firstOrFail();
                 FollowingChannels::where('channel_id', $query->channel_id)->update([ 'reason' => $request->reason ]);

        return [
            'status' => 'ok',
            'data' => [
                'channel_id' => $query->channel_id,
                'reason' => $request->reason
            ]
        ];
    }

    /* ********** */
    /* ************************* */
    /* ************************************************** */
    /* ************************* */
    /* ********** */

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu video listesi view.
    # 
    public function videoList()
    {
        return view('crawlers.youtube.dataPool.video_list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu video listesi json.
    # 
    public function videoListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        preg_match('/(?<=\@)[([a-zA-Z0-9-_\.]+(?=)/', $request->string, $matches);

        $org_name = @$matches[0];
        $string = trim(preg_replace('/\@[([a-zA-Z0-9-_\.]+/', '', $request->string));

        $query = new FollowingVideos;
        $query = $query->with('organisation');

    if ($string)
    {
        $query->where('video_title', 'ILIKE', '%'.$string.'%');
    }

    if ($org_name)
    {
        $query->whereHas('organisation', function($q) use($org_name) {
            $q->where('name', $org_name);
        });
    }

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube veri havuzu sorunlu video.
    # 
    public function videoReason(VideoReasonRequest $request)
    {
        $query = FollowingVideos::where('id', $request->id)->firstOrFail();
                 FollowingVideos::where('video_id', $query->video_id)->update([ 'reason' => $request->reason ]);

        return [
            'status' => 'ok',
            'data' => [
                'video_id' => $query->video_id,
                'reason' => $request->reason
            ]
        ];
    }
}
