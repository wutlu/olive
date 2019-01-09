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

        $logs = SessionTable::with('user')->where('last_activity', '>=', $date)->get();

        return [
            'status' => 'ok',
            'hits' => $logs,
            'total' => count($logs)
        ];
    }
}
