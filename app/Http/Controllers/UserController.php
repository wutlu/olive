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
use App\Http\Requests\User\Partner\UpdateRequest as PartnerUpdateRequest;
use App\Http\Requests\User\Partner\CreateRequest as PartnerCreateRequest;
use App\Http\Requests\User\Partner\PaymentRequest;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\AutocompleteRequest;
use App\Http\Requests\QRequest;
use App\Http\Requests\IdRequest;

use App\Notifications\PasswordValidationNotification;
use App\Notifications\EmailValidationNotification;
use App\Notifications\WelcomeNotification;
use App\Notifications\NewPasswordNotification;
use App\Notifications\LoginNotification;
use App\Notifications\MessageNotification;
use App\Notifications\SendPasswordNotification;

use App\Utilities\UserActivityUtility;

use App\Models\User\User;
use App\Models\User\PartnerPayment;
use App\Models\User\UserNotification;
use App\Models\Option;
use App\Models\Organisation\Organisation;
use App\Models\SearchHistory;

use Auth;
use Session;
use Jenssegers\Agent\Agent;
use Image;
use System;
use Validator;
use Carbon\Carbon;

use Mail;
use App\Mail\ServerAlertMail;

use App\Http\Controllers\OrganisationController;

use App\SMS;

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
            'mobile',
            'mobileCreate',
            'mobileVerification',
            'mobileDelete',
            'mobileResend',
            'partnerSubPercent',
            'searchHistory',
            'searchHistoryData',
            'searchHistoryDelete',
        ]);

        /**
         ***** ZORUNLU *****
         *
         * - Partner
         */
        $this->middleware('partner')->only([
            'partnerListView',
            'partnerListJson',
            'partnerUserView',
            'partnerUserCreate',
            'partnerUserUpdate',
            'partnerHistory',
            'partnerSubPercent',
        ]);

        ### [ 5 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:5,5')->only('passwordPost');

        ### [ 10 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:10,1')->only('loginPost');

        ### [ 1 işlemden sonra 1 dakika ile sınırla ] ###
        $this->middleware('throttle:1,1')->only('registerResend');
	}

    /**
     * Alt Partner Yüzdesi
     *
     * @return array
     */
    public static function partnerSubPercent(Request $request)
    {
        $request->validate([
            'sub_partner_percent' => 'required|integer|min:0|max:100',
            'user_id' => 'required|integer'
        ]);

        $user = User::where('partner_user_id', auth()->user()->id)->where('id', $request->user_id)->firstOrFail();

        $user->sub_partner_percent = $request->sub_partner_percent;

        $user->partner = $request->sub_partner_percent ? 'eagle' : null;

        $user->update();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Kullanıcı Arama Geçmişi
     *
     * @return view
     */
    public static function searchHistory()
    {
        $user = auth()->user();

        return view('user.search_history', compact('user'));
    }

    /**
     * Kullanıcı Arama Geçmişi, Data
     *
     * @return array
     */
    public static function searchHistoryData(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = SearchHistory::where('user_id', auth()->user()->id);
        $query = $request->string ? $query->where('query', 'ILIKE', '%'.$request->string.'%') : $query;
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
     *******************
     ****** ADMIN ******
     *******************
     *
     * Kullanıcı Arama Geçmişi
     *
     * @return view
     */
    public static function adminSearchHistory(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('user.admin.search_history', compact('user'));
    }

    /**
     *******************
     ****** ADMIN ******
     *******************
     *
     * Kullanıcı Arama Geçmişi, Data
     *
     * @return array
     */
    public static function adminSearchHistoryData(int $id, SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = SearchHistory::where('user_id', $id);
        $query = $request->string ? $query->where('query', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->withTrashed();
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
     * Kullanıcı Arama Geçmişi, Sil
     *
     * @return array
     */
    public static function searchHistoryDelete(IdRequest $request)
    {
        $query = SearchHistory::where([ 'id' => $request->id, 'user_id' => auth()->user()->id ])->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }

    /**
     * GSM bilgisi
     *
     * @return view
     */
    public static function mobile()
    {
        $user = auth()->user();

        if ($user->verified)
        {
            return view('user.mobile', compact('user'));
        }
        else
        {
            $data = (object) [
                'message' => 'Lütfen önce e-posta adresinizi doğrulayın! 222',
                'button' => [
                    'text' => 'Hesap Bilgileri',
                    'route' => route('settings.account')
                ]
            ];

            return view('alert', compact('data'));
        }
    }

    /**
     * GSM oluştur
     *
     * @return array
     */
    public static function mobileCreate(Request $request)
    {
        $user = auth()->user();

        if (!$user->verified)
        {
            return [
                'status' => 'err',
                'message' => 'Lütfen önce e-posta adresinizi doğrulayın!'
            ];
        }

        Validator::extend('gsm', function() use ($user) {
            return $user->gsm ? false : true;
        }, 'Zaten kayıtlı bir GSM numaranız mevcut.');

        $request->validate([ 'gsm' => 'required|bail|string|phone_number|gsm|unique:users,gsm' ]);

        if (!$user->organisation_id)
        {
            $exists = Organisation::where('gsm', $request->gsm)->exists();

            if ($exists)
            {
                return [
                    'status' => 'err',
                    'message' => 'Bu GSM numarası daha önce kullanıldı.'
                ];
            }
        }

        $code = mt_rand(1000, 9999);

        $sms = SMS::send('GSM doğrulama kodunuz: '.$code, [ str_replace([ ' ', '(', ')' ], '', $request->gsm) ]);

        if ($sms['status'] == 'ok')
        {
            $user->gsm = $request->gsm;
            $user->gsm_code = $code;
            $user->save();
        }

        return $sms;
    }

    /**
     * GSM doğrula
     *
     * @return array
     */
    public static function mobileVerification(Request $request)
    {
        $user = auth()->user();

        Validator::extend('code', function($key, $code) use ($user) {
            return $user->gsm_code == $code ? true : false;
        }, 'Girdiğiniz kod geçerli değil.');

        Validator::extend('gsm', function() use ($user) {
            return $user->gsm_verified_at ? false : true;
        }, 'Zaten doğrulanmış bir GSM numaranız mevcut.');

        $request->validate([ 'code' => 'required|integer|between:1000,9999|gsm|code' ]);

        $user->gsm_verified_at = date('Y-m-d H:i:s');
        $user->save();

        $subject = 'GSM eklendi!';
        $message = $user->gsm.' gsm numarası hesabınıza tanımlandı.';

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification(
                        $subject,
                        'Merhaba, '.$user->name,
                        $message
                    )
                )->onQueue('email')
            );
        }

        UserActivityUtility::push(
            $subject,
            [
                'key'       => implode('-', [ 'user', $user->id, 'gsm' ]),
                'icon'      => 'phone_android',
                'markdown'  => $message
            ]
        );

        if ($user->organisation_id)
        {
            $demo = false;
        }
        else
        {
            $demo = true;

            $organisation = new Organisation;
            $organisation->name = $user->name.'-org';
            $organisation->start_date = date('Y-m-d H:i:s');
            $organisation->end_date = date('Y-m-d H:i:s', strtotime('+8 days'));
            $organisation->user_id = $user->id;
            $organisation->status = true;
            $organisation->user_capacity = 1;
            $organisation->data_twitter = true;
            $organisation->data_sozluk = true;
            $organisation->data_news = true;
            $organisation->data_youtube_video = true;
            $organisation->data_youtube_comment = true;
            $organisation->data_shopping = true;
            $organisation->data_facebook = true;
            $organisation->data_instagram = true;
            $organisation->data_blog = true;
            $organisation->archive_limit = 2;
            $organisation->data_pool_youtube_channel_limit = 2;
            $organisation->data_pool_youtube_video_limit = 2;
            $organisation->data_pool_youtube_keyword_limit = 2;
            $organisation->data_pool_twitter_keyword_limit = 2;
            $organisation->data_pool_twitter_user_limit = 2;
            $organisation->data_pool_facebook_keyword_limit = 2;
            $organisation->data_pool_facebook_user_limit = 2;
            $organisation->data_pool_instagram_follow_limit = 2;
            $organisation->historical_days = 7;
            $organisation->module_real_time = true;
            $organisation->module_crm = true;
            $organisation->module_search = true;
            $organisation->module_trend = true;
            $organisation->module_alarm = true;
            $organisation->saved_searches_limit = 2;
            $organisation->gsm = $user->gsm;
            $organisation->module_compare = true;
            $organisation->module_borsa = true;
            $organisation->module_report = true;
            $organisation->demo = true;
            $organisation->save();

            $user->organisation_id = $organisation->id;
            $user->save();

            $subject = 'Deneme paketiniz aktif edildi!';
            $message = 'Deneme paketiniz 7 gün süreyle aktif edildi. İyi araştırmalar dileriz.';

            UserActivityUtility::push(
                $subject,
                [
                    'key'       => implode('-', [ 'user', $user->id, 'demo' ]),
                    'icon'      => 'av_timer',
                    'markdown'  => $message
                ]
            );

            if ($user->notification('important'))
            {
                $user->notify(
                    (
                        new MessageNotification(
                            $subject,
                            'Merhaba, '.$user->name,
                            $message
                        )
                    )->onQueue('email')
                );
            }
        }

        return [
            'status' => 'ok',
            'demo' => $demo
        ];
    }

    /**
     * GSM tekrar gönder
     *
     * @return array
     */
    public static function mobileResend()
    {
        $user = auth()->user();

        if ($user->gsm)
        {
            $to_time = time();
            $from_time = strtotime($user->updated_at);

            $diff = intval(($to_time - $from_time) / 60);

            if ($diff >= 10)
            {
                $code = mt_rand(1000, 9999);

                $sms = SMS::send('GSM doğrulama kodunuz: '.$code, [ str_replace([ ' ', '(', ')' ], '', $user->gsm) ]);

                if ($sms['status'] == 'ok')
                {
                    $user->gsm_code = $code;
                    $user->save();
                }

                return $sms;
            }
            else
            {
                return [
                    'status' => 'err',
                    'message' => 'Yeni doğrulama kodu için '.(10 - $diff).' dakika beklemeniz gerekiyor.'
                ];
            }
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'Önce bir GSM numarası eklemeniz gerekiyor.'
            ];
        }
    }

    /**
     * GSM sil
     *
     * @return array
     */
    public static function mobileDelete()
    {
        $user = auth()->user();

        $user->gsm_verified_at = null;
        $user->gsm = null;
        $user->gsm_code = null;
        $user->save();

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
    public static function loginView(Request $request)
    {
        $request->validate(
            [
                'q' => 'string|in:giris,sifre,kaydol'
            ]
        );

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

    	return view('user.logister', compact('photo', 'request'));
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

            if ($user->verified && $user->email_verified_at == null)
            {
                $user->email_verified_at = date('Y-m-d H:i:s');
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

                $browser['name'] = $agent_browser ? $agent_browser : 'unknown';

                if ($agent->version($agent_browser))
                {
                    $browser['version'] = $agent->version($agent_browser);
                }

                $os['name'] = $agent_platform ? $agent_platform : 'unknown';

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
                    'Hesabınıza farklı bir ip ile giriş yapıldı.',
                    [
                        'key'       => implode('-', [ 'user', 'auth', $info->ip, $user->id ]),
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
                'status' => 'ok',
                'data' => [
                    'name' => $user->name
                ]
            ];
        }
        else
        {
            System::log(
                'Başarısız bir giriş denemesi yapıldı.',
                'App\Http\Controllers\UserController::loginPost('.$value.':'.$password.')', 1
            );

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
        ])->first();

        if (@$user)
        {
            $user->verified = true;
            $user->save();

            session()->flash('validate', 'ok');

            $text = 'E-posta adresiniz başarılı bir şekilde doğrulandı! İyi araştırmalar...';

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

            $data = (object) [
                'title' => 'Bilgi',
                'message' => 'Tebrikler! E-posta adresiniz doğrulandı.',
                'status' => 'success'
            ];
        }
        else
        {
            $data = (object) [
                'message' => 'Geçersiz veya eski bir bağlantı kullandınız.',
            ];
        }

        return view('alert', compact('data'));
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
     * Kullanıcı Şifre Oluştur, SMS ile gönder.
     *
     * @return view
     */
    public static function sendPasswordByGSM(int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        if ($user->gsm)
        {
            $password = mt_rand(100000, 999999);

            $sms = SMS::send(
                implode(' ', [ '8vz giriş bilgileriniz,', 'E-posta: '.$user->email, 'Şifre: '.$password ]),
                [ str_replace([ ' ', '(', ')' ], '', $user->gsm) ]
            );

            if ($sms['status'] == 'ok')
            {
                $user->password = bcrypt($password);
                $user->save();
            }

            return $sms;
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'SMS yöntemiyle şifre gönderebilmek için kullanıcının GSM numarası gerekiyor.'
            ];
        }
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

        if ($user->moderator == false && $request->moderator)
        {
            ### [ moderatör rozeti ] ###
            $user->addBadge(997);
        }

        if ($user->root == false && $request->root)
        {
            ### [ root rozeti ] ###
            $user->addBadge(998);
        }

        if ($user->admin == false && $request->admin)
        {
            ### [ admin rozeti ] ###
            $user->addBadge(996);
        }

        if (!$user->partner && $request->partner)
        {
            if (!$user->badge(11))
            {
                $user->addBadge(11); // partner
            }
        }

        $user->name = $request->name;
        $user->password = $request->password ? bcrypt($request->password) : $user->password;

        if (strtolower($request->email) != $user->email)
        {
            $user->email = strtolower($request->email);
        }

        $user->verified = $request->verified ? true : false;
        $user->gsm = $request->gsm ? $request->gsm : null;
        $user->avatar = $request->avatar ? null : $user->avatar;
        $user->root = $request->root ? true : false;
        $user->admin = $request->admin ? true : false;
        $user->moderator = $request->moderator ? true : false;
        $user->about = $request->about ? $request->about : null;

        $user->partner = $request->partner ? $request->partner : null;

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
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Kullanıcı Listesi
     *
     * @return view
     */
    public static function partnerListView(int $id = null)
    {
        $user = auth()->user();

        return view('user.partner.list', compact('user'));
    }

    /**
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Kullanıcı Listesi
     *
     * @return array
     */
    public static function partnerListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $user = auth()->user();

        $query = new User;
        $query = $query->with('organisation:id,status,end_date');
        $query = $query->where('partner_user_id', $user->id);
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
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Kullanıcı Formu
     *
     * @return view
     */
    public static function partnerUserView(int $id = null)
    {
        $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

        $auth = auth()->user();

        $user = $id ? User::findOrFail($id) : [];

        if ($user && $user->partner_user_id != $auth->id)
        {
        	if ($user && $auth->admin)
	        {
	        	return redirect()->route('admin.user', $user->id);
	        }
	        else
	        {
	        	return abort(403);
	        }
        }

        $partner_percent = System::option('formal.partner.'.$auth->partner.'.percent');

        return view('user.partner.view', compact('auth', 'user', 'prices', 'partner_percent'));
    }

    /**
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Kullanıcı Oluşturma
     *
     * @return array
     */
    public static function partnerUserCreate(PartnerCreateRequest $request)
    {
        $password = str_random(6);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($password);
        $user->session_id = base64_encode(time());
        $user->partner_user_id = auth()->user()->id;
        $user->verified = true;
        $user->save();

        $user->notify((new SendPasswordNotification($user->email, $password))->onQueue('email'));

        foreach (config('system.notifications') as $key => $title)
        {
            UserNotification::create([
                'user_id' => $user->id,
                'key' => $key
            ]);
        }

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'created',
                'id' => $user->id
            ]
        ];
    }

    /**
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Kullanıcı Güncelleme
     *
     * @return array
     */
    public static function partnerUserUpdate(PartnerUpdateRequest $request)
    {
        $auth = auth()->user();
        $user = User::where('id', $request->user_id)->first();

        if ($user->organisation_id)
        {
            $calculate = OrganisationController::calculate($request);

            $organisation = $user->organisation;
            $organisation->status = false;
            $organisation->user_capacity = $request->user_capacity;

            if (ceil(abs(strtotime($organisation->created_at) - time()) / 86400) <= 30)
            {
                $organisation->end_date = $request->end_date;
            }

            $organisation->historical_days = $request->historical_days;
            $organisation->archive_limit = $request->archive_limit;
            $organisation->saved_searches_limit = $request->saved_searches_limit;

            $organisation->data_pool_youtube_channel_limit = $request->data_pool_youtube_channel_limit;
            $organisation->data_pool_youtube_video_limit = $request->data_pool_youtube_video_limit;
            $organisation->data_pool_youtube_keyword_limit = $request->data_pool_youtube_keyword_limit;
            $organisation->data_pool_twitter_keyword_limit = $request->data_pool_twitter_keyword_limit;
            $organisation->data_pool_twitter_user_limit = $request->data_pool_twitter_user_limit;
            $organisation->data_pool_instagram_follow_limit = $request->data_pool_instagram_follow_limit;

            $organisation->unit_price = $calculate['total_price'];

            $organisation->module_real_time = $request->module_real_time ? true : false;
            $organisation->module_crm = $request->module_crm ? true : false;
            $organisation->module_search = $request->module_search ? true : false;
            $organisation->module_trend = $request->module_trend ? true : false;
            $organisation->module_compare = $request->module_compare ? true : false;
            $organisation->module_borsa = $request->module_borsa ? true : false;
            $organisation->module_report = $request->module_report ? true : false;
            $organisation->module_alarm = $request->module_alarm ? true : false;

            /**
             * modules
             */
            foreach (config('system.modules') as $key => $module)
            {
                $organisation->{'data_'.$key} = $request->{'data_'.$key} ? true : false;
            }

            $organisation->save();

            $status = 'updated';

            $subject = $auth->name.' Organizasyon Güncelledi';
            $message = 'İşlem yapmak için tıklayın:';
        }
        else
        {
            $organisation = new Organisation;
            $organisation->name = $user->name.'-org';
            $organisation->user_id = $user->id;
            $organisation->start_date = date('Y-m-d H:i:s');
            $organisation->end_date = date('Y-m-d H:i:s');
            $organisation->save();

            $user->organisation_id = $organisation->id;
            $user->save();

            $status = 'created';

            $subject = $auth->name.' Organizasyon Oluşturdu';
            $message = 'Yeni bir organizasyon oluşturuldu. İşlem yapmak için tıklayın:';
        }

        Mail::queue(
            new ServerAlertMail(
                $subject,
                implode(
                    PHP_EOL.PHP_EOL,
                    [
                        $message,
                        '['.$user->name.'@'.$user->organisation->name.']('.route('admin.organisation', $user->organisation_id).')'
                    ]
                ),
                'admin'
            )
        );

        return [
            'status' => 'ok',
            'data' => [
                'id' => $user->id,
                'status' => $status
            ]
        ];
    }

    /**
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Hesap Geçmişi
     *
     * @return view
     */
    public static function partnerHistory()
    {
        $user = auth()->user();
        $partner_wallet = $user->partnerWallet();

        return view('user.partner.history', compact('user', 'partner_wallet'));
    }

    /**
     ***********************
     ******* PARTNER *******
     ***********************
     *
     * Ödeme İsteği
     *
     * @return array
     */
    public static function partnerPaymentRequest(PaymentRequest $request)
    {
        $q = new PartnerPayment;
        $q->currency = config('formal.currency_text');
        $q->amount = '-'.$request->amount;
        $q->message = $request->name.' - '.$request->iban;
        $q->status = 'pending';
        $q->user_id = auth()->user()->id;
        $q->save();

        return [
            'status' => 'ok'
        ];
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
    public static function adminListView(QRequest $request)
    {
        $partners = [
            'eagle' => 'Eagle',
            'phoenix' => 'Phoenix',
            'gryphon' => 'Gryphon',
            'dragon' => 'Dragon'
        ];

        $user = [];

        if ($request->q)
        {
            preg_match('/(?<=partner:)[([0-9]+(?=)/', $request->q, $matches);

            if (@$matches[0])
            {
                $user = User::where('id', $matches[0])->firstOrFail();
            }
        }

        return view('user.admin.list', compact('user', 'partners', 'request'));
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

        if ($request->partner)
        {
            $query = $query->where('partner', $request->partner);
        }

        if ($request->auth)
        {
            $query = $query->where($request->auth, true);
        }

        if ($request->string)
        {
            preg_match('/(?<=partner:)[([0-9]+(?=)/', $request->string, $matches);

            if (@$matches[0])
            {
                $query = $query->where('partner_user_id', intval($matches[0]));
            }
            else
            {
                $query = $query->where(function ($query) use ($request) {
                    $query->orWhere('name', 'ILIKE', '%'.$request->string.'%')->orWhere('email', 'ILIKE', '%'.$request->string.'%');
                });
            }
        }

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
                        'Bilgiler Güncellendi!',
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
     * Kullanıcı, Bildirim Tercihleri
     *
     * @return view
     */
    public static function notifications()
    {
        return view('user.notifications');
    }

    /**
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
     * Avatar Sayfası
     *
     * @return view
     */
    public static function avatar()
    {
        return view('user.avatar');
    }

    /**
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
