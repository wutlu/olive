<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\DemoRequest;
use App\Http\Requests\ReportRequest;

use App\Models\User\User;
use App\Models\User\UserActivity;
use App\Models\User\UserIntro;
use App\Models\User\PartnerPayment;
use App\Models\Option;
use App\Models\Carousel;
use App\Models\Ticket;
use App\Models\Forum\Message;
use App\Models\Organisation\Organisation;
use App\Models\Organisation\OrganisationInvoice;
use App\Models\ReportedContents;
use App\Models\DetectedDomains;

use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\BlogCrawler;
use App\Models\Report;

use YouTube;
use System;
use App\Elasticsearch\Document;
use App\Utilities\Term;

class HomeController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth')->only([
            'dashboard',
            'organisation',
            'activity',
            'intro',
            'alert',
            'monitor',
            'sources',
            'termVersion'
        ]);
    }

    /**
     * ip log
     *
     * @return mixed
     */
    public static function ipLog(string $r, Request $request)
    {
        $ip = $request->ip();

        $line = date('Y-m-d H:i:s').' - '.$ip.' - '.$r;

        file_put_contents(public_path('ipLogs.txt'), $line.PHP_EOL , FILE_APPEND | LOCK_EX);

        return '404 not found';
    }

    /**
     * Kullanım Koşulları ve Gizlilik Politikası kabulu.
     *
     * @return array
     */
    public static function termVersion()
    {
        auth()->user()->update([ 'term_version' => config('system.term_version') ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Demo istek formu.
     *
     * @return array
     */
    public static function demoRequest(DemoRequest $request)
    {
        $ticket = new Ticket;
        $ticket->user_id = config('app.user_id_support');
        $ticket->status = 'open';

        $ticket->subject = 'Bilgi Bırakıldı';
        $ticket->message = implode(
            PHP_EOL,
            [
                $request->name,
                $request->corporate_name ? $request->corporate_name : 'Şirket Yok',
                $request->phone
            ]
        );
        $ticket->type = 'organisayon-teklifi';

        $ticket->save();
        $ticket->id = $ticket->id.rand(100, 999);
        $ticket->save();

        Option::where('key', 'root_alert.support')->first()->incr();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Rapor istek formu.
     *
     * @return array
     */
    public static function reportRequest(ReportRequest $request)
    {
        $ticket = new Ticket;
        $ticket->user_id = config('app.user_id_support');
        $ticket->status = 'open';

        $ticket->subject = 'Rapor İstenildi';
        $ticket->message = implode(
            PHP_EOL,
            [
                $request->subject,
                $request->name,
                $request->phone
            ]
        );
        $ticket->type = 'diger';

        $ticket->save();
        $ticket->id = $ticket->id.rand(100, 999);
        $ticket->save();

        Option::where('key', 'root_alert.support')->first()->incr();

        $exists = Report::where('gsm', $request->phone)->exists();

        if ($exists)
        {
            return [
                'status' => 'err',
                'message' => 'Daha önce bir rapor istediniz.'
            ];
        }
        else
        {
            $user = User::where('id', config('app.user_id_support'))->first();

            if (@$user && $user->organisation_id)
            {
                $report = new Report;
                $report->key = time().rand(1, 10).rand(10, 100).rand(1000, 1000000);
                $report->name = 'Örnek Otomatik Rapor';
                $report->date_1 = date('d-m-Y', strtotime('-8 days'));
                $report->date_2 = date('d-m-Y', strtotime('-1 days'));
                $report->organisation_id = $user->organisation_id;
                $report->user_id = $user->id;
                $report->password = rand(1000, 9999);
                $report->status = 'creating';
                $report->gsm = $request->phone;
                $report->subject = $request->subject;
                $report->save();
            }
            else
            {
                System::log(
                    'ENV dosyasında belirtilen destek hesabına erişilemiyor.',
                    'App\Http\Controllers\HomeController::reportRequest()',
                    10
                );
            }
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Kaynaklar Sayfası
     *
     * @return view
     */
    public static function sources()
    {
        $media = MediaCrawler::select('id', 'name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();
        $shopping = ShoppingCrawler::select('id', 'name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();
        $sozluk = SozlukCrawler::select('id', 'name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();
        $blog = BlogCrawler::select('id', 'name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();

        $options_query = Option::whereIn('key', [
            'youtube.status',
            'twitter.status',
            'instagram.status',
            'trend.status.google',
        ])->get();

        $options = [];

        foreach ($options_query as $option)
        {
            $options[$option->key] = $option->value;
        }

        return view('sources', compact(
            'media',
            'shopping',
            'sozluk',
            'blog',
            'options'
        ));
    }

    /**
     * Uyarı Sayfası
     *
     * @return mixed
     */
    public static function alert()
    {
        return session('alert') ? view('alert') : redirect()->route('dashboard');
    }

    /**
     * Manifest.json
     *
     * @return json
     */
    public static function manifest()
    {
        return json_encode([
            'name' => config('app.name'),
            'icons' => [
                [
                    'src' => '/favicons/android-chrome-192x192.png?v='.config('system.version'),
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/favicons/android-chrome-512x512.png?v='.config('system.version'),
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ],
            'theme_color' => config('view.theme_color'),
            'background_color' => config('view.background_color'),
            'display' => 'standalone',
            'scope' => '/',
            'start_url' => '/',
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Site Ana Sayfası
     *
     * @return view
     */
    public static function index(string $type = '')
    {
        $array = [
            [
                'title' => 'Eş Zamanlı Veri Akışları',
                'text' => 'Bu konuda iddialıyız! Siz paylaşımları okurken, gündemi kaçırmayın diye en iyi aracı geliştirdik.',
                'icon' => asset('img/icons/realtime.png')
            ],
            [
                'title' => 'Eş Zamanlı Trendler',
                'text' => 'Ülkemizde olup bitenlere haber ajanslarından daha önce ulaşabilirsiniz.',
                'icon' => asset('img/icons/trends.png')
            ],
            [
                'title' => 'Eş Zamanlı Arama Motoru',
                'text' => 'Şimdiyi ve geçmişi tek bir arama motorunda birleştirdik. Bilineni değiştirdik ve gelişmiş bir arama motoru tasarladık.',
                'icon' => asset('img/icons/search.png')
            ],
            [
                'title' => 'Eş Zamanlı Grafikler',
                'text' => 'Dün veya geçen haftanın yanında bugünün grafikleri de oluşturabilirsiniz.',
                'icon' => asset('img/icons/analytics.png')
            ],
            [
                'title' => 'Eş Zamanlı Alarmlar',
                'text' => 'Olan biteni okumak için vaktiniz yok mu? Alarm kurun, gündemler oluşmadan e-posta bildirimleri alın.',
                'icon' => asset('img/icons/alarm.png')
            ],
            [
                'title' => 'Olive Rapor Editörü',
                'text' => 'Araştırma yaparken sadece tıklamalar ile eş zamanlı ve hızlı raporlar oluşturun.',
                'icon' => asset('img/icons/analytics.png')
            ],
            [
                'title' => 'Hızlı Kayıt',
                'text' => 'Kullanımı kolay arayüzümüz sayesinde, herhangi bir ekranda gördüğünüz ve daha sonra ilgilenmeniz gereken verileri anında pinleyerek kaydedebilirsiniz.',
                'icon' => asset('img/icons/pin.png')
            ],
            [
                'title' => 'Olive Sıralamaları',
                'text' => 'Takipçi sayılarına göre yapılan anlamsız influencer sıralamalarını ilkel buluyoruz. Üstün Olive algoritmalarıyla daha farklı ve daha anlamlı sıralamalar tasarladık.',
                'icon' => asset('img/icons/counter.png')
            ],
            [
                'title' => 'Yerel Haberler',
                'text' => 'Haber kaynaklarını istediğiniz şekilde gruplayarak, ister yerel, ister daha özel haber verileri elde edebilirsiniz.',
                'icon' => asset('img/icons/news.png')
            ],
            [
                'title' => 'Bloglar ve Sözlükler',
                'text' => 'Blog ve Sözlük yazarlarının paylaşımlarını da tüm akışa dahil edebilirsiniz.',
                'icon' => asset('img/icons/microsoft-planner.png')
            ],
            [
                'title' => 'En İyi Filtreler',
                'text' => 'İster kelime, ister cümle olarak arayın veya 50\'den fazla ve her geçen gün artmakta olan filtrelerle, yapacağınız aramaları istediğiniz kadar daraltın.',
                'icon' => asset('img/icons/filter.png')
            ],
            [
                'title' => 'Rakip İncelemesi',
                'text' => 'Rakiplerinizi inceleyin ve performanslarını ölçümleyin, kampanyalarını analiz edin, içgörüler edinin.',
                'icon' => asset('img/icons/star-trek-symbol.png')
            ],
            [
                'title' => 'Excel Desteği',
                'text' => 'Analizleri ve grafikleri tek tuşla dışa aktarın! Sonuç ve grafikleri gerektiğinde Excel, gerektiğinde görsel formatında indirebilirsiniz.',
                'icon' => asset('img/icons/data-sheet.png')
            ],
            [
                'title' => 'Kaynaklar',
                'text' => 'Araştırmalarınız sonucu elde ettiğiniz değerlerden emin olmak için, tüm verileri tek tek veya gruplar halinde inceleyebilirsiniz.',
                'icon' => asset('img/icons/innovation.png')
            ],
            [
                'title' => 'Sınırsız Sorgu',
                'text' => 'Aboneliğiniz boyunca istediğiniz kadar sorgu ve analiz gerçekleştirebilirsiniz.',
                'icon' => asset('img/icons/infinity.png')
            ],
            [
                'title' => 'Sınırsız Sonuç',
                'text' => 'Örümceklerimizin ulaştığı tüm verilere hiçbir sınırlama olmaksızın ulaşabilirsiniz.',
                'icon' => asset('img/icons/report-card.png')
            ],
            [
                'title' => 'Anlamlı Sonuç',
                'text' => 'İyi Sonuç® algoritmamız ile anlamsız içerikleri arama sonuçlarından çıkartarak veri madenciliğini daha üst seviyelere taşıyabilirsiniz.',
                'icon' => asset('img/icons/light-on.png')
            ],
            [
                'title' => 'Akıllı Algoritmalar',
                'text' => 'Sürekli öğrenen algoritmalar sayesinde, arama sonuçlarınız her geçen gün daha iyi hale gelir.',
                'icon' => asset('img/icons/artificial-intelligence.png')
            ],
            [
                'title' => 'Ortak Veri Havuzu',
                'text' => 'Olive, veri mecralarını tarar ve elde ettiği verileri bir havuzda toplar. Ulaşamadığımız mecraları belirterek siz de bu havuza katkı sağlayabilirsiniz.',
                'icon' => asset('img/icons/network.png')
            ],
            [
                'title' => 'Trend Arşivi',
                'text' => 'Saatlik, Günlük, Haftalık, Aylık ve Yıllık Trendleri arşivliyoruz. Bu arşivlere her an erişebilirsiniz.',
                'icon' => asset('img/icons/archive.png')
            ],
            [
                'title' => 'Güncel Teknoloji',
                'text' => 'Her geçen gün yeni modüller ve özellikler üretiyoruz. Tüm özelliklerimizi, güncel teknolojiye ve ihtiyaçlarınıza göre geliştirmeye devam ediyoruz.',
                'icon' => asset('img/icons/apple-watch.png')
            ],
        ];

        $types = [
            'kisiler' => [
                'key' => 'kisiler',
                'title' => 'Kişiler İçin',
                'description' => 'Alanınızdaki rekabeti eş zamanlı takip edin!',
                'image' => asset('img/photo/1500x1500@person.jpg'),
            ],
            'markalar' => [
                'key' => 'markalar',
                'title' => 'Markalar İçin',
                'description' => 'Rakiplerinizi hızlı bir şekilde inceleyin, dijitaldeki itibarınızdan her zaman haberdar olun!',
                'image' => asset('img/photo/1500x1500@brand.jpg'),
            ],
            'reklam-ajanslari' => [
                'key' => 'reklam-ajanslari',
                'title' => 'Reklam Ajansları İçin',
                'description' => 'Müşterilerinizin dijital meleği olun!',
                'image' => asset('img/photo/1500x1500@agency.jpg'),
            ]
        ];

        if ($type)
        {
            $type = $types[$type];
        }

        return view('home.index', compact('array', 'type', 'types'));
    }

    /**
     * Tam Veri Sayısı
     *
     * @return array
     */
    public static function dataCounter()
    {
        $counts = RedisCache::get(implode(':', [ config('system.db.alias'), 'documents', 'total' ]));

        return [
            'status' => 'ok',
            'data' => [
                'count' => number_format($counts)
            ]
        ];
    }

    /**
     * Portal Ana Sayfası
     *
     * @return view
     */
    public static function dashboard(int $pager = 10)
    {
        $carousels = Carousel::where('carousel', true)->orderBy('updated_at', 'DESC')->get();
        $modals = Carousel::where('modal', true)->orderBy('updated_at', 'DESC')->get();

        $threads = Message::whereNull('message_id')->orderBy('updated_at', 'DESC')->simplePaginate($pager);

        $photos = [
            [
                'img' => asset('img/photo/galata.jpeg'),
                'text' => 'Galata Kulesi / İstanbul'
            ],
            [
                'img' => asset('img/photo/bogaz.jpeg'),
                'text' => 'İstanbul Boğazı / İstanbul'
            ]
        ];

        shuffle($photos);

        $photo = $photos[0];

        $news = json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', 'news' ])));

        return view(
            'dashboard',
            compact(
                'carousels',
                'modals',
                'photo',
                'threads',
                'news'
            )
        );
    }

    /**
     * Portal, Organizasyon Bilgileri
     *
     * @return json
     */
    public static function organisation()
    {
        $organisation = auth()->user()->organisation;

        $users = [];

        foreach ($organisation->users as $user)
        {
            $users[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar(),
                'online' => $user->online()
            ];
        }

        return [
            'status' => 'ok',
            'users' => $users,
            'organisation' => [
                'name' => $organisation->name,
                'user_capacity' => $organisation->user_capacity,
                'days' => $organisation->days(),
                'status' => $organisation->status,
                'author' => $organisation->user_id,
            ]
        ];
    }

    /**
     * Aktiviteler
     *
     * @return array
     */
    public static function activity(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new UserActivity;
        $query = $query->where('user_id', auth()->user()->id);
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     * Introyu Geç
     *
     * @return array
     */
    public static function intro(string $key)
    {
        $query = UserIntro::firstOrCreate([ 'user_id' => auth()->user()->id, 'key' => $key ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Bildirim Monitörü
     *
     * @return array
     */
    public static function monitor()
    {
        $data = [
            'ticket' => [
                'count' => 0
            ],
            'push_notifications' => []
        ];

        $user = auth()->user();

        if ($user->admin())
        {
            $data['ticket']['count'] = Option::where('key', 'root_alert.support')->value('value');
            $data['organisation']['pending']['count'] = Organisation::where('updated_at', '>=', date('Y-m-d').' 00:00:00')->where('status', false)->count();
            $data['organisation']['invoices']['count'] = OrganisationInvoice::whereNull('paid_at')->count();
            $data['partner']['payments']['count'] = PartnerPayment::where('status', 'pending')->count();
        }

        if ($user->root())
        {
            $data['reported_contents']['count'] = ReportedContents::count();
            $data['detected_domains']['count'] = DetectedDomains::where('status', 'new')->count();
        }

        if ($user->report_id)
        {
            $user->report['route'] = route('report.view', $user->report->key);

            $data['report'] = $user->report;
        }

        $activities = UserActivity::where('user_id', $user->id)->where('push_notification', 'on')->limit(3)->get();

        if (count($activities))
        {
            foreach ($activities as $activity)
            {
                $data['push_notifications'][] = [
                    'title' => $activity->title,
                    'button' => $activity->button_text ? [
                        'action' => $activity->button_action,
                        'class' => $activity->button_class,
                        'text' => $activity->button_text,
                    ] : false
                ];

                $activity->push_notification = 'ok';
                $activity->save();
            }
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }
}
