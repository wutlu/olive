<?php

namespace App\Http\Controllers\Twitter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Socialite;
use Laravel\Socialite\Contracts\Provider;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\Twitter\CreateKeywordRequest;
use App\Http\Requests\IdRequest;

class DataController extends Controller
{
	public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have', 'twitter:have' ]);
    }

    # twitter veri havuzu kelime listesi view.
    public function keywordList()
    {
    	$user = auth()->user();

        return view('twitter.data_pool.keyword_list', compact('user'));
    }

    # twitter veri havuzu kelime listesi json.
    public function keywordListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new StreamingKeywords;
        $query = $request->string ? $query->where('keyword', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # twitter veri havuzu kelime listesi oluşturma.
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

    # kelime sil.
    public static function keywordDelete(IdRequest $request)
    {
        StreamingKeywords::where('organisation_id', auth()->user()->organisation_id)->where('id', $request->id)->delete();

        return [
            'status' => 'ok',
        	'data' => [
        		'id' => $request->id
        	]
        ];
    }
}
