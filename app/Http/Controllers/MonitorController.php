<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use System;
use Mail;

use App\Utilities\Term;

use App\Mail\ServerAlertMail;

use Carbon\Carbon;

use App\Models\Option;
use App\Models\Log;

class MonitorController extends Controller
{
    # sunucu ekranı
    public static function server()
    {
        $disks = System::getDiskSize();

        return view('monitor.server', compact('disks'));
    }

    # sunucu ekranı data
    public static function serverJson()
    {
        $data['ram']['total'] = System::getRamTotal();
        $data['ram']['free'] = System::getRamFree();
        $data['cpu'] = [
            'core' => System::getCpuNumber(),
            'usage' => sys_getloadavg()[0]
        ];

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    # alarm kontrolü
    public static function alarmControl()
    {
        $data = self::serverJson();

        $ram_percent = 100-100/$data['data']['ram']['total']->size*$data['data']['ram']['free']->size;
        $cpu_percent = $data['data']['cpu']['usage'];

        $message[] = '| Bileşen | Tüketim |';
        $message[] = '| ------: | :------ |';

        if ($ram_percent > 96)
        {
            $message[] = '| RAM tüketimi | '.$ram_percent.'% |';
        }

        if ($cpu_percent > 96)
        {
            $message[] = '| CPU tüketimi | '.$cpu_percent.'% |';
        }

        foreach (System::getDiskSize() as $disk)
        {
            $hdd_percent = 100-100/$disk['total']->size*$disk['free']->size;

            if ($hdd_percent > 90)
            {
                $message[] = '| DISK kullanımı | '.$hdd_percent.'% |';
            }
        }

        if (count($message) > 2)
        {
            $message[] = 'Lütfen sunucuya müdehale edin!';

            $option = Option::where('key', 'email_alerts.server')->first();

            if (@$option)
            {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $option->value)->addMinutes(10)->format('Y-m-d H:i:s');

                if ($date <= date('Y-m-d H:i:s'))
                {
                    $option->update([ 'value' => date('Y-m-d H:i:s') ]);

                    Mail::queue(new ServerAlertMail('Yüksek Bileşen Tüketimi!', implode(PHP_EOL, $message)));
                }
            }
        }
    }

    # log ekranı
    public static function log()
    {
        return view('monitor.log');
    }

    # log ekranı data
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('updated_at', '>', $date)->orderBy('updated_at', 'DESC')->get();

        return [
            'status' => 'ok',
            'data' => $logs
        ];
    }
}
