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
use App\Http\Requests\User\TransactionRequest;
use App\Http\Requests\User\Admin\TransactionRequest as AdminTransactionRequest;
use App\Http\Requests\SearchRequest;

use App\Notifications\PasswordValidationNotification;
use App\Notifications\EmailValidationNotification;
use App\Notifications\WelcomeNotification;
use App\Notifications\NewPasswordNotification;
use App\Notifications\LoginNotification;
use App\Notifications\DiscountCouponNotification;
use App\Notifications\MessageNotification;

use App\Utilities\UserActivityUtility;

use App\Models\Discount\DiscountDay;
use App\Models\Discount\DiscountCoupon;
use App\Models\User\User;
use App\Models\User\UserNotification;
use App\Models\User\Transaction;
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
            'reference',
            'references',
            'referenceStart',
            'transactions',
            'transaction',
        ]);

        ### [ 5 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:5,5')->only('passwordPost');

        ### [ 5 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:5,1')->only('loginPost');

        ### [ 1 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:1,1')->only('registerResend');
	}


    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Üye, Refenras Sistemi
     *
     * @return view
     */
    public static function adminReference()
    {
        $user = auth()->user();

        return view('user.admin.reference', compact('user'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Üye, Refenras Sistemi, işlem geçmişi.
     *
     * @return array
     */
    public static function adminTransactions(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new Transaction;
        $query = $query->with('user');
        $query = $request->string ? $query->where('status_message', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('created_at', 'DESC');

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
     * Üye, Refenras Sistemi, işlem yap.
     *
     * @return array
     */
    public static function adminTransaction(AdminTransactionRequest $request)
    {
        $option = Option::where('key', 'root_alert.partner')->first();

        $transaction = Transaction::where('id', $request->id)->first();

        if ($transaction->withdraw == 'wait' && $request->withdraw != 'wait')
        {
            $option->decr();
        }
        else if ($transaction->withdraw != 'wait' && $request->withdraw == 'wait')
        {
            $option->incr();
        }

        $transaction->withdraw = $request->withdraw;
        $transaction->status_message = $request->status_message;
        $transaction->save();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $transaction->id,
                'withdraw' => $transaction->withdraw,
            ]
        ];
    }

    /**
     * Üye, Refenras Sistemi
     *
     * @return view
     */
    public static function reference($id = 0)
    {
        $root = $id ? true : false;
        $user = $id ? User::where('id', $id)->firstOrFail() : auth()->user();

        if ($root && !$user->reference_code)
        {
            return abort(404);
        }

        return view('user.reference', compact('user', 'root'));
    }

    /**
     * Üye, Refenras Sistemi
     *
     * @return array
     */
    public static function references($id = 0, SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new User;
        $query = $query->select('name', 'id', 'created_at');
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->where('reference_id', $id ? $id : auth()->user()->id);
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('created_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     * Üye, Refenras Sistemi
     *
     * @return array
     */
    public static function referenceStart()
    {
        $user = auth()->user();

        if (!$user->reference_code)
        {
            $code = null;

            while ($code === null)
            {
                $code_random = str_random(6);

                $exists = User::where('reference_code', $code_random)->exists();

                if (!$exists)
                {
                    $code = $code_random;
                }
            }

            $user->addBadge(11);
            $user->update([ 'reference_code' => $code ]);
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Üye, Refenras Sistemi, işlem geçmişi.
     *
     * @return array
     */
    public static function transactions($id = 0, SearchRequest $request)
    {
        $user = $id ? User::where('id', $id)->firstOrFail() : auth()->user();

        $take = $request->take;
        $skip = $request->skip;

        $query = new Transaction;
        $query = $query->select('id', 'price', 'currency', 'status_message', 'withdraw', 'iban', 'created_at');
        $query = $request->string ? $query->where('status_message', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->where('user_id', $user->id);
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('created_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     * Üye, Refenras Sistemi, işlem oluştur.
     *
     * @return array
     */
    public static function transaction(TransactionRequest $request)
    {
        $query = new Transaction;
        $query->price = '-'.$request->price;
        $query->withdraw = 'wait';
        $query->iban = $request->iban;
        $query->iban_name = $request->iban_name;
        $query->user_id = auth()->user()->id;
        $query->currency = config('formal.currency');
        $query->save();

        session()->flash('transaction', 'success');

        Option::where('key', 'root_alert.partner')->first()->incr();

        return [
            'status' => 'ok'
        ];
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
        $email = $request->email_login;
        $password = $request->password_login;

        if (Auth::attempt([ 'email' => $email, 'password' => $password ]))
        {
            $user = User::where('email', $email)->first();

            if ($user->banned_at)
            {
                auth()->logout();

                System::log(
                    'Yasaklı bir kullanıcı giriş denemesi yaptı.',
                    'App\Http\Controllers\UserController::loginPost('.$email.':'.$password.')', 1
                );

                return [
                    'status' => 'ban',
                    'data' => [
                        'reason' => $user->ban_reason
                    ]
                ];
            }

            $diffYears = Carbon::now()->diffInYears($user->created_at);

            $gift = 0;

            if ($diffYears >= 5)
            {
                if (!$user->badge(10))
                {
                    ### [ 5 yıl dolduruldu ] ###
                    $user->addBadge(10);

                    $gift = 30;
                }
            }
            else if ($diffYears >= 4)
            {
                if (!$user->badge(9))
                {
                    ### [ 4 yıl dolduruldu ] ###
                    $user->addBadge(9);

                    $gift = 20;
                }
            }
            else if ($diffYears >= 3)
            {
                if (!$user->badge(8))
                {
                    ### [ 3 yıl dolduruldu ] ###
                    $user->addBadge(8);

                    $gift = 10;
                }
            }
            else if ($diffYears >= 2)
            {
                if (!$user->badge(7))
                {
                    ### [ 2 yıl dolduruldu ] ###
                    $user->addBadge(7);

                    $gift = 10;
                }
            }
            else if ($diffYears >= 1)
            {
                if (!$user->badge(6))
                {
                    ### [ 1 yıl dolduruldu ] ###
                    $user->addBadge(6);

                    $gift = 10;
                }
            }

            if ($gift)
            {
                $ok = false;

                while ($ok == false)
                {
                    $generate_key = str_random(8);

                    $key = DiscountCoupon::where('key', $generate_key)->count();

                    if ($key == 0)
                    {
                        $coupon = new DiscountCoupon;
                        $coupon->key = $generate_key;
                        $coupon->rate = $gift;
                        $coupon->price = 0;
                        $coupon->save();

                        $ok = true;

                        $message[] = $diffYears == 1 ? 'Sizinle bir yılı geride bıraktık!' : 'Sizinle bir yılı daha geride bıraktık!';
                        $message[] = 'Birlikte geçecek nice yıllar adına sizin için bir indirim kuponu hazırladık.';
                        $message[] = 'İyi günlerde kullanın...';

                        $discount[] = '| Kupon Kodu        | İndirim Oranı                                              |';
                        $discount[] = '| ----------------: |:---------------------------------------------------------- |';

                        $discount[] = '| '.$generate_key.' | '.$gift.'%                                                 |';

                        # --- [] --- #

                        $discount = implode(PHP_EOL, $discount);

                        $user->notify(
                            (
                                new DiscountCouponNotification(
                                    $user->name,
                                    $discount,
                                    implode(PHP_EOL, $message)
                                )
                            )->onQueue('email')
                        );
                    }
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
                $data[] = '| --------------: |:------------------------------------------------------ |';
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

        if ($request->email != $user->email)
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

        ### [ indirim günü varsa kupon yarat ] ###

        $discountDay = DiscountDay::where('first_day', '<=', date('Y-m-d'))->where('last_day', '>=', date('Y-m-d'))->first();

        if (@$discountDay)
        {
            $ok = false;

            while ($ok == false)
            {
                $generate_key = str_random(8);

                $key = DiscountCoupon::where('key', $generate_key)->count();

                if ($key == 0)
                {
                    $coupon = new DiscountCoupon;
                    $coupon->key = $generate_key;
                    $coupon->rate = $discountDay->discount_rate;
                    $coupon->price = $discountDay->discount_price;
                    $coupon->save();

                    $ok = true;

                    $discount[] = '| Kupon Kodu        | İndirim Oranı                                              |';
                    $discount[] = '| ----------------: |:---------------------------------------------------------- |';

                    $discount[] = '| '.$generate_key.' | '.$discountDay->discount_rate.'%                           |';

                if ($discountDay->discount_price)
                {
                    $discount[] = '| Ek            | '.config('formal.currency').' '.$discountDay->discount_price.' |';
                }

                    # --- [] --- #

                    $discount = implode(PHP_EOL, $discount);

                    $user->notify(
                        (
                            new DiscountCouponNotification(
                                $user->name,
                                $discount
                            )
                        )->onQueue('email')
                    );
                }
            }
        }

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
        $user = new User;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->email = $request->email;
        $user->session_id = Session::getId();
        $user->term_version = config('system.term_version');

        if ($request->reference_code)
        {
            $referencer = User::where('reference_code', $request->reference_code)->first();

            if (@$referencer)
            {
                $user->reference_id = $referencer->id;
            }
        }

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
        $user->email = $request->email;
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

        if ($user->email != $request->email)
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

            $user->email = $request->email;
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
}
