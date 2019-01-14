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

use App\Models\Organisation\Organisation;

class DataController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, takip edilen kelime listesi.
     *
     * @return view
     */
    public function keywordList(int $id = 0)
    {
        $organisation = $id ? Organisation::where('id', $id)->firstOrFail() : null;

        return view('crawlers.youtube.dataPool.keyword_list', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, takip edilen kelime listesi.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube sorunlu kelimeler için neden belirtme.
     *
     * @return array
     */
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

    ### ### ###

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, takip edilen kanal listesi.
     *
     * @return view
     */
    public function channelList(int $id = 0)
    {
        $organisation = $id ? Organisation::where('id', $id)->firstOrFail() : null;

        return view('crawlers.youtube.dataPool.channel_list', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, takip edilen kelime listesi.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, sorunlu kanallar için neden belirtme.
     *
     * @return array
     */
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

    ### ### ###

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, takip edilen video listesi.
     *
     * @return view
     */
    public function videoList(int $id = 0)
    {
        $organisation = $id ? Organisation::where('id', $id)->firstOrFail() : null;

        return view('crawlers.youtube.dataPool.video_list', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, takip edilen video listesi.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube veri havuzu, sorunlu videolar için neden belirtme.
     *
     * @return array
     */
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
