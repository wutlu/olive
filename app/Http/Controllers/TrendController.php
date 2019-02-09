<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Elasticsearch\Document;

use Carbon\Carbon;

class TrendController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    /**
     * Trend Analizi Ana Sayfa
     *
     * @return view
     */
    public function dashboard()
    {
        return view('trends.live');
    }

    /**
     * Trend Endex Ekranı
     *
     * @return view
     */
    public function index()
    {
        return view('trends.index');
    }

    /**
     * Trend Arşiv Ekranı
     *
     * @return view
     */
    public function archive()
    {
        return view('trends.archive');
    }
}
