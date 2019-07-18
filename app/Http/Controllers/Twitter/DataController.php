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
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         * -- real_time özelliği
         */
        $this->middleware([ 'auth', 'organisation:have' ]);

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Onayı
         * -- Twitter Hesabı
         */
        $this->middleware([ 'can:organisation-status' ])->only([
            'keywordCreate',
            'accountCreate'
        ]);
    }

    /**
     * Twitter veri havuzu, takip edilen kelime listesi.
     *
     * @return view
     */
    public function keywordList()
    {
        return view('twitter.dataPool.keyword_list');
    }

    /**
     * Twitter veri havuzu, takip edilen kelime listesi.
     *
     * @return array
     */
    public function keywordListJson(SearchRequest $request)
    {
        $query = new StreamingKeywords;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);

        $query = $request->string ? $query->where('keyword', 'ILIKE', '%'.$request->string.'%') : $query;

        $total = $query->count();

        $query = $query->skip($request->skip)
                       ->take($request->take)
                       ->orderBy('reason', 'DESC')
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }

    /**
     * Twitter veri havuzu, kelime oluşturma.
     *
     * @return array
     */
    public function keywordCreate(CreateKeywordRequest $request)
    {
        $query = new StreamingKeywords;
        $query->keyword = $request->string;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Twitter veri havuzu, kelime silme.
     *
     * @return array
     */
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

    /**
     * Twitter veri havuzu, hesap listesi.
     *
     * @return view
     */
    public function accountList()
    {
        return view('twitter.dataPool.account_list');
    }

    /**
     * Twitter veri havuzu, hesap listesi.
     *
     * @return array
     */
    public function accountListJson(SearchRequest $request)
    {
        $query = new StreamingUsers;

        $query = $query->where('organisation_id', auth()->user()->organisation_id);
        $query = $request->string ? $query->where(function($query) use($request) {
                                        $query->orWhere('screen_name', 'ILIKE', '%'.$request->string.'%');
                                        $query->orWhere('user_id', 'ILIKE', '%'.$request->string.'%');
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
     * Twitter veri havuzu, hesap tanımlama.
     *
     * @return array
     */
    public function accountCreate(CreateAccountRequest $request)
    {
        $account = session('account');

        $query = new StreamingUsers;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->user_id = $account->id_str;
        $query->screen_name = $account->screen_name;
        $query->verified = @$account->verified ? true : false;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Twitter veri havuzu, hesap silme.
     *
     * @return array
     */
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
