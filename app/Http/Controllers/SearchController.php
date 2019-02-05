<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class SearchController extends Controller
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
    	return view('search.dashboard');
    }
}
