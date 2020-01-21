<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Socialite;
use Laravel\Socialite\Contracts\Provider;

use App\Models\TwitterUsers;

class CRMController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');

        ### [ zorunlu aktif organizasyon ve module_crm ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_crm'
        ]);
    }

    /**
     * CRM Ana Sayfa
     *
     * @return view
     */
    public static function dashboard()
    {
        $tusers = TwitterUsers::where('organisation_id', auth()->user()->organisation_id)->orderBy('id', 'DESC')->get();

        return view('crm.dashboard', compact('tusers'));
    }

    /**
     * CRM, Provider Callback
     *
     * @return array
     */
    public function providerCallback(string $provider, Request $request)
    {
        $request->validate([ 'denied' => 'nullable|string|max:255' ]);

        if ($request->denied)
        {
            $data = (object) [
                'title' => 'Uyarı',
                'message' => 'Yetkiyi reddettiniz.',
                'button' => [
                    'route' => route('crm.dashboard'),
                    'text' => 'CRM\'e dön'
                ]
            ];

            return view('alert', compact('data'));
        }

        $socialite = Socialite::driver($provider);
        $socialite = json_encode($socialite->user());
        $socialite = json_decode($socialite);

        switch ($provider)
        {
            case 'twitter':
                $user = TwitterUsers::updateOrCreate(
                    [
                        'user_id' => $socialite->user->id_str
                    ],
                    [
                        'user_id' => $socialite->user->id_str,
                        'token' => $socialite->token,
                        'token_secret' => $socialite->tokenSecret,
                        'nickname' => $socialite->nickname,
                        'name' => $socialite->name,
                        'avatar' => $socialite->avatar,
                        'verified' => $socialite->user->verified ? true : false,
                        'status' => true,
                        'organisation_id' => auth()->user()->organisation_id
                    ]
                );

                return redirect()->route('crm.dashboard');
            break;
        }
    }

    /**
     * CRM, Provider Redirect
     *
     * @return array
     */
 
    public function providerRedirect(string $provider)
    {
        switch ($provider)
        {
            case 'twitter':
                $count = TwitterUsers::where('organisation_id', auth()->user()->organisation_id)->count();
            break;
        }

        if ($count >= 4)
        {
            $data = (object) [
                'title' => 'Uyarı',
                'message' => 'En fazla 4 hesap bağlayabilirsiniz.',
                'button' => [
                    'route' => route('crm.dashboard'),
                    'text' => 'CRM\'e dön'
                ]
            ];

            return view('alert', compact('data'));
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * CRM, Provider Drop
     *
     * @return array
     */
    public function providerDrop(string $provider, int $id)
    {
        switch ($provider)
        {
            case 'twitter':
                $user = TwitterUsers::where(
                    [
                        'id' => $id,
                        'organisation_id' => auth()->user()->organisation_id
                    ]
                )->delete();

                return redirect()->route('crm.dashboard');
            break;
        }
    }
}
