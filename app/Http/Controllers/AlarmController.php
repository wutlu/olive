<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlarmController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have', 'can:organisation-status' ]);
    }

    /**
     * Trend Analizi Ana Sayfa
     *
     * @return view
     */
    public function dashboard()
    {
        return view('alarm.dashboard');
    }
}
