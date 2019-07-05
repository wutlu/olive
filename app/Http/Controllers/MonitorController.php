<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use System;
use Mail;
use Artisan;
use Redis;

use App\Utilities\Term;

use App\Mail\ServerAlertMail;

use Carbon\Carbon;

use App\Models\Option;
use App\Models\Log;
use App\Models\User\UserActivity;

use App\Http\Requests\ShellRequest;
use App\Http\Requests\SearchRequest;

use App\Jobs\KillProcessJob;

class MonitorController extends Controller
{
    /**
     * Sunucu Bilgisi Ekranı
     *
     * @return view
     */
    public static function server()
    {
        $disks = System::getDiskSize();

        return view('monitor.server', compact('disks'));
    }

    /**
     * Aktiviteler
     *
     * @return array
     */
    public static function activity(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new UserActivity;
        $query = $query->with('user');
        $query = $request->string ? $query->where(function($query) use ($request) {
            $query->orWhere('title', 'ILIKE', '%'.$request->string.'%');
            $query->orWhere('markdown', 'ILIKE', '%'.$request->string.'%');
        }) : $query;

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     * Sunucu Bilgisi
     *
     * @return array
     */
    public static function serverJson()
    {
        $data['ram']['total'] = System::getRamTotal();
        $data['ram']['free'] = System::getRamFree();
        $data['cpu'] = [
            'core' => System::getCpuNumber(),
            'usage' => shell_exec(storage_path('app/cpu_usage'))
        ];

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ****** SYSTEM ******
     ********************
     *
     * Alarm Kontrolü
     * - Anlık kontrol sorgusu yapılır ve sorun
     * durumunda yöneticilere e-posta bildirimi yapılır.
     *
     * @return mixed
     */
    public static function alarmControl()
    {
        $data = self::serverJson();

        $ram_percent = 100-100/$data['data']['ram']['total']->size*$data['data']['ram']['free']->size;
        $cpu_percent = $data['data']['cpu']['usage'];

        $message[] = '| Bileşen                    | Tüketim                                          |';
        $message[] = '| -------------------------: | :----------------------------------------------- |';

        if ($ram_percent > 100)
        {
            $message[] = '| RAM tüketimi           | '.$ram_percent.'%                                |';
        }

        if ($cpu_percent > 100)
        {
            $message[] = '| CPU tüketimi           | '.$cpu_percent.'%                                |';
        }

        foreach (System::getDiskSize() as $disk)
        {
            $hdd_percent = 100-100/$disk['total']->size*$disk['free']->size;

            if ($hdd_percent > 90)
            {
                $message[] = '| DISK kullanımı     | '.$hdd_percent.'%                               |';
            }
        }

        $message[] = PHP_EOL;

        if (count($message) > 3 && config('app.env') == 'production')
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

    /**
     * Log Ekranı
     *
     * @return view
     */
    public static function log()
    {
        return view('monitor.log');
    }

    /**
     * Log Verisi
     *
     * @return array
     */
    public static function logJson()
    {
        $date = Carbon::now()->subHours(2)->format('Y-m-d H:i:s');

        $logs = Log::where('updated_at', '>', $date)->orderBy('updated_at', 'DESC')->get();

        $log_files = [];

        foreach (config('app.log_files') as $key => $file)
        {
            $log_files[$key] = (object) [
                'id' => $key,
                'path' => $file,
                'size' => Term::humanFileSize(filesize($file))
            ];
        }

        return [
            'status' => 'ok',
            'data' => $logs,
            'files' => $log_files
        ];
    }

    /**
     * Log Temizle
     *
     * @return array
     */
    public static function logClear()
    {
        foreach (config('app.log_files') as $file)
        {
            file_put_contents($file, '');
        }

        Log::truncate();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Arkaplan Monitörü
     *
     * @return view
     */
    public static function background()
    {
        return view('monitor.background');
    }

    /**
     * Arkaplan İşlemleri
     *
     * @return array
     */
    public static function backgroundProcesses()
    {
        $pids = [];
        $lines = [];

        $redis = new Redis;

        exec('ps axo time,pid,cmd | grep '.base_path('artisan'), $output);

        if (count($output))
        {
            foreach ($output as $key => $line)
            {
                $split = preg_split('/(\d{2}:\d{2}:\d{2})[ ]+(\d+)[ ]+(.+)/i', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

                $lines[] = [
                    'time' => @$split[0],
                    'pid' => @$split[1],
                    'command' => @$split[2],
                ];

                $pids[@$split[1]] = true;
            }
        }

        session()->flash('pids', $pids);

        return [
            'status' => 'ok',
            'data' => $lines,
            'queues' => ''
        ];
    }

    /**
     * Arkaplan İşlemi Öldür
     *
     * @return array
     */
    public static function processKill(ShellRequest $request)
    {
        KillProcessJob::dispatch($request->pid)->onQueue('trigger');

        return [
            'status' => 'ok'
        ];
    }
}
