<?php

namespace App\Http\Controllers\Crawlers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\DetectedDomains;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\BlogCrawler;

use App\Http\Requests\IdRequest;

class DomainController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, ana sayfası.
     *
     * @return view
     */
    public static function dashboard(Request $request, int $pager = 50)
    {
        $request->validate([
            'q' => 'nullable|string|max:100'
        ]);

        $user = auth()->user();

        $data = new DetectedDomains;

        if ($request->q)
        {
            $data = $data->where('domain', 'ILIKE', '%'.$request->q.'%');
        }

        $data = $data->orderBy('id', 'DESC')->orderBy('status', 'ASC')->paginate($pager);

        $q = $request->q;

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('crawlers.domain');
        }

        return view('crawlers.domain', compact('data', 'q', 'pager'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, durum kontrolü.
     *
     * @return array
     */
    public static function check(IdRequest $request)
    {
        $query = DetectedDomains::where('id', $request->id)->firstOrFail();

        $message = null;

        $key = str_replace([ 'https://', 'http://', 'www.' ], '', $query->domain);

        $media_crawler = MediaCrawler::orWhere('site', 'like', 'http://'.$key.'%')->orWhere('site', 'like', 'http://www.'.$key.'%')->orWhere('site', 'like', 'https://'.$key.'%')->orWhere('site', 'like', 'https://www.'.$key.'%')->exists();
        $blog_crawler = BlogCrawler::orWhere('site', 'like', 'http://'.$key.'%')->orWhere('site', 'like', 'http://www.'.$key.'%')->orWhere('site', 'like', 'https://'.$key.'%')->orWhere('site', 'like', 'https://www.'.$key.'%')->exists();
        $sozluk_crawler = SozlukCrawler::orWhere('site', 'like', 'http://'.$key.'%')->orWhere('site', 'like', 'http://www.'.$key.'%')->orWhere('site', 'like', 'https://'.$key.'%')->orWhere('site', 'like', 'https://www.'.$key.'%')->exists();

        if ($media_crawler)
        {
            $query->module = 'news';
            $query->status = 'ok';

            $message = 'Haber kaynağı olarak takipte.';
        }
        else if ($blog_crawler)
        {
            $query->module = 'blog';
            $query->status = 'ok';

            $message = 'Blog kaynağı olarak takipte.';
        }
        else if ($sozluk_crawler)
        {
            $query->module = 'sozluk';
            $query->status = 'ok';

            $message = 'Sözlük kaynağı olarak takipte.';
        }
        else
        {
            $query->status = 'err';

            $message = 'Bağlantı hiçbir modülden takip edilmiyor.';
        }

        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'color' => $query->color(),
                'message' => $message
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, Sil
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        $query = DetectedDomains::where('id', $request->id)->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }
}
