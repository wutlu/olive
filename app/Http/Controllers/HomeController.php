<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\DemoRequest;

use App\Models\User\UserActivity;
use App\Models\User\UserIntro;
use App\Models\User\PartnerPayment;
use App\Models\Option;
use App\Models\Carousel;
use App\Models\Ticket;
use App\Models\Forum\Message;
use App\Models\Organisation\Organisation;
use App\Models\Organisation\OrganisationInvoice;

use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\BlogCrawler;

use YouTube;
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

        $ticket->subject = 'Demo İsteği';
        $ticket->message = '"'.$request->name.'" | "'.$request->phone.'"';
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
    public static function index()
    {
        return view('home');
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

        return view('dashboard', compact('carousels', 'modals', 'photo', 'threads'));
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
