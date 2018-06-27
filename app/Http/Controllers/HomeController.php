<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth')->only([
			'dashboard'
		]);
	}

    # home
    public static function index()
    {
    	return view('home');
    }

    # dashboard
    public static function dashboard()
    {
    	return view('dashboard');
    }
}
