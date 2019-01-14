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
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have' ]);

        /**
         ***** ZORUNLU *****
         *
         * - Twitter Hesabı
         */
        $this->middleware([ 'twitter:have' ])->only('disconnect');
    }

    /**
     * Twitter ile bağlan sayfası.
     *
     * @return view
     */
    public function connect()
    {
        $organisation = auth()->user()->organisation;

        return view('twitter.connect', compact('organisation'));
    }

    /**
     * Twitter hesap bağlantısı kesme.
     *
     * @return redirect
     */
    public function disconnect()
    {
        $twitter_account = auth()->user()->organisation->twitterAccount;
        $twitter_account->delete();

        return redirect()->route('twitter.connect');
    }

    /**
     * Twitter yönlendirme.
     *
     * @return mixed
     */
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
                    'suspended' => $response->user['suspended'] ? true : false,
                    'status' => $status,
                    'reasons' => $status ? null : implode(PHP_EOL, $reasons)
                ]
            );

            auth()->user()->organisation()->update([ 'twitter_account_id' => $response->id ]);
        }

        return redirect()->route('twitter.connect');
    }
}
