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

        $logs = SessionTable::with('user')
                            ->where('last_activity', '>=', $date)
                            ->orderBy('ip_address', 'asc')
                            ->orderBy('last_activity', 'asc')
                            ->get();

        $items = [];

        if (count($logs))
        {
            foreach ($logs as $log)
            {
                $location = geoip()->getLocation($log->ip_address);

                $array = [
                    'location' => [
                        'city' => $location->city,
                        'country' => $location->country
                    ],
                    'updated_at' => date('H:i:s', $log->last_activity)
                ];

                if (@$items[$log->ip_address])
                {
                    if ($log->ping > $items[$log->ip_address]['ping'])
                    {
                        $items[implode('.', [ $log->ip_address, md5($log->user_agent) ])] = array_merge($array, $log->toArray());
                    }
                }
                else
                {
                    $items[implode('.', [ $log->ip_address, md5($log->user_agent) ])] = array_merge($array, $log->toArray());
                }
            }
        }

        return [
            'status' => 'ok',
            'hits' => array_values($items),
            'total' => count($logs)
        ];
    }
}
