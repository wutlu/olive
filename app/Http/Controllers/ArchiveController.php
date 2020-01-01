<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

use App\Http\Requests\Archive\GetRequest;
use App\Http\Requests\Archive\CreateRequest;
use App\Http\Requests\Archive\UpdateRequest;

use App\Http\Requests\Archive\PdfRequest;
use App\Http\Requests\Archive\CommentRequest;

use App\Http\Requests\Archive\ArchiveRequest;

use App\Elasticsearch\Document;

use App\Models\Archive\Archive;
use App\Models\Archive\Item;

use App\Jobs\PDF\ArchiveJob;

use Carbon\Carbon;

use App\Utilities\Term;

class ArchiveController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have' ]);

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Onayı
         */
        $this->middleware('can:organisation-status')->only([
            'groupCreate',
            'pin',
            'comment'
        ]);

        $this->middleware('organisation:have')->only([
            'groupCreate'
        ]);
    }

    /**
     * Arşivler
     *
     * @return view
     */
    public function groups()
    {
        return view('archive.dashboard');
    }

    /**
     * Arşivler, Data
     *
     * @return array
     */
    public function groupListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = Archive::withCount('items');
        $query = $query->where('organisation_id', auth()->user()->organisation_id);
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     * Arşiv Bilgisi
     *
     * @return array
     */
    public function groupGet(GetRequest $request)
    {
        $data = Archive::select(
            'id',
            'name'
        )->with('items')->where([
            'id' => $request->archive_id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Arşiv Oluştur
     *
     * @return array
     */
    public function groupCreate(CreateRequest $request)
    {
        $data = new Archive;
        $data->organisation_id = auth()->user()->organisation_id;
        $data->name = $request->name;
        $data->save();

        return [
            'status' => 'ok',
            'type' => 'created'
        ];
    }

    /**
     * Arşiv Güncelle
     *
     * @return array
     */
    public function groupUpdate(UpdateRequest $request)
    {
        $data = Archive::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        $data->name = $request->name;
        $data->save();

        return [
            'status' => 'ok',
            'type' => 'updated',
            'data' => [
                'id' => $data->id,
                'name' => $data->name
            ]
        ];
    }

    /**
     * Arşiv Sil
     *
     * @return array
     */
    public function groupDelete(IdRequest $request)
    {
        $data = Archive::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $data->id
            ]
        ];

        $data->delete();

        return $arr;
    }

    /**
     * Arşiv
     *
     * @return array
     */
    public function pin(string $type, ArchiveRequest $request)
    {
        $user = auth()->user();

        $status = 'failed';

        try
        {
            $item = Item::where([
                'index' => $request->index,
                'type' => $request->type,
                'id' => $request->id,
                'archive_id' => $request->archive_id
            ])->where('organisation_id', $user->organisation_id)->first();

            if ($type == 'add')
            {
                if (@$item)
                {
                    $item->delete();
                    $status = 'removed';
                }
                else
                {
                    $document = Document::exists($request->index, $request->type, $request->id);

                    if ($document->status == 'ok')
                    {
                        $status = 'pinned';

                        switch ($document->data['_type'])
                        {
                            case 'tweet'  : $url = 'https://twitter.com/'.$document->data['_source']['user']['screen_name'].'/status/'.$document->data['_id']; break;
                            case 'media'  : $url = 'https://www.instagram.com/p/'.$document->data['_source']['shortcode'].'/'; break;
                            case 'video'  : $url = 'https://www.youtube.com/watch?v='.$document->data['_id']; break;
                            case 'comment': $url = 'https://www.youtube.com/channel/'.$document->data['_source']['channel']['id']; break;
                            case 'media'  : $url = $document->data['_source']['url']; break;
                            case 'article'  : $url = $document->data['_source']['url']; break;
                            case 'product'  : $url = $document->data['_source']['url']; break;
                            case 'document'  : $url = $document->data['_source']['url']; break;
                            case 'entry'  : $url = 'https://eksisozluk.com/entry/'.$document->data['_id']; break;
                            default: $url = ''; break;
                        }

                        $p = new Item;
                        $p->fill($request->all());
                        $p->user_id = $user->id;
                        $p->url = $url;
                        $p->content = $document->data['_source'];
                        $p->organisation_id = $user->organisation_id;
                        $p->save();
                    }
                }
            }
            else if ($type == 'remove')
            {
                if (@$item)
                {
                    $item->delete();
                    $status = 'removed';
                }
            }
        }
        catch (\Exception $e)
        {
            //
        }

        return [
            'status' => $status
        ];
    }

    /**
     * Arşiv, Pinler
     *
     * @return view
     */
    public function pins(int $id)
    {
        $archive = Archive::where('id', $id)->where('organisation_id', auth()->user()->organisation_id)->firstOrFail();
        $pins = $archive->items()->orderBy('created_at', 'DESC')->paginate(10);

        return view('archive.items', compact('archive', 'pins'));
    }

    /**
     * Arşiv, Url Çıktı
     *
     * @return view
     */
    public function pinUrls(int $id)
    {
        $archive = Archive::where('id', $id)->where('organisation_id', auth()->user()->organisation_id)->firstOrFail();

        $items = $archive->items()->orderBy('type', 'ASC')->orderBy('created_at', 'DESC')->get();

        if (count($items))
        {
            $content = [];

            foreach ($items as $item)
            {
                $content[] = implode([ $item->url ], '  ');
            }
        }
        else
        {
            $content = [ 'Hiç içerik arşivlenmedi.' ];
        }

        $headers = [
          'Content-type' => 'text/csv', 
          'Content-Disposition' => sprintf('attachment; filename="%s"', 'olive-'.str_slug($archive->name).'.csv'),
          'Cache-Control' => 'max-age=0'
        ];

        return \Response::make(implode($content, PHP_EOL), 200, $headers);
    }

    /**
     * Arşiv, PDF tetikleyici
     *
     * @return view
     */
    public function pdf(PdfRequest $request)
    {
        $archive = Archive::where('id', $request->id)->where('organisation_id', auth()->user()->organisation_id)->first();

        $archive->html_to_pdf = 'process';
        $archive->updated_at = date('Y-m-d H:i:s');
        $archive->save();

        ArchiveJob::dispatch($archive->id)->onQueue('process');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ****** SYSTEM ******
     ********************
     *
     * Arşiv, PDF zaman aşımı tetikleyici
     *
     * @return mixed
     */
    public static function pdfTrigger()
    {
        $date = Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s');
        $archives = Archive::where('html_to_pdf', 'process')->where('updated_at', '<', $date)->get();

        if (count($archives))
        {
            foreach ($archives as $archive)
            {
                $items = $archive->items()->count();

                if ($items > 100)
                {
                    echo Term::line('failed: many items');

                    $archive->html_to_pdf = null;
                }
                else
                {
                    echo Term::line('['.$archive->organisation->name.']['.$archive->name.']');

                    ArchiveJob::dispatch($archive->id)->onQueue('process');
                }

                $archive->updated_at = date('Y-m-d H:i:s');
                $archive->save();
            }
        }
    }

    /**
     * Pin, Yorum
     *
     * @return view
     */
    public function comment(CommentRequest $request)
    {
        $user = auth()->user();

        $item = Item::where([
            'index' => $request->index,
            'type' => $request->type,
            'id' => $request->id,
            'archive_id' => $request->archive_id
        ])->where('organisation_id', $user->organisation_id)->firstOrFail();

        $item->comment = $request->comment;
        $item->save();

        return [
            'status' => 'ok'
        ];
    }
}
