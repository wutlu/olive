<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    	return view('real-time.dashboard');
    }
}
