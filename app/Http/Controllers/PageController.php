<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Page\CreateRequest;
use App\Http\Requests\Page\UpdateRequest;
use App\Http\Requests\IdRequest;

use App\Models\Page;

class PageController extends Controller
{
    # 
    # view
    # 
    public static function view(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        return view('page.view', compact('page'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function adminListView(int $pager = 10)
    {
        $pages = Page::paginate($pager);

        return view('page.admin.list', compact('pages'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin view
    # 
    public static function adminView(int $id = 0)
    {
        if ($id)
        {
            $page = Page::where('id', $id)->firstOrFail();
        }
        else
        {
            $coupon = [];
        }

        return view('page.admin.view', compact('page'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin update
    # 
    public static function adminUpdate(UpdateRequest $request)
    {
        $request['slug'] = str_slug($request->slug);

        $page = Page::where('id', $request->id)->firstOrFail();
        $page->fill($request->all());
        $page->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'updated'
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create
    # 
    public static function adminCreate(CreateRequest $request)
    {
        $request['slug'] = str_slug($request->slug);

        $page = new Page;
        $page->fill($request->all());
        $page->save();

        session()->flash('status', 'created');

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'created',
                'route' => route('admin.page', $page->id)
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin delete
    # 
    public static function adminDelete(IdRequest $request)
    {
        $page = Page::where('id', $request->id)->delete();

        session()->flash('status', 'deleted');

        return [
            'status' => 'ok'
        ];
    }
}
