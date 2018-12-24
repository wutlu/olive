<?php

namespace App\Http\Controllers\Forum;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForumController extends Controller
{
    # 
    # index
    # 
    public static function index()
    {
        return view('forum.index');
    }
}
