<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use System;
use App\Utilities\Term;

class MonitorController extends Controller
{
    public function __construct()
    {
        //
    }

    # ekran
    public static function server()
    {
        $disks = [
            System::getDiskSize()
        ];

        return view('monitor.server', compact('disks'));
    }

    # ekran data
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
}
