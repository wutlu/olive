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

use App\Models\Organisation\Organisation;

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
            $query->where('keyword', 'ILIKE', '%'.$string.'%');
        }

        if ($org_name)
        {
            $query->whereHas('organisation', function($q) use($org_name) {
                $q->where('name', $org_name);
            });
        }

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
}
