<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrendController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # trend ekranı
    # 
    public function dashboard()
    {
        return view('trends.trend');
    }
}
