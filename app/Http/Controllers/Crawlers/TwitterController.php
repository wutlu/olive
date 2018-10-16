<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Elasticsearch\Indices;

use App\Models\Log;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;
use App\Models\Twitter\Account;

use App\Utilities\Term;

class TwitterController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin dashboard
    # 
    public static function dashboard()
    {
        return view('crawlers.twitter.dashboard');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # log ekranı data
    # 
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%google%')
                   ->where('updated_at', '>', $date)
                   ->orderBy('updated_at', 'DESC')
                   ->get();

        return [
            'status' => 'ok',
            'data' => $logs
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # istatistikler
    # 
    public static function statistics()
    {
        return [
            'status' => 'ok',
            'data' => [
                'twitter' => [
                ]
            ]
        ];
    }
}
