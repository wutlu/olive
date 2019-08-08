<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Source as SourceModel;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\BlogCrawler;
use App\Models\Crawlers\ShoppingCrawler;

use App\Http\Requests\SourceRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

class SourceController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');
        $this->middleware('organisation:have')->except('miniListJson');

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'can:organisation-status'
        ])->only([
            'delete',
            'save'
        ]);
    }

    /**
     * Kaynak Tercihleri, Tercihler
     *
     * @return view
     */
    public static function index(int $pager = 6)
    {
        $user = auth()->user();
        $query = SourceModel::where('organisation_id', $user->organisation_id)->orderBy('id', 'DESC')->paginate($pager);

        return view('source.index', compact('query', 'user'));
    }

    /**
     * Kaynak Tercihleri, Form
     *
     * @return view
     */
    public static function form(int $id = null)
    {
        $user = auth()->user();
        $query = $id ? SourceModel::where('id', $id)->where('organisation_id', $user->organisation_id)->firstOrFail() : [];
        $sources = (object) [
            'media' => MediaCrawler::orderBy('name', 'ASC')->get(),
            'sozluk' => SozlukCrawler::orderBy('name', 'ASC')->get(),
            'blog' => BlogCrawler::orderBy('name', 'ASC')->get(),
            'forum' => [],
            'shopping' => ShoppingCrawler::orderBy('name', 'ASC')->get()
        ];

        return view('source.form', compact('query', 'user', 'sources'));
    }

    /**
     * Kaynak Tercihleri, kaynak tercihi sil
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        $user = auth()->user();
        $query = SourceModel::where('id', $request->id)->where('organisation_id', $user->organisation_id)->delete();

        session()->flash('deleted');

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Kaynak Tercihleri, Form Kayıt
     *
     * @return array
     */
    public static function save(int $id = null, SourceRequest $request)
    {
        $user = auth()->user();

        if ($id)
        {
            $query = SourceModel::where('id', $id)->where('organisation_id', $user->organisation_id)->firstOrFail();
            $status = 'updated';
        }
        else
        {
            $count = SourceModel::where('organisation_id', $user->organisation_id)->count();

            if ($count >= $user->organisation->source_limit)
            {
                return [
                    'status' => 'err',
                    'reason' => 'Dahil olduğunuz organizasyon içerisinde en fazla '.$user->organisation->source_limit.' adet kaynak tercihi oluşturabilirsiniz!'
                ];
            }

            $query = new SourceModel;
            $query->organisation_id = $user->organisation_id;
            $status = 'created';
        }

        $query->name = $request->name;
        $query->source_media = $request->sources_media;
        $query->source_sozluk = $request->sources_sozluk;
        $query->source_blog = $request->sources_blog;
        $query->source_forum = $request->sources_forum;
        $query->source_shopping = $request->sources_shopping;
        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $query->id,
                'route' => route('sources.form', $query->id),
                'status' => $status
            ]
        ];
    }

    /**
     * Kaynak Tercihleri, mini list source
     *
     * @return array
     */
    public function miniListJson(SearchRequest $request)
    {
        $query = new SourceModel;
        $query = $query->where('organisation_id', auth()->user()->organisation_id);

        $total = $query->count();

        $query = $query->skip($request->skip)
                       ->take($request->take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }
}
