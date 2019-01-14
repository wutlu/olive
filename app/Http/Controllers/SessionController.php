<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Session as SessionTable;

use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Log Ekranı
     *
     * @return view
     */
    public function logs()
    {
        return view('guestLog');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Log Ekranı
     *
     * @return array
     */
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
