<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    # home
    public static function index()
    {
    	return view('home');
    }
}
