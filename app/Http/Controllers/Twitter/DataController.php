<?php

namespace App\Http\Controllers\Twitter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Socialite;
use Laravel\Socialite\Contracts\Provider;
use App\Models\Twitter\Account;

class DataController extends Controller
{
	public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have', 'twitter:have' ]);
    }

    # twitter data pool
    public function dataPool()
    {
        return view('twitter.data_pool');
    }
}
