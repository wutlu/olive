<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class DataController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Veri Havuzu Ana Sayfa
     *
     * @return view
     */
    public static function dashboard()
    {
    	return view('dataPool.dashboard');
    }
}
