<?php

namespace App\Http\Controllers\Crawlers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewspaperController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Gazete Ayarları, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function dashboard()
    {
        return view('crawlers.newspaper.dashboard');
    }
}
