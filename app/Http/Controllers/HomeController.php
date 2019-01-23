<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;

use App\Models\User\UserActivity;
use App\Models\Discount\DiscountDay;
use App\Models\User\UserIntro;
use App\Models\Option;
use App\Models\Carousel;

use App\Ticket;

use YouTube;

use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;
use App\Models\Crawlers\SozlukCrawler;

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
            'activity',
            'intro',
            'alert',
            'monitor',
            'sources'
        ]);
    }

    /**
     * veri.zone Ana Sayfa
     *
     * @return view
     */
    public static function vz()
    {
        return view('vz.home');
    }

    /**
     * Kullanım Koşulları ve Gizlilik Politikası kabulu.
     *
     * @return view
     */
    public static function termVersion()
    {
        auth()->user()->update([ 'term_version' => config('system.term_version') ]);

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
        $media = MediaCrawler::select('name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();
        $shopping = ShoppingCrawler::select('name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();
        $sozluk = SozlukCrawler::select('name', 'status', 'site')->where('test', true)->orderBy('id', 'DESC')->get();

        $options_query = Option::whereIn('key', [
            'youtube.status',
            'twitter.status',
            'google.status',
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
        $discountDay = DiscountDay::where('first_day', '<=', date('Y-m-d'))
                                  ->where('last_day', '>=', date('Y-m-d'))
                                  ->first();

        return view('home', compact('discountDay'));
    }

    /**
     * Portal Ana Sayfası
     *
     * @return json
     */
    public static function dashboard()
    {
        $user = auth()->user();

        $carousels = Carousel::where('carousel', true)->orderBy('updated_at', 'DESC')->get();
        $modals = Carousel::where('modal', true)->orderBy('updated_at', 'DESC')->get();

        return view('dashboard', compact('user', 'carousels', 'modals'));
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
        $query = $request->string ? $query->where('title', 'ILIKE', '%'.$request->string.'%') : $query;
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
            'partner' => [
                'count' => 0
            ],
            'push_notifications' => []
        ];

        $user = auth()->user();

        if ($user->root())
        {
            $data['ticket']['count'] = Option::where('key', 'root_alert.support')->value('value');
            $data['partner']['count'] = Option::where('key', 'root_alert.partner')->value('value');
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
