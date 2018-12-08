<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;

use App\Models\User\UserActivity;
use App\Models\Discount\DiscountDay;
use App\Models\User\UserIntro;
use App\Models\Option;

use App\Ticket;

use App\Utilities\DateUtility;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'dashboard',
            'activity',
            'intro',
            'alert',
            'monitor'
        ]);
    }

    # 
    # uyarı
    # 
    public static function alert()
    {
        return session('alert') ? view('alert') : redirect()->route('dashboard');
    }

    # home
    public static function index(Request $request)
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
        $data = [
            'ticket' => [
                'count' => 0
            ],
            'push_notifications' => []
        ];
        $user = auth()->user();

        if ($user->root())
        {
            $data['ticket']['count'] = Option::where('key', 'root_alert.support')->value('value');
        }

        $activities = UserActivity::where('user_id', $user->id)->where('push_notification', 'on')->limit(3)->get();

        if (count($activities))
        {
            foreach ($activities as $activity)
            {
                $data['push_notifications'][] = [
                    'title' => $activity->title,
                    'button' => $activity->button_type ? [
                        'type' => $activity->button_type,
                        'method' => $activity->button_method,
                        'action' => $activity->button_action,
                        'class' => $activity->button_class,
                        'text' => $activity->button_text,
                    ] : false
                ];

                $activity->push_notification = 'ok';
                $activity->save();
            }
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }
}
