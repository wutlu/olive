<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Crawlers\Sozluk\StatusRequest;
use App\Http\Requests\Crawlers\Sozluk\UpdateRequest;
use App\Http\Requests\Crawlers\Sozluk\DeleteRequest;

use App\Models\Crawlers\SozlukCrawler;

use App\Jobs\Elasticsearch\CreateSozlukIndexJob;
use App\Jobs\Elasticsearch\DeleteIndexJob;
use App\Jobs\KillProcessJob;
use App\Jobs\Crawlers\Sozluk\TriggerJob as SozlukTriggerJob;

use App\Utilities\Crawler;

use App\Elasticsearch\Indices;

class SozlukController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sözlük Botları, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function listView()
    {
        return view('crawlers.sozluk.list');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sözlük Modülü, bot listesi.
     *
     * @return array
     */
    public static function listViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new SozlukCrawler;
        $query = $request->string ? $query->orWhere('name', 'ILIKE', '%'.$request->string.'%')
                                          ->orWhere('site', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('status', 'ASC')
                       ->orderBy('max_attempt', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sözlük Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function allStatistics()
    {
        $sozluk_crawler = new SozlukCrawler;

        return [
            'status' => 'ok',
            'data' => [
                'count' => [
                    'active' => $sozluk_crawler->where('status', true)->count(),
                    'disabled' => $sozluk_crawler->where('status', false)->count()
                ],
                'elasticsearch' => Indices::stats([ 'sozluk', '*' ])
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sözlük Modülü, çalışmayan botları başlatma tetikleyicisi.
     *
     * @return array
     */
    public static function allStart()
    {
        $crawlers = SozlukCrawler::where(
            [
                'status' => false,
                'elasticsearch_index' => true,
                'test' => true
            ]
        )->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                SozlukTriggerJob::dispatch($crawler->id)->onQueue('trigger');

                $crawler->update([ 'status' => true ]);
            }
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Sözlük Modülü, çalışan botları durdurma tetikleyicisi.
     *
     * @return array
     */
    public static function allStop()
    {
        $crawlers = SozlukCrawler::where('status', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                KillProcessJob::dispatch($crawler->pid)->onQueue('trigger');

                $crawler->pid = null;
                $crawler->status = false;
                $crawler->save();
            }
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, Elasticsearch, eksik tüm indexleri oluşturma tetikleyicisi.
     * - Indexlerin tetiklenmesi için botların test edilmiş olması gerekir.
     *
     * @return array
     */
    public static function allIndex()
    {
        $crawlers = SozlukCrawler::where('elasticsearch_index', false)->where('test', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                CreateSozlukIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
            }
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot silme tetikleyicisi.
     *
     * @return array
     */
    public static function delete(DeleteRequest $request)
    {
        $crawler = SozlukCrawler::where('id', $request->id)->delete();

        DeleteIndexJob::dispatch([ 'sozluk', $request->id ])->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function statistics(int $id)
    {
        $crawler = SozlukCrawler::where('id', $id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => [
                'crawler' => $crawler,
                'pid' => $crawler->pid,
                'elasticsearch' => Indices::stats([ 'sozluk', $crawler->id ])
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot detayları sayfası.
     *
     * @return view
     */
    public static function view(int $id = 0)
    {
        if ($id)
        {
            $crawler = SozlukCrawler::where('id', $id)->firstOrFail();
        }
        else
        {
            $crawler = new SozlukCrawler;
            $crawler->name = 'Yeni Bot '.rand(99999, 999999);
            $crawler->site = 'http://';
            $crawler->selector_title = 'h1#title';
            $crawler->selector_entry = '.content';
            $crawler->selector_author = '.entry-author';
            $crawler->save();

            return redirect()->route('crawlers.sozluk.bot', $crawler->id);
        }

        return view('crawlers.sozluk.view', compact('crawler'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot oluştur veya bot güncelle.
     *
     * @return array
     */
    public static function update(UpdateRequest $request)
    {
        $crawler = SozlukCrawler::where('id', $request->id)->first();

        $data['status'] = 'err';

        $total = 0;
        $accepted = 0;

        for ($i = $request->last_id; $i < $request->last_id+$request->test_count; $i++)
        {
            $item = Crawler::entryDetection(
                [
                    'site' => $request->site,
                    'url_pattern' => $request->url_pattern,
                    'selector_title' => $request->selector_title,
                    'selector_entry' => $request->selector_entry,
                    'selector_author' => $request->selector_author,
                    'cookie' => json_decode($request->cookie, true)
                ],
                $i,
                $request->proxy ? true : false
            );

            $data['items'][] = $item;

            if ($item->status == 'ok')
            {
                $accepted++;
            }

            $total++;
        }

        if ($accepted > $total/3)
        {
            $crawler->fill($request->all());
            $crawler->proxy = $request->proxy ? true : false;
            $crawler->test = true;
            $crawler->off_reason = null;
            $crawler->status = false;
            $crawler->cookie = json_decode($request->cookie);

            $data['status'] = 'ok';

            CreateSozlukIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');

            if ($crawler->pid)
            {
                KillProcessJob::dispatch($crawler->pid)->onQueue('trigger');
            }
        }

        $crawler->save();

        return $data;
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot durumu değiştirme.
     * - Çalışan botu durdur.
     * - Durmuş botu çalıştır.
     *
     * @return array
     */
    public static function status(StatusRequest $request)
    {
        $crawler = SozlukCrawler::where('id', $request->id)->first();

        if ($crawler->status)
        {
            $crawler->status = false;

            if ($crawler->pid)
            {
                KillProcessJob::dispatch($crawler->pid)->onQueue('trigger');

                $crawler->pid = null;
            }
        }
        else
        {
            SozlukTriggerJob::dispatch($crawler->id)->onQueue('trigger');

            $crawler->update([ 'status' => true ]);
        }

        $crawler->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $crawler->status
            ]
        ];
    }
}
