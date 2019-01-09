<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function dashboard()
    {
    	return view('dataPool.dashboard');
    }
}
