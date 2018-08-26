<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use App\Http\Requests\SearchRequest;

use App\Models\User\UserActivity;
use App\Models\Discount\DiscountDay;
use App\Models\User\UserIntro;

use App\Ticket;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'dashboard',
            'activity',
            'intro'
        ]);
        $this->middleware('root')->only('monitor');
    }

    # home
    public static function index()
    {
        $discountDay = DiscountDay::where('first_day', '<=', date('Y-m-d'))->where('last_day', '>=', date('Y-m-d'))->first();

        return view('home', compact('discountDay'));
    }

    # dashboard
    public static function dashboard()
    {
        $user = auth()->user();

        return view('dashboard', compact('user'));
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
    public static function intro(string $key)
    {
        $query = UserIntro::firstOrCreate([ 'user_id' => auth()->user()->id, 'key' => $key ]);

        return [
            'status' => 'ok'
        ];
    }

    # monitor
    public static function monitor()
    {
        return [
            'status' => 'ok',
            'data' => [
                'ticket' => [
                    'count' => Redis::get('monitor:ticket:count')
                ]
            ]
        ];
    }
}
