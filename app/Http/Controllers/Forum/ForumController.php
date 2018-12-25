<?php

namespace App\Http\Controllers\Forum;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Forum\Category;

class ForumController extends Controller
{
    /**
     * kategoriler
     */
    private static function categories()
    {
        return Category::orderBy('sort')->get();
    }

    /**
     * kategoriler
     */
    public static function index()
    {
        return view('forum.index', [ 'categories' => self::categories() ]);
    }
}
