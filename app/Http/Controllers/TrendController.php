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
    # trend analiz ekranı
    # 
    public function dashboard()
    {
        return view('trends.live');
    }

    # 
    # trend endeks ekranı
    # 
    public function index()
    {
        return view('trends.index');
    }

    # 
    # trend arşiv ekranı
    # 
    public function archive()
    {
        return view('trends.archive');
    }
}
