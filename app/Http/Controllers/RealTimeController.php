<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RealTimeController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # gerçek zamanlı ekranı.
    # 
    public function dashboard()
    {
    	return view('realtime.dashboard');
    }
}
