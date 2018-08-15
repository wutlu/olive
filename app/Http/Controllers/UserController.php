<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\PasswordGetRequest;
use App\Http\Requests\User\PasswordNewRequest;
use App\Http\Requests\User\Admin\UpdateRequest as AdminUpdateRequest;
use App\Http\Requests\SearchRequest;

use App\Notifications\PasswordValidationNotification;
use App\Notifications\RegisterValidationNotification;
use App\Notifications\WelcomeNotification;
use App\Notifications\NewPasswordNotification;
use App\Notifications\SignInNotification;
use App\Notifications\OrganisationDiscountCouponNotification;

use App\Utilities\UserActivityUtility;

use App\Models\Organisation\OrganisationDiscountDay;
use App\Models\Organisation\OrganisationDiscountCoupon;
use App\Models\User\User;
use App\Models\User\UserNotification;

use Auth;
use Session;
use Jenssegers\Agent\Agent;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware('guest')->only([
			'registerPut',
			'loginPost',
			'loginView'
		]);
        $this->middleware('auth')->only([ 'registerResend' ]);

        $this->middleware('throttle:5,5')->only('passwordPost');
        $this->middleware('throttle:5,1')->only('loginPost');
        $this->middleware('throttle:1,1')->only('registerResend');
	}

    # login view
    public static function loginView()
    {
    	return view('user.logister');
    }

    # login post
    public static function loginPost(LoginRequest $request)
    {
        $email = $request->email_login;
        $password = $request->password_login;

        if (Auth::attempt([ 'email' => $email, 'password' => $password ]))
        {
            $user = User::where('email', $email)->first();

            $request->session()->regenerate();

            $previous_session = $user->session_id;

            if ($previous_session)
            {
                $agent = new Agent();

                $device     = $agent->device();
                $platform   = $agent->platform();
                $browser    = $agent->browser();
                $type       = $agent->isDesktop() ? 'Masaüstü' : $agent->isPhone() ? 'Mobil' : 'Diğer';
                $ip         = $request->ip();
                $date       = date('Y-m-d H:i:s');

                $location   = geoip()->getLocation($ip);

                $data[] = '| Özellik         | Değer                                      |';
                $data[] = '| --------------: |:------------------------------------------ |';
                $data[] = '| IP              | '.$ip.'                                    |';
                $data[] = '| Konum           | '.$location->city.'/'.$location->country.' |';

            if ($device)
            {
                $data[] = '| Cihaz           | '.$device.'                                |';
            }

            if ($platform)
            {
                $data[] = '| İşletim Sistemi | '.$platform.'                              |';
            }

            if ($browser)
            {
                $data[] = '| Tarayıcı        | '.$browser.'                               |';
            }

                $data[] = '| İşlem Tarihi    | '.$date.'                                  |';

                # --- [] --- #

                $data = implode(PHP_EOL, $data);

                UserActivityUtility::push(
                    'Hesabınıza yeni bir ip\'den giriş yapıldı.',
                    [
                        'key'       => implode('-', [ 'user', 'auth', $ip ]),
                        'icon'      => 'accessibility',
                        'markdown'  => $data
                    ]
                );

                $user->notify(new SignInNotification($user->name, $data));

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
            return response([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email_login' => [
                        'Geçersiz e-posta/şifre kombinasyonu.'
                    ],
                    'password_login' => [
                        'Geçersiz e-posta/şifre kombinasyonu.'
                    ]
                ]
            ], 422);
        }
    }

    # password post
    public static function passwordGetPost(PasswordGetRequest $request)
    {
        $user = User::where('email', $request->email_password)->first();

        $user->notify(new PasswordValidationNotification($user->id, $user->session_id));

        return [
            'status' => 'ok'
        ];
    }

    # password new get
    public static function passwordNew(string $id, string $sid)
    {
        $user = User::where([
            'id' => $id,
            'session_id' => $sid
        ])->firstOrFail();

        return view('user.password_new', compact('user'));
    }

    # password new patch
    public static function passwordNewPatch(string $id, string $sid, PasswordNewRequest $request)
    {
        $user = User::where([
            'id' => $id,
            'session_id' => $sid
        ])->firstOrFail();

        if ($request->email != $user->email)
        {
            return response([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => [
                        'Geçerli e-posta adresiniz bu değil.'
                    ]
                ]
            ], 422);
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

        $user->notify(new NewPasswordNotification($user->name, $text));

        return [
            'status' => 'ok'
        ];
    }

    # register validate
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

        $text = 'Sizleri aramızda görmek çok güzel, iyi araştırmalar dileriz.';

        UserActivityUtility::push(
            'Hoşgeldiniz!',
            [
                'icon'              => 'check',
                'markdown'          => $text,
                'markdown_color'    => '#8bc34a',
                'user_id'           => $user->id,
            ]
        );

        $user->notify(new WelcomeNotification($user->name, $text));

        # indirim günü varsa kupon yarat #

        $discountDay = OrganisationDiscountDay::where('first_day', '<=', date('Y-m-d'))->where('last_day', '>=', date('Y-m-d'))->first();

        if (@$discountDay)
        {
            $ok = false;

            while ($ok == false)
            {
                $generate_key = str_random(8);

                $key = OrganisationDiscountCoupon::where('key', $generate_key)->count();

                if ($key == 0)
                {
                    OrganisationDiscountCoupon::create([
                        'key' => $generate_key,
                        'rate' => $discountDay->discount_rate
                    ]);

                    $ok = true;

                    $discount[] = '| Kupon Kodu        | İndirim Oranı                    |';
                    $discount[] = '| ----------------: |:-------------------------------- |';
                    $discount[] = '| '.$generate_key.' | '.$discountDay->discount_rate.'% |';

                    # --- [] --- #

                    $discount = implode(PHP_EOL, $discount);

                    $user->notify(new OrganisationDiscountCouponNotification($user->name, $discount));
                }
            }
        }

        # --- #

        return redirect()->route('dashboard');
    }

    # register put
    public static function registerPut(RegisterRequest $request)
    {
        $request['password'] = bcrypt($request->password);

        $user = new User;
        $user->fill($request->all());
        $user->session_id = Session::getId();
        $user->save();

        $user->notify(new RegisterValidationNotification($user->id, $user->session_id, $user->name));

        Auth::login($user);

        return [
            'status' => 'ok'
        ];
    }

    # register resend
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
            $user->notify(new RegisterValidationNotification($user->id, $user->session_id, $user->name));
        }

        return [
            'status' => 'ok'
        ];
    }

    # logout
    public static function logout()
    {
        auth()->logout();

        return redirect()->route('user.login');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin view
    # 
    public static function adminView(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.admin.view', compact('user'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin update
    # 
    public static function adminUpdate(int $id, AdminUpdateRequest $request)
    {
        $user = User::where('id', $id)->firstOrFail();
        $user->name = $request->name;
        $user->password = $request->password ? bcrypt($request->password) : $user->password;
        $user->email = $request->email;
        $user->verified = $request->verified ? true : false;
        $user->avatar = $request->avatar ? null : $user->avatar;
        $user->root = $request->root ? true : false;
        $user->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin notifications
    # 
    public static function adminNotifications(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.admin.notifications', compact('user'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin update notification
    # 
    public static function adminNotificationUpdate(int $id, Request $request)
    {
        $user = User::where('id', $id)->firstOrFail();

        $request->validate([
            'key' => 'string|in:'.implode(',', array_keys(config('app.notifications')))
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin invoice history
    # 
    public static function adminInvoiceHistory(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $invoices = $user->invoices()->paginate(5);

        return view('user.admin.invoiceHistory', compact('user', 'invoices'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin tickets
    # 
    public static function adminTickets(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $tickets = $user->tickets();

        return view('user.admin.tickets', compact('user', 'tickets'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function adminListView()
    {
        return view('user.admin.list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
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
}
