<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\Report\StartRequest;
use App\Http\Requests\Report\StatusRequest;
use App\Http\Requests\Report\UpdateRequest ;
use App\Http\Requests\Report\PageRequest;
use App\Http\Requests\Report\DataRequest;
use App\Http\Requests\Report\AggsRequest;

use App\Models\Report;
use App\Models\ReportPage;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SortRequest;

use App\Utilities\UserActivityUtility;

use App\Elasticsearch\Document;

class ReportController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth')->except('view');

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'organisation:have,module_report',
            'can:organisation-status'
        ])->except([
            'view'
        ]);
    }

    /**
     * Rapor, Bitmiş Raporlar
     *
     * @return view
     */
    public static function dashboard(Request $request, int $pager = 10)
    {
        $request->validate([
            'q' => 'nullable|string|max:100'
        ]);

        $user = auth()->user();

        $data = Report::where('organisation_id', $user->organisation_id);

        if ($request->q)
        {
            $data = $data->where('name', 'ILIKE', '%'.$request->q.'%');
        }

        $data = $data->orderBy('id', 'DESC')->paginate($pager);

        $q = $request->q;

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('report.dashboard');
        }

        return view('report.dashboard', compact('data', 'q', 'pager'));
    }

    /**
     * Rapor Kontrolü
     * - Rapor varsa durdurmak için uyarı çıkarır.
     * - Yoksa başlat formunu açar.
     *
     * @return array
     */
    public static function status(StatusRequest $request)
    {
        $request->validate([
            'validate' => 'nullable|string|in:on'
        ]);

        $user = auth()->user();

        if ($user->report_id && $request->validate)
        {
            $report = $user->report;
            $report->password = $request->report_password ? $request->report_password : null;
            $report->date_1 = $request->report_date_1 ? $request->report_date_1 : null;
            $report->date_2 = $request->report_date_2 ? $request->report_date_2 : null;
            $report->save();

            $user->report_id = null;
            $user->save();

            if (!$user->badge(13))
            {
                ### [ analist rozeti ] ###
                $user->addBadge(13);
            }

            if ($user->reports->count() >= 100)
            {
                if (!$user->badge(14))
                {
                    ### [ veri gurusu rozeti ] ###
                    $user->addBadge(14);
                }
            }

            foreach ($user->organisation->users as $u)
            {
                UserActivityUtility::push(
                    'Bir Rapor Tamamlandı',
                    [
                        'key'       => implode('-', [ 'report', $user->organisation_id, 'complete' ]),
                        'icon'      => 'playlist_add_check',
                        'markdown'  => '['.$user->name.']('.route('user.profile', $user->id).') bir rapor tamamladı.',
                        'user_id'   => $u->id,
                        'button'    => [
                            'action' => route('report.view', $report->key),
                            'text'   => 'İncele',
                            'class'  => 'btn-flat waves-effect'
                        ]
                    ]
                );
            }
        }

        return [
            'status' => 'ok',
            'data' => [
                'status' => $user->report_id ? true : false,
                'validate' => $request->validate ? true : false
            ]
        ];
    }

    /**
     * Rapor Başlat
     *
     * @return array
     */
    public static function start(StartRequest $request)
    {
        $user = auth()->user();

        $report = new Report;
        $report->fill($request->all());
        $report->organisation_id = $user->organisation_id;
        $report->user_id = $user->id;
        $report->key = time().$user->id.$user->organisation_id.rand(1000, 1000000);
        $report->save();

        $user->report_id = $report->id;
        $user->save();

        return [
            'status' => 'ok',
            'data' => json_encode($report)
        ];
    }

    /**
     * Rapor, Görüntüle
     *
     * @return view
     */
    public static function view(Request $request)
    {
        $report = Report::where('key', $request->key)->firstOrFail();

        $authenticate = null;

        if ($report->password)
        {
            if ($request->isMethod('post'))
            {
                $request->validate([
                    'password' => 'required|string|max:32',
                    'g-recaptcha-response' => 'required|recaptcha'
                ]);

                $authenticate = true;

                $report->increment('hit_with_password');
            }
            else
            {
                $authenticate = false;
            }
        }

        if ($authenticate === null || $authenticate === true)
        {
            $report->increment('hit');
        }

        $anchors = [ 'giris' ];
        $titles = [ 'Giriş' ];

        for ($i = 1; $i <= $report->pages->count(); $i++)
        {
            $anchors[] = 'sayfa-'.$i;
        }

        foreach ($report->pages as $page)
        {
            $titles[] = $page->title;
        }

        $anchors[] = 'olive';
        $titles[] = 'Olive';

        $anchors = array_values($anchors);

        return view('report.view', compact('report', 'authenticate', 'request', 'anchors', 'titles'));
    }

    /**
     * Rapor, Düzenle
     *
     * @return view
     */
    public static function edit(int $id)
    {
        $report = Report::where('id', $id)->where('organisation_id', auth()->user()->organisation_id)->firstOrFail();
        $pages = $report->pages;
        $types = [
            'page.title' => 'Başlık',
            'page.lines' => 'Satır',
            'data.article' => 'Haber',
            'data.tweet' => 'Twitter',
            'data.entry' => 'Sözlük',
            'data.media' => 'Instagram',
            'data.document' => 'Blog',
            'data.comment' => 'YouTube Yorum',
            'data.video' => 'YouTube Video',
            'data.product' => 'E-ticaret',
            'data.stats' => 'İstatistik',
            'data.chart' => 'Grafik',
            'data.gender' => 'Cinsiyet Dağılımı',
            'data.tr_map' => 'Türkiye Haritası',
            'data.user_description' => 'Profil Bulutu',

            'data.twitterMentions' => 'Twitter Bahsedilen Hesaplar',
            'data.twitterInfluencers' => 'Twitter Takipçi Sırasına Göre Hesaplar',
            'data.twitterUsers' => 'Twitter, En Çok Tweet Paylaşan Hesaplar',
            'data.youtubeUsers' => 'YouTube, En Çok Video Yükleyen Hesaplar',
            'data.youtubeComments' => 'YouTube, En Çok Yorum Yapan Hesaplar',
            'data.sozlukSites' => 'Sözlükler',
            'data.sozlukUsers' => 'Sözlük Yazarları',
            'data.sozlukTopics' => 'Sözlük Başlıkları',
            'data.newsSites' => 'Haber Siteleri',
            'data.blogSites' => 'Blog Siteleri',
            'data.shoppingSites' => 'E-ticaret Siteleri',
            'data.shoppingUsers' => 'E-ticaret Satıcıları',
        ];

        return view('report.edit', compact('report', 'pages', 'types'));
    }

    /**
     * Rapor, Düzenle Kayıt
     *
     * @return array
     */
    public static function editSave(int $id, UpdateRequest $request)
    {
        $report = Report::where('id', $id)->where('organisation_id', auth()->user()->organisation_id)->firstOrFail();
        $report->name = $request->name;
        $report->date_1 = $request->date_1;
        $report->date_2 = $request->date_2;
        $report->password = $request->password;
        $report->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Rapor, Sil
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        $user = auth()->user();

        $report = Report::where('id', $request->id)->where('organisation_id', $user->organisation_id)->firstOrFail();

        foreach ($user->organisation->users as $u)
        {
            UserActivityUtility::push(
                'Bir Rapor Silindi',
                [
                    'key'       => implode('-', [ 'report', $user->organisation_id, 'deleted' ]),
                    'icon'      => 'playlist_add_check',
                    'markdown'  => '['.$user->name.']('.route('user.profile', $user->id).'), "'.$report->name.'" başlıklı raporu sildi.',
                    'user_id'   => $u->i
                ]
            );
        }

        $report->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }

    /**
     * Rapor, Sayfa
     *
     * @return array
     */
    public static function page(int $id)
    {
        $page = ReportPage::where('id', $id)->firstOrFail();
        $report = $page->report()->where('organisation_id', auth()->user()->organisation_id)->exists();

        if (!$report)
        {
            return abort(404);
        }

        $data = $page->data;

        unset($page['data']);

        $data = [
            'status' => 'ok',
            'page' => $page,
            'data' => $data
        ];

        if ($page->type == 'data.stats')
        {
            $data['stats'] = $data['data'];

            unset($data['data']);
        }

        return $data;
    }

    /**
     * Rapor, Sayfa Oluştur
     *
     * @return array
     */
    public static function pageCreate(PageRequest $request)
    {
        $user = auth()->user();

        if ($user->report_id)
        {
            $sort = intval(ReportPage::where('report_id', $user->report_id)->orderBy('sort', 'desc')->value('sort'))+1;

            $page = new ReportPage;
            $page->title = $request->title;
            $page->subtitle = $request->subtitle;
            $page->text = $request->text;
            $page->data = json_decode($request->lines);
            $page->type = $request->lines || $request->text ? 'page.lines' : 'page.title';
            $page->report_id = $user->report_id;
            $page->sort = $sort;
            $page->save();
        }
        else
        {
            return [
                'status' => 'flash-alert',
                'text' => 'İlk önce bir rapor başlatmalısınız!',
                'class' => 'red white-text'
            ];
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Rapor, Sayfa Güncelle
     *
     * @return array
     */
    public static function pageUpdate(int $id, PageRequest $request)
    {
        $page = ReportPage::where('id', $id)->firstOrFail();

        $user = auth()->user();

        $report = $page->report()->where('organisation_id', $user->organisation_id)->exists();

        if ($report)
        {
            $page->title = $request->title;
            $page->subtitle = $request->subtitle;
            $page->text = $request->text;
            $page->data = json_decode($request->lines);
            $page->type = $request->lines || $request->text ? 'page.lines' : 'page.title';
            $page->save();

            return [
                'status' => 'ok',
                'data' => $page
            ];
        }
        else
        {
            return abort(404);
        }
    }

    /**
     * Rapor, Sayfa Sıralamasi
     *
     * @return array
     */
    public static function pageSort(SortRequest $request)
    {
        $user = auth()->user();
        $report = Report::where('id', $request->id)->where('organisation_id', $user->organisation_id)->firstOrFail();

        if ($report->pages->count() == count($request->ids))
        {
            $i = 1;

            foreach ($request->ids as $key => $id)
            {
                $page = ReportPage::where('id', $id)->where('report_id', $report->id)->first();

                if (@$page)
                {
                    $page->sort = $i;
                    $page->save();

                    $i++;
                }
            }

            return [
                'status' => 'ok'
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'Sayfa sayısı güncel değil. Lütfen sayfayı yenileyin ve tekrar deneyin.'
            ];
        }
    }

    /**
     * Rapor, Sayfa Sil
     *
     * @return array
     */
    public static function pageDelete(IdRequest $request)
    {
        $page = ReportPage::where('id', $request->id)->firstOrFail();

        $user = auth()->user();

        $report = $page->report()->where('organisation_id', $user->organisation_id)->exists();

        if ($report)
        {
            $page->delete();
        }
        else
        {
            return abort(404);
        }

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }

    /**
     * Rapor, İçerik Sayfası Oluştur
     *
     * @return array
     */
    public static function dataCreate(DataRequest $request)
    {
        $user = auth()->user();

        if ($user->report_id)
        {
            $document = Document::get(
                $request->index ? $request->index : 'test',
                $request->type ? $request->type : 'test',
                $request->id ? $request->id : 'test'
            );

            if ($document->status == 'ok')
            {
                $sort = intval(ReportPage::where('report_id', $user->report_id)->orderBy('sort', 'desc')->value('sort'))+1;

                switch ($request->type)
                {
                    case 'comment':
                        $data = $document->data['_source'];

                        $video = Document::get([ 'youtube', 'videos' ], 'video', $document->data['_source']['video_id']);

                        if ($video->status == 'ok')
                        {
                            $data['video'] = $video->data['_source'];
                        }
                    break;
                    case 'tweet':
                        $data = $document->data['_source'];

                        if (@$document->data['_source']['external']['id'])
                        {
                            $original = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                                'query' => [
                                    'bool' => [ 'must' => [ 'match' => [ 'id' => $document->data['_source']['external']['id'] ] ] ]
                                ]
                            ]);

                            if ($original->status == 'ok' && @$original->data['hits']['hits'][0])
                            {
                                $data['original'] = $original->data['hits']['hits'][0]['_source'];
                            }
                        }
                    break;
                    case 'media':
                        $data = $document->data['_source'];

                        if (@$document->data['_source']['user']['id'])
                        {
                            $external = Document::get([ 'instagram', 'users' ], 'user', $document->data['_source']['user']['id']);

                            if ($external->status == 'ok')
                            {
                                $data['user'] = $external->data['_source'];
                            }
                        }
                    break;
                    default:
                        $data = $document->data['_source'];
                    break;
                }

                $page = new ReportPage;
                $page->title = $request->title;
                $page->subtitle = $request->subtitle;
                $page->text = $request->text;
                $page->data = $data;
                $page->type = implode('.', [ 'data', $request->type ]);
                $page->report_id = $user->report_id;
                $page->sort = $sort;
                $page->save();
            }
            else
            {
                return abort(404);
            }
        }
        else
        {
            return [
                'status' => 'flash-alert',
                'text' => 'İlk önce bir rapor başlatmalısınız!',
                'class' => 'red white-text'
            ];
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Rapor, İçerik Sayfası Güncelle
     *
     * @return array
     */
    public static function dataUpdate(int $id, DataRequest $request)
    {
        $page = ReportPage::where('id', $id)->firstOrFail();

        $user = auth()->user();

        $report = $page->report()->where('organisation_id', $user->organisation_id)->exists();

        if ($report)
        {
            $page->title = $request->title;
            $page->subtitle = $request->subtitle;
            $page->text = $request->text;
            $page->save();

            return [
                'status' => 'ok',
                'data' => $page
            ];
        }
        else
        {
            return abort(404);
        }
    }

    /**
     * Rapor, İstatistik Sayfası Oluştur
     *
     * @return array
     */
    public static function aggsCreate(AggsRequest $request)
    {
        $user = auth()->user();

        if ($user->report_id)
        {
            $sort = intval(ReportPage::where('report_id', $user->report_id)->orderBy('sort', 'desc')->value('sort'))+1;

            $page = new ReportPage;
            $page->title = $request->title;
            $page->subtitle = $request->subtitle;
            $page->text = $request->text;
            $page->data = json_decode($request->data);
            $page->type = implode('.', [ 'data', $request->type ]);
            $page->report_id = $user->report_id;
            $page->sort = $sort;
            $page->save();
        }
        else
        {
            return [
                'status' => 'flash-alert',
                'text' => 'İlk önce bir rapor başlatmalısınız!',
                'class' => 'red white-text'
            ];
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Rapor, İstatistik Sayfası Güncelle
     *
     * @return array
     */
    public static function aggsUpdate(int $id, AggsRequest $request)
    {
        $page = ReportPage::where('id', $id)->firstOrFail();

        $user = auth()->user();

        $report = $page->report()->where('organisation_id', $user->organisation_id)->exists();

        if ($report)
        {
            $page->title = $request->title;
            $page->subtitle = $request->subtitle;
            $page->text = $request->text;
            $page->save();

            return [
                'status' => 'ok',
                'data' => $page
            ];
        }
        else
        {
            return abort(404);
        }
    }
}
