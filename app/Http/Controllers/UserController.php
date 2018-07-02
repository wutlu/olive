<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\PasswordGetRequest;
use App\Http\Requests\User\PasswordNewRequest;
use App\User;
use App\Notifications\PasswordValidationNotification;
use App\Notifications\RegisterValidationNotification;
use App\Notifications\WelcomeNotification;
use App\Notifications\NewPasswordNotification;
use App\Notifications\SignInNotification;
use Auth;
use Session;
use Jenssegers\Agent\Agent;

use App\Utilities\UserActivityUtility;

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

                $data[] = '| Özellik         | Değer           |';
                $data[] = '| --------------: |:--------------- |';
                $data[] = '| IP              | '.$ip.'         |';

            if ($device)
            {
                $data[] = '| Cihaz           | '.$device.'     |';
            }

            if ($platform)
            {
                $data[] = '| İşletim Sistemi | '.$platform.'   |';
            }

            if ($browser)
            {
                $data[] = '| Tarayıcı        | '.$browser.'    |';
            }

                $data[] = '| İşlem Tarihi    | '.$date.'       |';

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

                if ($user->signin_notification)
                {
                    $user->notify(new SignInNotification($user->name, $data));
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
        $user = User::where('id', $id)->where('session_id', $sid)->firstOrFail();

        return view('user.password_new', compact('user'));
    }

    # password new patch
    public static function passwordNewPatch(string $id, string $sid, PasswordNewRequest $request)
    {
        $user = User::where('id', $id)->where('session_id', $sid)->firstOrFail();

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
        $user = User::where('id', $id)->where('session_id', $sid)->where('verified', false)->firstOrFail();
        $user->verified = true;
        $user->save();

        session()->flash('validate', 'ok');

        $text = 'Sizleri aramızda görmekten şeref duyar, iyi araştırmalar dileriz.';

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
    	Auth::logout();

    	return redirect()->route('user.login');
    }
}
