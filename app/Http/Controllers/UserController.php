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
use Auth;
use Session;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware('guest')->only([
			'registerPut',
			'loginPost',
			'loginView'
		]);

        $this->middleware('throttle:5,5')->only('passwordPost');
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

    # register validate
    public static function registerValidate(string $id, string $sid)
    {
        $user = User::where('id', $id)->where('session_id', $sid)->where('verified', false)->firstOrFail();
        $user->verified = true;
        $user->save();

        session()->flash('validate', 'ok');

        return redirect()->route('dashboard');
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

        return [
            'status' => 'ok'
        ];
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

    # logout
    public static function logout()
    {
    	Auth::logout();

    	return redirect()->route('user.login');
    }
}
