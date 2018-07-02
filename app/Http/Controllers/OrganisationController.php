<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserActivity;

class OrganisationController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

    # başla
    public static function start()
    {
    	return view('start');
    }
}
