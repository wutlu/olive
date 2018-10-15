<?php

namespace App\Http\Controllers\Twitter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Socialite;
use Laravel\Socialite\Contracts\Provider;
use App\Models\Twitter\Account;

class AccountController extends Controller
{
	public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
        $this->middleware([ 'twitter:have' ])->only('disconnect');
    }

    # twitter connect
    public function connect()
    {
        $organisation = auth()->user()->organisation;

        return view('twitter.connect', compact('organisation'));
    }

    # twitter connect
    public function disconnect()
    {
        $user = auth()->user();
        $organisation = $user->organisation;

        $twitter_account = $user->organisation->twitterAccount;
        $twitter_account->delete();

        return redirect()->route('twitter.connect');
    }

    # twitter redirect
    public function redirect()
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Return a callback method from twitter api.
     *
     * @return callback URL from twitter
     */
    public function callback(Request $request)
    {
        if ($request->denied)
        {
            session()->flash('denied', true);
        }
        else
        {
        	$response = Socialite::with('twitter')->user();

            $reasons = [];
            $status = true;

            if (!$response->user['description'])
            {
                $reasons[] = 'Twitter hesabınızın biyografi alanını doldurun.';
                $status = false;
            }

            if ($response->avatar_original == 'http://abs.twimg.com/sticky/default_profile_images/default_profile.png')
            {
                $reasons[] = 'Twitter hesabınıza bir profil resmi ekleyin.';
                $status = false;
            }

            if ($response->user['suspended'])
            {
                $reasons[] = 'Twitter hesabınız askıya alınmış. Lütfen farklı bir hesap ile tekrar giriş yapın.';
                $status = false;
            }

        	$user = Account::updateOrCreate(
                [
                    'id' => $response->id
                ],
                [
                    'token' => $response->token,
                    'token_secret' => $response->tokenSecret,
                    'name' => $response->name,
                    'screen_name' => $response->nickname,
                    'avatar' => $response->avatar_original,
                    'description' => $response->user['description'],
                    'suspended' => $response->user['suspended'],
                    'status' => $status,
                    'reasons' => $status ? null : implode(PHP_EOL, $reasons)
                ]
            );

            auth()->user()->organisation()->update([ 'twitter_account_id' => $response->id ]);
        }

        return redirect()->route('twitter.connect');
    }
}
