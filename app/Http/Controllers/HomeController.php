<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserActivity;

use App\Http\Requests\SearchRequest;

use Auth;

class HomeController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth')->only([
			'dashboard',
            'activity',
            'skipIntro'
		]);
	}

    # home
    public static function index()
    {
    	return view('home');
    }

    # dashboard
    public static function dashboard()
    {
        return view('dashboard');
    }

    # activities
    public static function activity(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new UserActivity();
        $query = $request->string ? $query->where('title', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->where('user_id', auth()->user()->id);
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # skip intro
    public static function skipIntro()
    {
        Auth::user()->update([
            'skip_intro' => true
        ]);

        return [
            'status' => 'ok'
        ];
    }
}
