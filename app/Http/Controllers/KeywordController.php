<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;
use App\Models\Keyword;

use App\Http\Requests\Keyword\CreateRequest;
use App\Http\Requests\Keyword\UpdateRequest;
use App\Http\Requests\IdRequest;

use App\Jobs\KeywordJob;

class KeywordController extends Controller
{
	public function __construct()
	{
        $this->middleware('auth');
        $this->middleware('organisation:have');
	}

    # kelime list view
    public static function listView()
    {
        return view('keyword');
    }

    # 
    # kelime list view
    # 
    public static function listViewJson(SearchRequest $request)
    {
        $user = auth()->user();
        $organisation = $user->organisation;

        $take = $request->take;
        $skip = $request->skip;

        $query = new Keyword;
        $query = $query->with('user');
        $query = $query->where('organisation_id', $organisation->id);
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

    # 
    # kelime update
    # 
    public static function update(UpdateRequest $request)
    {
        $user = auth()->user();

        $keyword = Keyword::where(
            [
                'id' => $request->id,
                'organisation_id' => $user->organisation_id
            ]
        )->first();

        $keyword->fill($request->all());
        $keyword->status = false;
        $keyword->save();

        // KeywordJob::dispatch($keyword->id);

        return [
            'status' => 'ok',
            'data' => [
                'id' => $keyword->id,
                'keyword' => $keyword->keyword
            ]
        ];
    }

    # 
    # kelime delete
    # 
    public static function delete(IdRequest $request)
    {
        $user = auth()->user();

        $keyword = $user->organisation->keywords()->where('id', $request->id)->first();

        $id = $keyword->id;

        $keyword->delete();

        KeywordJob::dispatch($id);

        return [
            'status' => 'ok',
            'data' => [
                'id' => $id,
                'count' => $user->organisation->keywords()->count()
            ]
        ];
    }

    # 
    # kelime create
    # 
    public static function create(CreateRequest $request)
    {
        $user = auth()->user();

        $keyword = new Keyword;
        $keyword->user_id = $user->id;
        $keyword->organisation_id = $user->organisation_id;
        $keyword->fill($request->all());
        $keyword->save();

        // KeywordJob::dispatch($keyword->id);

        return [
            'status' => 'ok'
        ];
    }
}
