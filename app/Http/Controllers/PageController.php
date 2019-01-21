<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Page\CreateRequest;
use App\Http\Requests\Page\UpdateRequest;
use App\Http\Requests\IdRequest;

use App\Models\Page;

class PageController extends Controller
{
    /**
     * Sayfa
     *
     * @return view
     */
    public static function view(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $pages = Page::get();

        return view('page.view', compact('page', 'pages'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sayfa Listesi
     *
     * @return view
     */
    public static function adminListView(int $pager = 10)
    {
        $pages = Page::paginate($pager);

        return view('page.admin.list', compact('pages'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sayfa
     *
     * @return view
     */
    public static function adminView(int $id = 0)
    {
        $page = $id ? Page::where('id', $id)->firstOrFail() : [];

        return view('page.admin.view', compact('page'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sayfa Güncelle
     *
     * @return array
     */
    public static function adminUpdate(UpdateRequest $request)
    {
        $request['slug'] = str_slug($request->slug);

        $page = Page::where('id', $request->id)->first();
        $page->fill($request->all());
        $page->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'updated'
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sayfa Oluştur
     *
     * @return view
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sayfa Sil
     *
     * @return view
     */
    public static function adminDelete(IdRequest $request)
    {
        $page = Page::where('id', $request->id)->delete();

        session()->flash('status', 'deleted');

        return [
            'status' => 'ok'
        ];
    }
}
