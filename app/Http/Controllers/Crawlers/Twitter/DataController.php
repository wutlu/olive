<?php

namespace App\Http\Controllers\Crawlers\Twitter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Socialite;
use Laravel\Socialite\Contracts\Provider;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;

use App\Http\Requests\Twitter\Stream\KeywordReasonRequest;
use App\Http\Requests\Twitter\Stream\AccountReasonRequest;

class DataController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # twitter veri havuzu kelime listesi view.
    # 
    public function keywordList()
    {
        $user = auth()->user();

        return view('crawlers.twitter.data_pool.keyword_list', compact('user'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # twitter veri havuzu kelime listesi json.
    # 
    public function keywordListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new StreamingKeywords;
        $query = $query->with('organisation');
        $query = $request->string ? $query->where('keyword', 'ILIKE', '%'.$request->string.'%') : $query;
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
    # twitter veri havuzu sorunlu kelime.
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # twitter veri havuzu kullanıcı listesi view.
    # 
    public function accountList()
    {
        $user = auth()->user();

        return view('crawlers.twitter.data_pool.account_list', compact('user'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # twitter veri havuzu kullanıcı listesi json.
    # 
    public function accountListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new StreamingUsers;
        $query = $query->with('organisation');
        $query = $request->string ? $query->where('screen_name', 'ILIKE', '%'.$request->string.'%') : $query;
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
    # twitter veri havuzu sorunlu profil.
    # 
    public function accountReason(AccountReasonRequest $request)
    {
        $query = StreamingUsers::where('user_id', $request->user_id)->firstOrFail();

        StreamingUsers::where('user_id', $query->user_id)->update([ 'reason' => $request->reason ]);

        return [
            'status' => 'ok',
            'data' => [
                'user_id' => $request->user_id,
                'reason' => $request->reason
            ]
        ];
    }
}
