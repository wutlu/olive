<?php

namespace App\Http\Controllers\Forum;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Forum\Category;
use App\Models\Forum\Message;

use App\Http\Requests\IdRequest;
use App\Http\Requests\Forum\Kategori\UpdateRequest;
use App\Http\Requests\Forum\Kategori\CreateRequest;

class ForumController extends Controller
{
    /**
     * forum ana sayfa
     */
    public static function index(int $pager = 10)
    {
        $data = Message::whereNull('message_id')->orderBy('updated_at', 'DESC')->simplePaginate($pager);

        return view('forum.index', compact('data'));
    }

    /**
     * kategoriler
     */
    public static function categoryJson()
    {
        return [
            'status' => 'ok',
            'hits' => array_map(function($item) {
                return array_merge($item, [ 'url' => route('forum.category', $item['slug']) ]);
            }, Category::orderBy('sort')->get()->toArray())
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin get forum kategori
    # 
    public static function categoryGet(IdRequest $request)
    {
        $data = Category::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin update forum kategori
    # 
    public static function categoryUpdate(UpdateRequest $request)
    {
        $query = Category::where('id', $request->id)->firstOrFail();
        $query->fill($request->all());
        $query->save();

        return [
            'status' => 'ok',
            'data' => array_merge($query->toArray(), [ 'url' => route('forum.category', $query->slug) ])
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create forum kategori
    # 
    public static function categoryCreate(CreateRequest $request)
    {
        $query = new Category;
        $query->fill($request->all());
        $query->sort = intval(Category::orderBy('sort', 'DESC')->take(1)->value('sort'))+1;
        $query->save();

        return [
            'status' => 'ok',
            'data' => array_merge($query->toArray(), [ 'url' => route('forum.category', $query->slug) ])
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin delete forum kategori
    # 
    public static function categoryDelete(IdRequest $request)
    {
        $category = Category::where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $category->id
            ]
        ];

        $category->delete();

        return $arr;
    }

    /*******************************************************************************/

    /**
     * forum konu sayfası
     */
    public static function thread(string $slug, string $fake_slug, int $id)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $thread = Message::where('id', $id)->whereNull('message_id')->firstOrFail();

        return view('forum.thread', compact('category', 'thread'));
    }
}
