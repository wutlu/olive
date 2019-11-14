<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRMController extends Controller
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
     * CRM Ana Sayfa
     *
     * @return view
     */
    public static function dashboard()
    {
        return view('crm.dashboard');
    }
}
