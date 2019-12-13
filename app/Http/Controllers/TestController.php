<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notifications\MessageNotification;
use App\Models\User\User;

use App\Models\Link;

use Carbon\Carbon;

use App\Models\Report;

use App\Jobs\ReportJob;

use System;

class TestController extends Controller
{
    public static function test(Request $request)
    {
            $user = User::where('id', config('app.user_id_support'))->first();

            if (@$user && $user->organisation_id)
            {
                $report = new Report;
                $report->key = time().rand(1, 10).rand(10, 100).rand(1000, 1000000);
                $report->name = 'Örnek Otomatik Rapor';
                $report->date_1 = date('d-m-Y', strtotime('-8 days'));
                $report->date_2 = date('d-m-Y', strtotime('-1 days'));
                $report->organisation_id = $user->organisation_id;
                $report->user_id = $user->id;
                $report->password = rand(1000, 9999);
                $report->status = 'creating';
                $report->gsm = '(543) 353 04 93';
                $report->subject = 'asgşkldsjgldaljhg';
                $report->save();

                ReportJob::dispatch(null, $report)->onQueue('process');
            }
            else
            {
                System::log(
                    'ENV dosyasında belirtilen destek hesabına erişilemiyor.',
                    'App\Http\Controllers\HomeController::reportRequest()',
                    10
                );
            }
    }
}
