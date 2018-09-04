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

    # İzleme Ekranı
    public static function dashboard()
    {
        $data['ram']['total'] = System::getRamTotal();
        $data['ram']['free'] = System::getRamFree();
        $data['disk'] = System::getDiskSize();
        $data['cpu'] = System::getCpuLoadPercentage();

        return [
            'status' => 'ok',
            'data' => $data
        ];

        return view('monitor.dashboard');
    }
}
