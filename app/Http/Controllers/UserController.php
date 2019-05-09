<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\PasswordGetRequest;
use App\Http\Requests\User\PasswordNewRequest;
use App\Http\Requests\User\UpdateRequest as AccountUpdateRequest;
use App\Http\Requests\User\AvatarRequest;
use App\Http\Requests\User\Admin\UpdateRequest as AdminUpdateRequest;
use App\Http\Requests\User\Admin\CreateRequest as AdminCreateRequest;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\AutocompleteRequest;

use App\Notifications\PasswordValidationNotification;
use App\Notifications\EmailValidationNotification;
use App\Notifications\WelcomeNotification;
use App\Notifications\NewPasswordNotification;
use App\Notifications\LoginNotification;
use App\Notifications\MessageNotification;

use App\Utilities\UserActivityUtility;

use App\Models\User\User;
use App\Models\User\UserNotification;
use App\Models\Option;

use Auth;
use Session;
use Jenssegers\Agent\Agent;
use Image;
use System;
use Carbon\Carbon;

class UserController extends Controller
{
	public function __construct()
	{
        /**
         ***** ZORUNLU *****
         *
         * - Ziyaretçi
         */
		$this->middleware('guest')->only([
			'registerPut',
			'loginPost',
			'loginView'
		]);

        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth')->only([
            'registerResend',
            'account',
            'accountUpdate',
            'notifications',
            'notificationUpdate',
            'avatar',
            'avatarUpload',
        ]);

        ### [ 5 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:5,5')->only('passwordPost');

        ### [ 10 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:10,1')->only('loginPost');

        ### [ 1 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:1,1')->only('registerResend');
	}

    /**
     * Forum, Kullanıcı Profili
     *
     * @return view
     */
    public static function profile(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.profile', compact('user'));
    }

    /**
     * Üyelik Formu
     *
     * @return view
     */
    public static function loginView()
    {
    	return view('user.logister');
    }

    /**
     * Üye Girişi
     *
     * @return array
     */
    public static function loginPost(LoginRequest $request)
    {
        $value = $request->value_login;
        $password = $request->password_login;

        $key = filter_var($value, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            $key = 'email';
            $value = strtolower($value);
        }
        else
        {
            $key = 'name';
        }

        if (Auth::attempt([ $key => $value, 'password' => $password ]))
        {
            $user = User::where($key, $value)->first();

            if ($user->banned_at)
            {
                auth()->logout();

                System::log(
                    'Yasaklı bir kullanıcı giriş denemesi yaptı.',
                    'App\Http\Controllers\UserController::loginPost('.$key.':'.$password.')', 1
                );

                return [
                    'status' => 'ban',
                    'data' => [
                        'reason' => $user->ban_reason
                    ]
                ];
            }

            $diffYears = Carbon::now()->diffInYears($user->created_at);

            if ($diffYears >= 5)
            {
                if (!$user->badge(10))
                {
                    ### [ 5 yıl dolduruldu ] ###
                    $user->addBadge(10);
                }
            }
            else if ($diffYears >= 4)
            {
                if (!$user->badge(9))
                {
                    ### [ 4 yıl dolduruldu ] ###
                    $user->addBadge(9);
                }
            }
            else if ($diffYears >= 3)
            {
                if (!$user->badge(8))
                {
                    ### [ 3 yıl dolduruldu ] ###
                    $user->addBadge(8);
                }
            }
            else if ($diffYears >= 2)
            {
                if (!$user->badge(7))
                {
                    ### [ 2 yıl dolduruldu ] ###
                    $user->addBadge(7);
                }
            }
            else if ($diffYears >= 1)
            {
                if (!$user->badge(6))
                {
                    ### [ 1 yıl dolduruldu ] ###
                    $user->addBadge(6);
                }
            }

            $request->session()->regenerate();

            $previous_session = $user->session_id;

            if ($previous_session)
            {
                $agent = new Agent();

                $agent_browser = $agent->browser();
                $agent_platform = $agent->platform();

                $browser['name'] = $agent_browser;

                if ($agent->version($agent_browser))
                {
                    $browser['version'] = $agent->version($agent_browser);
                }

                $os['name'] = $agent_platform;

                if ($agent->version($agent_platform))
                {
                    $os['version'] = $agent->version($agent_platform);
                }

                $info = (object) [
                    'ip' => $request->ip(),
                    'location' => geoip()->getLocation($request->ip),

                    'device' => $agent->device() ? $agent->device() : null,
                    'os' => implode(', ', $os),
                    'browser' => implode(', ', $browser),

                    'date' => date('Y-m-d H:i:s'),
                ];

                $data[] = '| Özellik         | Değer                                                  |';
                $data[] = '| --------------: | :----------------------------------------------------- |';
                $data[] = '| IP              | '.$info->ip.'                                          |';
                $data[] = '| Konum           | '.$info->location->city.'/'.$info->location->country.' |';

            if ($info->device)
            {
                $data[] = '| Cihaz           | '.$info->device.'                                      |';
            }

                $data[] = '| İşletim Sistemi | '.$info->os.'                                          |';
                $data[] = '| Tarayıcı        | '.$info->browser.'                                     |';
                $data[] = '| İşlem Tarihi    | '.$info->date.'                                        |';

                # --- [] --- #

                $data = implode(PHP_EOL, $data);

                UserActivityUtility::push(
                    'Hesabınıza yeni bir ip\'den giriş yapıldı.',
                    [
                        'key'       => implode('-', [ 'user', 'auth', $info->ip ]),
                        'icon'      => 'accessibility',
                        'markdown'  => $data
                    ]
                );

                if ($user->notification('login'))
                {
                    $user->notify(
                        (
                            new LoginNotification(
                                $user->name,
                                $data
                            )
                        )->onQueue('email')
                    );
                }

                Session::getHandler()->destroy($previous_session);
            }

            $user->session_id = Session::getId();
            $user->save();

            return [
                'status' => 'ok'
            ];
        }
        else
        {
            return response
            (
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'email_login' => [
                            'Geçersiz e-posta/şifre kombinasyonu.'
                        ],
                        'password_login' => [
                            'Geçersiz e-posta/şifre kombinasyonu.'
                        ]
                    ]
                ],
                422
            );
        }
    }

    /**
     * Yeni Şifre İstek
     *
     * @return array
     */
    public static function passwordGetPost(PasswordGetRequest $request)
    {
        $user = User::where('email', $request->email_password)->first();

        $user->notify((new PasswordValidationNotification($user->id, $user->session_id))->onQueue('email'));

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Yeni Şifre
     *
     * @return view
     */
    public static function passwordNew(string $id, string $sid)
    {
        $user = User::where([
            'id' => $id,
            'session_id' => $sid
        ])->firstOrFail();

        return view('user.password_new', compact('user'));
    }

    /**
     * Yeni Şifre Güncelle
     *
     * @return array
     */
    public static function passwordNewPatch(string $id, string $sid, PasswordNewRequest $request)
    {
        $user = User::where([
            'id' => $id,
            'session_id' => $sid
        ])->firstOrFail();

        if (strtolower($request->email) != $user->email)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'email' => [
                            'Geçerli e-posta adresiniz bu değil.'
                        ]
                    ]
                ],
                422
            );
        }

        $user->password = bcrypt($request->password);
        $user->session_id = Session::getId();
        $user->save();

        $text = 'Eğer bu işlem size ait değilse hemen yeni bir şifre oluşturun, veya destek ekibimizle iletişime geçin.';

        UserActivityUtility::push(
            'Hesap şifreniz güncellendi!',
            [
                'icon'      => 'check',
                'markdown'  => $text,
                'user_id'   => $user->id
            ]
        );

        $user->notify(
            (
                new NewPasswordNotification(
                    $user->name,
                    $text
                )
            )->onQueue('email')
        );

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Üyelik Onayı
     *
     * @return array
     */
    public static function registerValidate(string $id, string $sid)
    {
        $user = User::where([
            'id' => $id,
            'session_id' => $sid,
            'verified' => false
        ])->firstOrFail();

        $user->verified = true;
        $user->save();

        session()->flash('validate', 'ok');

        $text = 'E-posta adresiniz başarılı bir şekilde doğrulandı. İyi araştırmalar dileriz...';

        ### [ e-posta doğrulandı ] ###
        $user->addBadge(1);

        UserActivityUtility::push(
            'E-posta Doğrulandı!',
            [
                'icon'     => 'check',
                'markdown' => $text,
                'user_id'  => $user->id,
            ]
        );

        $user->notify(
            (
                new WelcomeNotification(
                    $user->name,
                    $text
                )
            )->onQueue('email')
        );

        # --- #

        return redirect()->route('dashboard');
    }

    /**
     * Yeni Üye Kayıt
     *
     * @return array
     */
    public static function registerPut(RegisterRequest $request)
    {
        if (config('system.user.registration'))
        {
            $user = new User;
            $user->name = $request->name;
            $user->password = bcrypt($request->password);
            $user->email = strtolower($request->email);
            $user->session_id = Session::getId();
            $user->term_version = config('system.term_version');

            $user->save();

            foreach (config('system.notifications') as $key => $val)
            {
                UserNotification::create([
                    'user_id' => $user->id,
                    'key' => $key
                ]);
            }

            $user->notify(
                (
                    new EmailValidationNotification(
                        $user->id,
                        $user->session_id,
                        $user->name
                    )
                )->onQueue('email')
            );

            Auth::login($user);

            return [
                'status' => 'ok'
            ];
        }
        else
        {
            return [
                'status' => 'err'
            ];
        }
    }

    /**
     * Yeni Doğrulama İsteği
     *
     * @return array
     */
    public static function registerResend()
    {
        $user = auth()->user();

        if ($user->verified)
        {
            return [
                'status' => 'err'
            ];
        }
        else
        {
            $user->notify((new EmailValidationNotification($user->id, $user->session_id, $user->name))->onQueue('email'));
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Çıkış
     *
     * @return array
     */
    public static function logout()
    {
        auth()->logout();

        return redirect()->route('user.login');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Güncelle
     *
     * @return view
     */
    public static function adminView(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.admin.view', compact('user'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Güncelle
     *
     * @return array
     */
    public static function adminUpdate(int $id, AdminUpdateRequest $request)
    {
        $user = User::where('id', $id)->firstOrFail();

        if ($user->moderator == false && $request->moderator == true)
        {
            ### [ moderatör rozeti ] ###
            $user->addBadge(997);
        }

        if ($user->root == false && $request->root == true)
        {
            ### [ root rozeti ] ###
            $user->addBadge(998);
        }

        $user->name = $request->name;
        $user->password = $request->password ? bcrypt($request->password) : $user->password;

        if (strtolower($request->email) != $user->email)
        {
            $user->email = strtolower($request->email);
        }

        $user->verified = $request->verified ? true : false;
        $user->avatar = $request->avatar ? null : $user->avatar;
        $user->root = $request->root ? true : false;
        $user->moderator = $request->moderator ? true : false;
        $user->about = $request->about ? $request->about : null;

        if ($request->ban_reason)
        {
            $user->ban_reason = $request->ban_reason;
            $user->banned_at = date('Y-m-d H:i:s');
        }
        else
        {
            $user->ban_reason = null;
            $user->banned_at = null;
        }

        $user->save();

        return [
            'status' => 'ok',
            'data' => $user
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı, Bildirim Durumları
     *
     * @return view
     */
    public static function adminNotifications(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.admin.notifications', compact('user'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı, Bildirim Durumu Güncelle
     *
     * @return view
     */
    public static function adminNotificationUpdate(int $id, Request $request)
    {
        $user = User::where('id', $id)->firstOrFail();

        $request->validate([
            'key' => 'string|in:'.implode(',', array_keys(config('system.notifications')))
        ]);

        if ($user->notification($request->key))
        {
            UserNotification::where([
                'user_id' => $id,
                'key' => $request->key
            ])->delete();
        }
        else
        {
            UserNotification::create([
                'user_id' => $id,
                'key' => $request->key
            ]);
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
     * Kullanıcı, Fatura Geçmişi
     *
     * @return view
     */
    public static function adminInvoiceHistory(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.admin.invoiceHistory', compact('user'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Destek Talepleri
     *
     * @return view
     */
    public static function adminTickets(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $tickets = $user->tickets();

        return view('user.admin.tickets', compact('user', 'tickets'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Listesi
     *
     * @return view
     */
    public static function adminListView()
    {
        return view('user.admin.list');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Listesi
     *
     * @return array
     */
    public static function adminListViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new User;
        $query = $request->string ? $query->where(function ($query) use ($request) {
                                    $query->orWhere('name', 'ILIKE', '%'.$request->string.'%')
                                          ->orWhere('email', 'ILIKE', '%'.$request->string.'%');
                                    }) : $query;

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC')
                       ->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => $total
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Bilgileri
     *
     * @return view
     */
    public static function account()
    {
        $user = auth()->user();

        return view('user.account', compact('user'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Bilgileri, Güncelle
     *
     * @return array
     */
    public static function accountUpdate(AccountUpdateRequest $request)
    {
        $user = auth()->user();

        $user->name = $request->name;
        $user->about = $request->about ? $request->about : null;

        if ($user->email != strtolower($request->email))
        {
            $user->notify(
                (
                    new EmailValidationNotification(
                        $user->id,
                        $user->session_id,
                        $user->name
                    )
                )->onQueue('email')
            );

            $user->email = strtolower($request->email);
            $user->verified = false;
        }

        if ($request->password)
        {
            $user->password = bcrypt($request->password);
        }

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification(
                        'Olive: Bilgiler Güncellendi!',
                        'Merhaba, '.$user->name,
                        'Hesap bilgieriniz başarılı bir şekilde güncellendi.'
                    )
                )->onQueue('email')
            );
        }

        $user->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı, Bildirim Tercihleri
     *
     * @return view
     */
    public static function notifications()
    {
        return view('user.notifications');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı, Bildirim Tercihi, Güncelle
     *
     * @return array
     */
    public static function notificationUpdate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'key' => 'string|in:'.implode(',', array_keys(config('system.notifications')))
        ]);

        if ($user->notification($request->key))
        {
            UserNotification::where([
                'user_id' => $user->id,
                'key' => $request->key
            ])->delete();
        }
        else
        {
            UserNotification::create([
                'user_id' => $user->id,
                'key' => $request->key
            ]);
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
     * Avatar Sayfası
     *
     * @return view
     */
    public static function avatar()
    {
        return view('user.avatar');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Avatar Yükle
     *
     * @return redirect
     */
    public static function avatarUpload(AvatarRequest $request)
    {
        $user = auth()->user();
        $name = md5(implode('_', [ config('app.name'), $user->id ]));

        $img = Image::make($request->file);
        $img->fit(256, 256);
        $img->save(storage_path('app/public/avatar/'.$name.'.jpg'), 60);

        $user->avatar = 'storage/avatar/'.$name.'.jpg';
        $user->save();

        return redirect()->route('settings.avatar');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Oluştur
     *
     * @return array
     */
    public static function adminCreate(AdminCreateRequest $request)
    {
        $user = new User;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->session_id = str_random(100);
        $user->verified = true;
        $user->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Kullanıcı Listesi, Otomatik Tamamlama
     *
     * @return array
     */
    public static function adminAutocomplete()
    {
        $data = [];

        $users = User::select('name', 'avatar')->get();

        if (count($users))
        {
            foreach ($users as $user)
            {
                $data[$user->name] = $user->avatar();
            }
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }
}
