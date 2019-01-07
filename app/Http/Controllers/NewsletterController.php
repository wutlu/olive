<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsletterController extends Controller
{
	public function __construct()
	{
	}

    # e-posta bülteni liste view
    public static function adminListView()
    {
        return view('user.newsletter.list');
    }
}
