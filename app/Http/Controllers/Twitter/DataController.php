<?php

namespace App\Http\Controllers\Twitter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Socialite;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\Twitter\CreateKeywordRequest;
use App\Http\Requests\Twitter\CreateAccountRequest;
use App\Http\Requests\IdRequest;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('organisation:have,source');
        $this->middleware([ 'can:organisation-status', 'twitter:have' ])->only([
            'keywordCreate',
            'accountCreate'
        ]);
    }

    # twitter veri havuzu kelime listesi view.
    public function keywordList()
    {
        return view('twitter.dataPool.keyword_list');
    }

    # twitter veri havuzu kelime listesi json.
    public function keywordListJson(int $skip = 0, int $take = 27)
    {
        $query = StreamingKeywords::where('organisation_id', auth()->user()->organisation_id)->skip($skip)->take($take)->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # twitter veri havuzu kelime oluşturma.
    public function keywordCreate(CreateKeywordRequest $request)
    {
        $query = new StreamingKeywords;
        $query->keyword = $request->keyword;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    # twitter veri havuzu kelime silme.
    public static function keywordDelete(IdRequest $request)
    {
        $query = StreamingKeywords::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $query->id
            ]
        ];

        $query->delete();

        return $arr;
    }

    # twitter veri havuzu kullanıcı listesi view.
    public function accountList()
    {
        return view('twitter.dataPool.account_list');
    }

    # twitter veri havuzu kullanıcı listesi json.
    public function accountListJson(int $skip = 0, int $take = 27)
    {
        $query = StreamingUsers::where('organisation_id', auth()->user()->organisation_id)->skip($skip)->take($take)->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # twitter veri havuzu kullanıcı oluşturma.
    public function accountCreate(CreateAccountRequest $request)
    {
        $account = session('account');

        $query = new StreamingUsers;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->user_id = $account->id_str;
        $query->screen_name = $account->screen_name;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    # twitter veri havuzu kullanıcı silme.
    public static function accountDelete(IdRequest $request)
    {
        StreamingUsers::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }
}
