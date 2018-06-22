<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    # home
    public static function login()
    {
    	return view('user.login');
    }
}
