<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Session as SessionTable;

use Carbon\Carbon;

class SessionController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # log ekranı.
    # 
    public function logs()
    {
        return view('guestLog');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # log ekranı json çıktısı.
    # 
    public static function logsJson()
    {
        $date = Carbon::now()->subMinutes(1)->timestamp;

        $logs = SessionTable::with('user')->where('last_activity', '>=', $date)->get()->toArray();

        return [
            'status' => 'ok',
            'hits' => array_map(function($array) {
                $location = geoip()->getLocation($array['ip_address']);

                return array_merge($array, [
                    'location' => [
                        'city' => $location->city,
                        'country' => $location->country
                    ]
                ]);
            }, $logs),
            'total' => count($logs)
        ];
    }
}
