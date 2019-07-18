<?php

namespace App\Http\Controllers\Crawlers\Twitter;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;

use App\Http\Requests\Twitter\Reason\KeywordRequest as KeywordReasonRequest;
use App\Http\Requests\Twitter\Reason\AccountRequest as AccountReasonRequest;
use App\Http\Requests\Twitter\CreateBlockedTrendKeywordRequest;

use App\Models\Organisation\Organisation;
use App\Models\Twitter\BlockedTrendKeywords;

class DataController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, takip edilen kelime listesi.
     *
     * @return view
     */
    public function keywordList(int $id = 0)
    {
        $organisation = $id ? Organisation::where('id', $id)->firstOrFail() : null;

        return view('crawlers.twitter.dataPool.keyword_list', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, takip edilen kelime listesi.
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

        $query = new StreamingKeywords;
        $query = $query->with('organisation');

        if ($string)
        {
            $query = $query->where('keyword', 'ILIKE', '%'.$string.'%');
        }

        if ($org_name)
        {
            $query->whereHas('organisation', function($q) use($org_name) {
                $q->where('name', $org_name);
            });
        }

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, sorunlu kelimeler için neden belirtme.
     *
     * @return array
     */
    public function keywordReason(KeywordReasonRequest $request)
    {
        $query = StreamingKeywords::where('id', $request->id)->firstOrFail();

        StreamingKeywords::where('keyword', $query->keyword)->update([ 'reason' => $request->reason ]);

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id,
                'keyword' => $query->keyword,
                'reason' => $request->reason
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, takip edilen kullanıcı listesi.
     *
     * @return view
     */
    public function accountList(int $id = 0)
    {
        $organisation = $id ? Organisation::where('id', $id)->firstOrFail() : null;

        return view('crawlers.twitter.dataPool.account_list', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, takip edilen kullanıcı listesi.
     *
     * @return array
     */
    public function accountListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        preg_match('/(?<=\@)[([a-zA-Z0-9-_\.]+(?=)/', $request->string, $matches);

        $org_name = @$matches[0];
        $string = trim(preg_replace('/\@[([a-zA-Z0-9-_\.]+/', '', $request->string));

        $query = new StreamingUsers;
        $query = $query->with('organisation');

        if ($string)
        {
            $query->where('screen_name', 'ILIKE', '%'.$string.'%');
        }

        if ($org_name)
        {
            $query->whereHas('organisation', function($q) use($org_name) {
                $q->where('name', $org_name);
            });
        }

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, sorunlu kullanıcılar için neden belirtme.
     *
     * @return array
     */
    public function accountReason(AccountReasonRequest $request)
    {
        $query = StreamingUsers::where('id', $request->id)->firstOrFail();
                 StreamingUsers::where('user_id', $query->user_id)->update([ 'reason' => $request->reason ]);

        return [
            'status' => 'ok',
            'data' => [
                'user_id' => $query->user_id,
                'reason' => $request->reason
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, engelli kelime listesi.
     *
     * @return view
     */
    public function blockedTrendKeywordList(int $id = 0)
    {
        return view('crawlers.twitter.dataPool.blocked_keyword_list');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter veri havuzu, engelli kelime listesi.
     *
     * @return array
     */
    public function blockedTrendKeywordListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new BlockedTrendKeywords;

        if ($request->string)
        {
            $query = $query->where('keyword', 'ILIKE', '%'.$request->string.'%');
        }

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }

    /**
     * Twitter veri havuzu, engelli kelime oluşturma.
     *
     * @return array
     */
    public function blockedTrendKeywordCreate(CreateBlockedTrendKeywordRequest $request)
    {
        $query = new BlockedTrendKeywords;
        $query->keyword = $request->string;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Twitter veri havuzu, engelli kelime silme.
     *
     * @return array
     */
    public static function blockedTrendKeywordDelete(IdRequest $request)
    {
        $query = BlockedTrendKeywords::where('id', $request->id)->firstOrFail();

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
