<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User\Newsletter;

use App\Http\Requests\SearchRequest;

use App\Models\User\User;

class NewsletterController extends Controller
{
	public function __construct()
	{
	}

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni ana sayfa.
    # 
    public static function dashboard()
    {
        return view('user.newsletter.dashboard');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni json çıktısı.
    # 
    public static function json(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new Newsletter;
        $query = $request->string ? $query->where('subject', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni formu.
    # 
    public static function form(int $id = 0)
    {
        $nlet = null;

        return view('user.newsletter.form', compact('nlet'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni formu.
    # 
    public static function formSave(int $id = 0)
    {
        $nlet = null;

        return view('user.newsletter.form', compact('nlet'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni için tüm kullanıcıların e-posta listesi.
    # 
    public static function users()
    {
        $users = User::select('email')->where('verified', true)->get()->pluck('email');

        return [
            'status' => 'ok',
            'data' => [
                'hits' => $users
            ]
        ];
    }
}
