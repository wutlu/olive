<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility as Activity;
use App\Utilities\Term;

use App\Models\Organisation\Organisation;
use App\Models\Organisation\OrganisationInvoice as Invoice;
use App\Models\User\User;
use App\Models\User\PartnerPayment;
use App\Models\User\UserNotification;
use App\Models\RealTime\KeywordGroup;
use App\Models\Pin\Group as PinGroup;
use App\Models\BillingInformation;
use App\Models\Alarm;
use App\Models\Option;
use App\Models\SavedSearch;
use App\Models\Geo\States;

use App\Http\Requests\Organisation\BillingUpdateRequest;
use App\Http\Requests\Organisation\NameRequest;
use App\Http\Requests\Organisation\TransferAndRemoveRequest;
use App\Http\Requests\Organisation\InviteRequest;
use App\Http\Requests\Organisation\LeaveRequest;
use App\Http\Requests\Organisation\DeleteRequest;
use App\Http\Requests\Organisation\Admin\UpdateRequest as AdminUpdateRequest;
use App\Http\Requests\Organisation\Admin\CreateRequest as AdminCreateRequest;
use App\Http\Requests\Organisation\Admin\InvoiceApproveRequest;
use App\Http\Requests\Organisation\Admin\PriceSettingsSaveRequest;
use App\Http\Requests\Search\AdminSaveRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Organisation\CreateRequest as OfferCreateRequest;

use App\Http\Requests\RealTime\KeywordGroup\AdminUpdateRequest as KeywordGroupAdminUpdateRequest;

use App\Notifications\OrganisationWasUpdatedNotification;
use App\Notifications\MessageNotification;
use App\Notifications\SendPasswordNotification;

use Carbon\Carbon;

use App\Jobs\CheckUpcomingPayments;

use App\Http\Requests\PaymentCallbackRequest;

use System;
use Validator;

use Mail;
use App\Mail\ServerAlertMail;

class OrganisationController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth')->except([ 'invoice', 'paymentCallback' ]);

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyonu Olanlar
         */
        $this->middleware('organisation:have')->only([
            'settings',
            'updateName',
            'leave',
            'transfer',
            'remove',
            'delete',
            'payment',
            'invite',
            'update',
            'paymentStatus',
            'invoiceCancel',
            'offer',
            'offerCreate',
        ]);

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Sahibi
         */
        $this->middleware('can:organisation-owner')->only([
            'invite',
            'update',
            'delete',
            'payment',
            'updateName',
            'transfer',
            'remove',
            'paymentStatus',
        ]);
    }

    /**
     *
     * Organizasyon, Plan Seçimi
     *
     * @return view
     */
    public static function offer()
    {
        $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();
        $user = auth()->user();

        return view('organisation.create.offer', compact('user', 'prices'));
    }

    /**
     *
     * Organizasyon, Plan Oluştur
     *
     * @return array
     */
    public static function offerCreate(OfferCreateRequest $request)
    {
        $user = auth()->user();

        if (!$user->verified)
        {
            return [
                'status' => 'err',
                'reason' => 'E-posta adresinizi doğrulamadan kayıt işlemini tamamlayamazsınız!'
            ];
        }

        if ($user->gsm_verified_at)
        {
            if ($request->module_real_time || $request->module_search || $request->module_alarm)
            {
                $source_count = 0;

                /**
                 * modules
                 */
                foreach (config('system.modules') as $key => $module)
                {
                    $source_count = $request->{'data_'.$key} ? ($source_count+1) : $source_count;
                }

                if (!$source_count)
                {
                    return [
                        'status' => 'step',
                        'step' => 2,
                        'message' => 'Canlı Akış, Arama veya Alarm seçiliyken en az 1 veri kaynağı seçmeniz gerekiyor.'
                    ];
                }
            }

            $module_count = 0;
            $module_count = $request->module_real_time ? ($module_count+1) : $module_count;
            $module_count = $request->module_search ? ($module_count+1) : $module_count;
            $module_count = $request->module_alarm ? ($module_count+1) : $module_count;
            $module_count = $request->module_trend ? ($module_count+1) : $module_count;
            $module_count = $request->module_compare ? ($module_count+1) : $module_count;
            $module_count = $request->module_borsa ? ($module_count+1) : $module_count;
            $module_count = $request->module_report ? ($module_count+1) : $module_count;

            if (!$module_count)
            {
                return [
                    'status' => 'step',
                    'step' => 1,
                    'message' => 'En az 1 modül seçmeniz gerekiyor.'
                ];
            }

            $organisation = $user->organisation;

            $calculate = self::calculate($request);

            $organisation->status = true;
            $organisation->user_capacity = $request->user_capacity;
            $organisation->start_date = date('Y-m-d H:i:s');
            $organisation->end_date = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $organisation->historical_days = $request->historical_days ? $request->historical_days : 0;
            $organisation->pin_group_limit = $request->pin_group_limit ? $request->pin_group_limit : 0;
            $organisation->saved_searches_limit = $request->saved_searches_limit ? $request->saved_searches_limit : 0;

            $organisation->data_pool_youtube_channel_limit = $request->data_pool_youtube_channel_limit ? $request->data_pool_youtube_channel_limit : 0;
            $organisation->data_pool_youtube_video_limit = $request->data_pool_youtube_video_limit ? $request->data_pool_youtube_video_limit : 0;
            $organisation->data_pool_youtube_keyword_limit = $request->data_pool_youtube_keyword_limit ? $request->data_pool_youtube_keyword_limit : 0;
            $organisation->data_pool_twitter_keyword_limit = $request->data_pool_twitter_keyword_limit ? $request->data_pool_twitter_keyword_limit : 0;
            $organisation->data_pool_twitter_user_limit = $request->data_pool_twitter_user_limit ? $request->data_pool_twitter_user_limit : 0;
            $organisation->data_pool_instagram_follow_limit = $request->data_pool_instagram_follow_limit ? $request->data_pool_instagram_follow_limit : 0;

            $organisation->unit_price = $calculate['total_price'];

            $organisation->module_real_time = $request->module_real_time ? true : false;
            $organisation->module_search = $request->module_search ? true : false;
            $organisation->module_trend = $request->module_trend ? true : false;
            $organisation->module_compare = $request->module_compare ? true : false;
            $organisation->module_borsa = $request->module_borsa ? true : false;
            $organisation->module_report = $request->module_report ? true : false;
            $organisation->module_alarm = $request->module_alarm ? true : false;

            $organisation->demo = false;

            /*!
             * modules
             */
            foreach (config('system.modules') as $key => $module)
            {
                $organisation->{'data_'.$key} = $request->{'data_'.$key} ? true : false;
            }

            $organisation->save();

            $message = [
                'title' => 'Organizasyon Yükseltildi',
                'info' => 'Tebrikler! Organizasyonunuz başarılı bir şekilde yükseltildi.',
                'body' => 'Deneme süreniz 1 gün daha uzatıldı. 1 gün sonunda kesintisiz bir şekilde devam edebilmek için lütfen "Organizasyon Ayarları" sayfasından paketinizi uzatın.'
            ];

            $user->notify((new MessageNotification('Olive: '.$message['title'], $message['info'], $message['body']))->onQueue('email'));

            Activity::push(
                $message['title'],
                [
                    'icon' => 'access_time',
                    'markdown' => $message['body'],
                    'user_id' => $user->id,
                    'key' => implode('-', [ $user->id, 'welcome' ])
                ]
            );

            if (!$user->badge(12))
            {
                ### [ organizatör ] ###
                $user->addBadge(12);
            }

            return [
                'status' => 'ok'
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'reason' => 'Lütfen ilk önce "Hesap Bilgileri -> Mobil" sayfasından bir GSM numarası tanımlayın!'
            ];
        }
    }

    /**
     *
     * Organizasyon, Ödeme Bildirimleri
     *
     * @return mixed
     */
    public static function checkUpcomingPayments()
    {
        $organisations = Organisation::whereBetween('end_date', [ Carbon::now()->subDays(2), Carbon::now()->addDays(2) ])->get();

        if (count($organisations))
        {
            foreach ($organisations as $organisation)
            {
                echo Term::line($organisation->name);

                if ($organisation->status == true && $organisation->demo == true)
                {
                    if ($organisation->days() <= 0)
                    {
                        $message = [
                            'title' => 'Deneme Süreniz Bitti :(',
                            'info' => '1 gün daha ücretsiz denemek ister misiniz?',
                            'body' => implode(PHP_EOL, [
                                'Paketinizi şimdi yükseltin ve sadece istediğiniz özellikleri seçerek araştırmalarınıza kaldığınız yerden devam edin.'
                            ])
                        ];

                        $organisation->status = false;
                        $organisation->save();
                    }
                    else if ($organisation->days() > 0)
                    {
                        $message = [
                            'title' => 'Yenileyin',
                            'info' => $organisation->days() ? $organisation->days().' gün kaldı!' : 'Son gün!',
                            'body' => implode(PHP_EOL, [
                                'Paketinizi şimdi yükseltin ve sadece istediğiniz özellikleri seçerek araştırmalarınıza kaldığınız yerden devam edin.'
                            ])
                        ];
                    }
                }
                else if ($organisation->status == true && $organisation->demo == false)
                {
                    if ($organisation->days() <= 0)
                    {
                        $message = [
                            'title' => 'Ödeme Gerçekleşmedi',
                            'info' => 'Hizmet Süreniz Doldu',
                            'body' => implode(PHP_EOL, [
                                'Araştırmalarınıza kaldığınız yerden devam edebilmek için lütfen hizmet sürenizi uzatın.'
                            ])
                        ];

                        $organisation->status = false;
                        $organisation->save();
                    }
                    else if ($organisation->days() > 0)
                    {
                        $message = [
                            'title' => 'Ödeme Gerekiyor',
                            'info' => $organisation->days() ? $organisation->days().' gün kaldı!' : 'Son gün!',
                            'body' => implode(PHP_EOL, [
                                'Kesinti yaşamamak için hizmet sürenizi uzatmanız gerekiyor.'
                            ])
                        ];
                    }
                }
                else if ($organisation->status == false && $organisation->demo == true)
                {
                    $message = [
                        'title' => '1 Gün Daha Deneyin!',
                        'info' => 'Deneme süreniz bitti :( 1 gün daha ücretsiz denemek ister misiniz?',
                        'body' => implode(PHP_EOL, [
                            'Paketinizi şimdi yükseltin ve sadece istediğiniz özellikleri seçerek araştırmalarınıza kaldığınız yerden devam edin.'
                        ])
                    ];
                }
                else if ($organisation->status == false && $organisation->demo == false)
                {
                    $message = [
                        'title' => 'Hemen Şimdi Uzatın',
                        'info' => 'Hizmet Süreniz Doldu',
                        'body' => implode(PHP_EOL, [
                            'Araştırmalarınıza kaldığınız yerden devam edebilmek için lütfen hizmet sürenizi uzatın.'
                        ])
                    ];
                }

                /*!
                 * email
                 */
                $author = $organisation->author;

                $author->notify((new MessageNotification('Olive: '.$message['title'], $message['info'], $message['body']))->onQueue('email'));

                Activity::push(
                    $message['title'],
                    [
                        'icon' => 'access_time',
                        'markdown' => $message['body'],
                        'user_id' => $organisation->user_id,
                        'key' => implode('-', [ $organisation->user_id, 'upcoming_payments' ]),
                        'button' => [
                            'type' => 'http',
                            'method' => 'GET',
                            'action' => route('settings.organisation').'#tab-2',
                            'class' => 'btn-flat waves-effect',
                            'text' => 'Uzatın'
                        ]
                    ]
                );
            }
        }
        else
        {
            echo Term::line('İşlem yok!');
        }
    }

    /**
     *
     * Organizasyon, Ayarlar
     *
     * @return view
     */
    public static function settings()
    {
        $user = auth()->user();

        if ($user->organisation->demo == true)
        {
            $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

            return view('organisation.create.offer', compact('user', 'prices'));
        }
        else
        {
            $discount_with_year = System::option('formal.discount_with_year');

            return view('organisation.settings', compact('user', 'discount_with_year'));
        }
    }

    /**
     *
     * Organizasyon, Ad Güncelle
     *
     * @return array
     */
    public static function updateName(NameRequest $request)
    {
        auth()->user()->organisation->update([ 'name' => $request->organisation_name ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     *
     * Organizasyon, Davet Gönder
     *
     * @return array
     */
    public static function invite(InviteRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!@$user)
        {
            $new_password = str_random(6);
            $new_name = null;
            $name_i = 1;
            $explode_email = explode('@', $request->email);

            while ($new_name === null)
            {
                $new_name = str_slug($name_i == 1 ? $explode_email[0] : $explode_email[0].'-'.$name_i);

                $new_name = @User::where('name', $new_name)->exists() ? null : $new_name;

                $name_i++;
            }

            $user = new User;
            $user->name = $new_name;
            $user->email = strtolower($request->email);
            $user->verified = true;
            $user->password = bcrypt($new_password);
            $user->session_id = str_random(100);
            $user->save();

            foreach (config('system.notifications') as $key => $title)
            {
                UserNotification::create([
                    'user_id' => $user->id,
                    'key' => $key
                ]);
            }

            $user = User::find($user->id);

            $user->notify((new SendPasswordNotification($user->email, $new_password))->onQueue('email'));
        }

        $user->organisation_id = auth()->user()->organisation_id;
        $user->save();

        $title = 'Organizasyona Eklendiniz';
        $message = $user->organisation->name.'; '.auth()->user()->name.' tarafından eklendiniz.';

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification(
                        'Olive: '.$title,
                        'Merhaba, '.$user->name,
                        $message
                    )
                )->onQueue('email')
            );
        }

        Activity::push(
            $title,
            [
                'icon' => 'exit_to_app',
                'markdown' => $message,
                'user_id' => $user->id
            ]
        );

        return [
            'status' => 'ok',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar()
            ]
        ];
    }

    /**
     *
     * Organizasyon, Ayrıl
     *
     * @return array
     */
    public static function leave(LeaveRequest $request)
    {
        $user = auth()->user();

        session()->flash('leaved', true);

        $title = 'Organizasyondan Ayrıldınız';
        $message = $user->organisation->name.'; kendi isteğiniz üzerine ayrıldınız.';

        Activity::push(
            $title,
            [
                'icon' => 'exit_to_app',
                'markdown' => $message
            ]
        );

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification(
                        'Olive: '.$title,
                        'Merhaba, '.$user->name,
                        $message
                    )
                )->onQueue('email')
            );
        }

        $user->organisation_id = null;
        $user->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     *
     * Organizasyon, Devret
     *
     * @return array
     */
    public static function transfer(TransferAndRemoveRequest $request)
    {
        $user = auth()->user();

        $transferred_user = User::where('id', $request->user_id)->first();

        $organisation = $user->organisation;
        $organisation->user_id = $transferred_user->id;
        $organisation->save();

        ### [ devreden için bilgilendirme ] ###
        $title = 'Organizasyon Devredildi';
        $message = $user->organisation->name.', '.$transferred_user->name.' üzerine devredildi.';

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification(
                        'Olive: '.$title,
                        'Merhaba, '.$user->name,
                        $message
                    )
                )->onQueue('email')
            );
        }

        Activity::push(
            $title,
            [
                'icon' => 'accessibility',
                'markdown' => $message
            ]
        );

        ### [ devralan için bilgilendirme ] ###
        $title = 'Organizasyon Devredildi';
        $message = $user->organisation->name.', '.$user->name.' tarafından size devredildi.';

        if ($transferred_user->notification('important'))
        {
            $transferred_user->notify(
                (
                    new MessageNotification(
                        'Olive: '.$title,
                        'Merhaba, '.$transferred_user->name,
                        $message
                    )
                )->onQueue('email')
            );
        }

        Activity::push(
            $title,
            [
                'icon' => 'accessibility',
                'markdown' => $message,
                'user_id' => $transferred_user->id
            ]
        );

        session()->flash('transferred', true);

        return [
            'status' => 'ok'
        ];
    }

    /**
     *
     * Organizasyon, Kullanıcı Çıkar
     *
     * @return array
     */
    public static function remove(TransferAndRemoveRequest $request)
    {
        $user = auth()->user();

        $removed_user = User::where('id', $request->user_id)->first();

        if ($removed_user->term_version == 1)
        {
            $removed_user->delete();
        }
        else
        {
            $removed_user->organisation_id = null;
            $removed_user->save();

            $title = 'Organizasyondan Çıkarıldınız';
            $message = $user->organisation->name.'; '.$user->name.' tarafından çıkarıldınız.';

            if ($removed_user->notification('important'))
            {
                $removed_user->notify(
                    (
                        new MessageNotification(
                            'Olive: '.$title,
                            'Merhaba, '.$removed_user->name,
                            $message
                        )
                    )->onQueue('email')
                );
            }

            Activity::push(
                $title,
                [
                    'icon' => 'exit_to_app',
                    'markdown' => $message,
                    'user_id' => $removed_user->id
                ]
            );
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     *
     * Organizasyon, Ödeme Durum Yönlendirme
     *
     * @return redirect
     */
    public static function paymentStatus(string $status)
    {
        session()->flash('success', $status);

        return redirect()->route('organisation.invoice.payment');
    }

    /**
     *
     * Organizasyon, Ödeme Bildirim (Api Service)
     *
     * !! API SERVICE !!
     *
     * @return string
     */
    public static function paymentCallback(PaymentCallbackRequest $request)
    {
        $merchant_key = config('services.paytr.merchant.key');
        $merchant_salt = config('services.paytr.merchant.salt');

        $invoice = Invoice::where('invoice_id', $request->merchant_oid)->first();

        if (@$invoice)
        {
            $organisation = $invoice->organisation;

            $hash = base64_encode(
                hash_hmac(
                    'sha256',
                    $request->merchant_oid.$merchant_salt.$request->status.$request->total_amount,
                    $merchant_key,
                    true
                )
            );

            if ($hash != $request->hash)
            {
                $invoice->reason_code = 0;
                $invoice->reason_msg = 'Bağlantı zaman aşımına uğradı.';
                $invoice->save();

                return 'OK';
            }

            if ($request->status == 'success' && $invoice->paid_at === null)
            {
                $organisation->status = true;
                $organisation->start_date = $organisation->invoices()->count() == 1 ? date('Y-m-d H:i:s') : $organisation->start_date;

                $add_month = new Carbon($organisation->invoices()->count() == 1 ? $organisation->start_date : $organisation->end_date);
                $add_month = $add_month->addMonths($invoice->month);

                $organisation->end_date = $add_month;
                $organisation->save();

                $title = 'Olive: Fatura Onayı';
                $greeting = 'Tebrikler! Faturanız Onaylandı.';
                $message = 'Ödemeniz başarılı bir şekilde gerçekleştirildi ve organizasyonunuz aktif edildi.';
                $message = 'Olive kullandığınız için teşekkür eder, iyi araştırmalar dileriz.';

                $author = $organisation->author;

                $author->notify(
                    (
                        new MessageNotification(
                            $title,
                            $greeting,
                            $message
                        )
                    )->onQueue('email')
                );

                Activity::push(
                    $greeting,
                    [
                        'user_id' => $author->id,
                        'icon' => 'check',
                        'markdown' => $message
                    ]
                );

                if (!$author->badge(999))
                {
                    $author->addBadge(999); // destekçi
                }

                if ($author->reference && @$author->reference->partner)
                {
                    /* --- */
                    $consumer = $author;
                    $reference = $author->reference;

                    $each = true;

                    $paydaslar = [];

                    while ($each)
                    {
                        $pay_orani = $reference->sub_partner_percent ? $reference->sub_partner_percent : System::option('formal.partner.'.$reference->partner.'.percent');

                        $paydaslar[] = [
                            'user_id' => $reference->id,
                            'name' => $reference->name,
                            'email' => $reference->email,
                            'percent' => $pay_orani,
                            'consumer' => [
                                'email' => $consumer->email
                            ]
                        ];

                        $consumer = $reference;
                        $reference = User::where('id', $reference->partner_user_id)->first();

                        if (!$reference) break;
                    }

                    $paydaslar = array_reverse($paydaslar);

                    $kalan_pay = $invoice->total_price / 100 * $paydaslar[0]['percent'];

                    foreach ($paydaslar as $key => $paydas)
                    {
                        if ($key > 0)
                        {
                            $kalan_pay = $kalan_pay / 100 * $paydas['percent'];
                        }

                        $paydaslar[$key]['pay'] = $kalan_pay;
                    }

                    foreach ($paydaslar as $key => $paydas)
                    {
                        if (@$paydaslar[$key+1])
                        {
                            $paydaslar[$key]['pay'] = $paydas['pay'] - $paydaslar[$key+1]['pay'];
                        }
                    }

                    foreach ($paydaslar as $key => $paydas)
                    {
                        $pay = new PartnerPayment;
                        $pay->currency = config('formal.currency_text');
                        $pay->amount = $paydas['pay'];
                        $pay->status = 'success';
                        $pay->message = $paydas['consumer']['email'].' tarafından bir ödeme alındı.';
                        $pay->user_id = $paydas['user_id'];
                        $pay->save();
                    }
                    /* --- */
                }

                $invoice->total_amount = $request->total_amount;
                $invoice->paid_at = date('Y-m-d H:i:s');
                $invoice->method = 'PAYTR';
                $invoice->save();
            }
            else
            {
                $invoice->reason_code = $request->failed_reason_code;
                $invoice->reason_msg = $request->failed_reason_msg;
                $invoice->save();
            }
        }

        return 'OK';
    }

    /**
     *
     * Organizasyon, Ödeme Sayfası
     *
     * @return view
     */
    public static function payment(Request $request)
    {
        $user = auth()->user();
        $organisation = $user->organisation;
        $invoice = $organisation->invoices[0];

        if ($invoice->paid_at)
        {
            $reason = 'clean';
        }
        else
        {
            $merchant_id = config('services.paytr.merchant.id');
            $merchant_key = config('services.paytr.merchant.key');
            $merchant_salt = config('services.paytr.merchant.salt');

            $ip = $request->ip();

            $user_ip = $ip;

            $merchant_oid = $invoice->invoice_id;

            $name = [];

            $name[] = $invoice->info->person_name;
            $name[] = $invoice->info->person_lastname;

            if ($invoice->info->merchant_name)
            {
                $name[] = '('.$invoice->info->merchant_name.')';
            }

            $user_name = implode(' ', $name);

            $user_address = implode(
                ' ',
                [
                    $invoice->info->address,
                    $invoice->info->postal_code,
                    $invoice->info->city.'/'.$invoice->info->state->name.'/'.$invoice->info->country->name
                ]
            );
            $user_phone = $invoice->info->phone;

            $user_basket = base64_encode(
                json_encode(
                    [
                        [
                            '1 Aylık Olive Aboneliği #'.$organisation->id,
                            $invoice->unit_price,
                            $invoice->month,
                        ]
                    ]
                )
            );

            $email = $user->email;
            $payment_amount = $invoice->fee()->amount_int;
            $test_mode = intval(config('app.env') == 'local');
            $debug_on = intval(config('app.debug'));
            $timeout_limit = 10;
            $no_installment = config('formal.installment.status') == true ? 0 : 1;
            $max_installment = config('formal.installment.max');
            $currency = config('formal.currency_text');

            $hash_str = $merchant_id.$user_ip.$merchant_oid.$email.$payment_amount.$user_basket.$no_installment.$max_installment.$currency.$test_mode;
            $paytr_token = base64_encode(hash_hmac('sha256', $hash_str.$merchant_salt, $merchant_key, true));

            $post_vals = [
                'merchant_id' => $merchant_id,
                'user_ip' => $user_ip,
                'merchant_oid' => $merchant_oid,
                'email' => $email,
                'payment_amount' => $payment_amount,
                'paytr_token' => $paytr_token,
                'user_basket' => $user_basket,
                'debug_on' => $debug_on,
                'no_installment' => $no_installment,
                'max_installment' => $max_installment,
                'user_name' => $user_name,
                'user_address' => $user_address,
                'user_phone' => $user_phone,
                'merchant_ok_url' => route('organisation.invoice.payment.status', 'ok'),
                'merchant_fail_url' => route('organisation.invoice.payment.status', 'fail'),
                'timeout_limit' => $timeout_limit,
                'currency' => $currency,
                'test_mode' => $test_mode
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1) ;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $result = @curl_exec($ch);

            if (curl_errno($ch))
            {
                $reason = curl_error($ch);
            }

            curl_close($ch);

            $result = json_decode($result, true);

            if ($result['status'] == 'success')
            {
                $token = $result['token'];
            }
            else
            {
                if ($result['reason'])
                {
                    $reason = $result['reason'];
                }
                else
                {
                    $reason = 'done';
                }
            }
        }

        return view('organisation.payment', compact('user', 'organisation', 'invoice', 'token', 'reason'));
    }

    /**
     *
     * Organizasyon, Süre Uzat
     *
     * @return array
     */
    public static function update(BillingUpdateRequest $request)
    {
        $user = auth()->user();

        if ($user->organisation->demo)
        {
            return [
                'status' => 'err',
                'message' => 'Deneme aşamasında ödeme gerçekleştiremezsiniz!'
            ];
        }

        $discount_rate = $request->month >= System::option('formal.discount_with_year');

        $billing_information = new BillingInformation;
        $billing_information->user_id = $user->id;
        $billing_information->fill($request->all());
        $billing_information->save();

        $invoice_id = $user->id.$user->organisation_id.rand(10, 99);

        $unit_price = $user->organisation->unit_price;

        $total_price = $request->month * $unit_price;

        Invoice::create([
                        'invoice_id' => $invoice_id,
                   'organisation_id' => $user->organisation_id,
                           'user_id' => $user->id,

                        'unit_price' => $unit_price,
                             'month' => $request->month,
                       'total_price' => $total_price,
                               'tax' => config('formal.tax'),

                              'plan' => $user->organisation->toJson(),
                     'discount_rate' => $discount_rate,

            'billing_information_id' => $billing_information->id,
        ]);

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new OrganisationWasUpdatedNotification(
                        $user->name,
                        $user->organisation_id
                    )
                )->onQueue('email')
            );
        }

        Activity::push(
            'Fatura oluşturuldu',
            [
                'icon' => 'flag',
                'markdown' => implode(PHP_EOL, [
                    'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.',
                    'Ödemenizi gerçekleştirdikten sonra, e-posta ile bildilendirileceksiniz.'
                ]),
                'button' => [
                    'type' => 'http',
                    'method' => 'GET',
                    'action' => route('organisation.invoice', [ 'id' => $invoice_id ]),
                    'class' => 'btn-flat waves-effect',
                    'text' => 'Fatura'
                ]
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    /**
     *
     * Organizasyon, Fatura
     *
     * @return view
     */
    public static function invoice(int $id, string $key = '')
    {
        if (auth()->guest() && $key != md5(config('app.key')))
        {
            abort(404);
        }

        $invoice = Invoice::join('users', 'organisation_invoices.user_id', '=', 'users.id')
                          ->where('invoice_id', $id)
                          ->where(function($q) {
                                $q->orWhere(function ($query) {
                                    if (auth()->check())
                                    {
                                        $user = auth()->user();

                                        if ($user->root() || $user->admin())
                                        {
                                            $query->orWhere('invoice_id', '>', 0);
                                        }
                                        else
                                        {
                                            $query->orWhere('organisation_invoices.organisation_id', $user->organisation_id)
                                                  ->orWhere('organisation_invoices.user_id', $user->id);
                                        }
                                    }
                                });
                                if (auth()->check())
                                {
                                    $q->orWhere('users.partner_user_id', auth()->user()->id);
                                }
                          })
                          ->firstOrFail();

        return view('organisation.invoice', compact('invoice'));
    }

    /**
     *
     * Organizasyon, Fatura İptal
     *
     * @return array
     */
    public static function invoiceCancel()
    {
        $user = auth()->user();

        $invoice = Invoice::where('invoice_id', $user->organisation->invoices[0]->invoice_id)->whereNull('paid_at')->delete();

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification
                    (
                        'Olive: Fatura İptal Edildi',
                        'Faturanızı İptal Ettiniz!',
                        'Organizasyon ödemesi için oluşturduğunuz fatura, ödeme tamamlanmadan iptal edildi.'
                    )
                )->onQueue('email')
            );
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
     * Organizasyon Fiyatlandırma Ayarları
     *
     * @return view
     */
    public static function adminPriceSettings()
    {
        $settings = Option::select('key', 'value')
                          ->where(function($query) {
                              $query->orWhere('key', 'LIKE', 'unit_price.%');
                              $query->orWhere('key', 'formal.discount_with_year');
                              $query->orWhere('key', 'LIKE', 'formal.partner.%');
                          })
                          ->get()
                          ->keyBy('key')
                          ->toArray();

        return view('organisation.admin.priceSettings', compact('settings'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon Fiyatlandırma Ayarları Kaydet
     *
     * @return array
     */
    public static function adminPriceSettingsSave(PriceSettingsSaveRequest $request)
    {
        System::log(
            json_encode([
                'Fiyat ayarlarını güncelledi.',
                auth()->user()->email,
                json_encode($request->all())
            ]),
            'App\Http\Controllers\OrganisationController::adminPriceSettingsSave('.auth()->user()->email.')', 10
        );

        Option::updateOrCreate([ 'key' => 'unit_price.data_twitter'                    ], [ 'value' => $request->data_twitter                    ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_sozluk'                     ], [ 'value' => $request->data_sozluk                     ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_news'                       ], [ 'value' => $request->data_news                       ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_blog'                       ], [ 'value' => $request->data_news                       ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_youtube_video'              ], [ 'value' => $request->data_youtube_video              ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_youtube_comment'            ], [ 'value' => $request->data_youtube_comment            ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_shopping'                   ], [ 'value' => $request->data_shopping                   ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_instagram'                  ], [ 'value' => $request->data_instagram                  ]);

        Option::updateOrCreate([ 'key' => 'unit_price.pin_group_limit'                 ], [ 'value' => $request->pin_group_limit                 ]);
        Option::updateOrCreate([ 'key' => 'unit_price.saved_searches_limit'            ], [ 'value' => $request->saved_searches_limit            ]);
        Option::updateOrCreate([ 'key' => 'unit_price.historical_days'                 ], [ 'value' => $request->historical_days                 ]);

        Option::updateOrCreate([ 'key' => 'unit_price.data_pool_youtube_channel_limit'  ], [ 'value' => $request->data_pool_youtube_channel_limit  ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_pool_youtube_video_limit'    ], [ 'value' => $request->data_pool_youtube_video_limit    ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_pool_youtube_keyword_limit'  ], [ 'value' => $request->data_pool_youtube_keyword_limit  ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_pool_twitter_keyword_limit'  ], [ 'value' => $request->data_pool_twitter_keyword_limit  ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_pool_twitter_user_limit'     ], [ 'value' => $request->data_pool_twitter_user_limit     ]);
        Option::updateOrCreate([ 'key' => 'unit_price.data_pool_instagram_follow_limit' ], [ 'value' => $request->data_pool_instagram_follow_limit ]);

        Option::updateOrCreate([ 'key' => 'unit_price.module_real_time'                ], [ 'value' => $request->module_real_time                ]);
        Option::updateOrCreate([ 'key' => 'unit_price.module_search'                   ], [ 'value' => $request->module_search                   ]);
        Option::updateOrCreate([ 'key' => 'unit_price.module_trend'                    ], [ 'value' => $request->module_trend                    ]);
        Option::updateOrCreate([ 'key' => 'unit_price.module_compare'                  ], [ 'value' => $request->module_compare                  ]);
        Option::updateOrCreate([ 'key' => 'unit_price.module_borsa'                    ], [ 'value' => $request->module_borsa                    ]);
        Option::updateOrCreate([ 'key' => 'unit_price.module_report'                   ], [ 'value' => $request->module_report                   ]);
        Option::updateOrCreate([ 'key' => 'unit_price.module_alarm'                    ], [ 'value' => $request->module_alarm                    ]);

        Option::updateOrCreate([ 'key' => 'unit_price.user'                            ], [ 'value' => $request->user_price                      ]);

        Option::updateOrCreate([ 'key' => 'formal.discount_with_year'                  ], [ 'value' => $request->discount_with_year              ]);

        Option::updateOrCreate([ 'key' => 'formal.partner.eagle.percent'               ], [ 'value' => $request->eagle_percent                   ]);
        Option::updateOrCreate([ 'key' => 'formal.partner.phoenix.percent'             ], [ 'value' => $request->phoenix_percent                 ]);
        Option::updateOrCreate([ 'key' => 'formal.partner.gryphon.percent'             ], [ 'value' => $request->gryphon_percent                 ]);
        Option::updateOrCreate([ 'key' => 'formal.partner.dragon.percent'              ], [ 'value' => $request->dragon_percent                  ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon Listesi
     *
     * @return view
     */
    public static function adminListView(string $status = '')
    {
        return view('organisation.admin.list', compact('status'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon Listesi
     *
     * @return array
     */
    public static function adminListViewJson(SearchRequest $request)
    {
        $request->validate([
            'status' => 'nullable|in:on,off'
        ]);

        $take = $request->take;
        $skip = $request->skip;

        $query = new Organisation;
        $query = $query->with('author');
        $query = $request->status ? $query->where('status', $request->status == 'on' ? true : false) : $query;
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%') : $query;

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC')
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
     * @return array
     */
    public static function calculate($request)
    {
        $arr = [
            'historical_days'                  => '*',
            'pin_group_limit'                  => '*',
            'saved_searches_limit'             => '*',

            'module_real_time'                 => '+',
            'module_search'                    => '+',
            'module_trend'                     => '+',
            'module_compare'                   => '+',
            'module_borsa'                     => '+',
            'module_report'                    => '+',
            'module_alarm'                     => '+',

            'data_pool_youtube_channel_limit'  => '*',
            'data_pool_youtube_video_limit'    => '*',
            'data_pool_youtube_keyword_limit'  => '*',
            'data_pool_twitter_keyword_limit'  => '*',
            'data_pool_twitter_user_limit'     => '*',
            'data_pool_instagram_follow_limit' => '*',
        ];

        foreach (config('system.modules') as $key => $module)
        {
            $arr['data_'.$key] = '+';
        }

        $math_prices = 0;

        $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

        foreach ($arr as $key => $group)
        {
            if ($group == '+' && $request->{$key} == 'on')
            {
                $math_prices = $math_prices + $prices['unit_price.'.$key]['value'];
            }
            else if ($group == '*')
            {
                $math_prices = $math_prices + ($request->{$key} * $prices['unit_price.'.$key]['value']);
            }
        }

        $math_prices = $math_prices + ($request->user_capacity * $prices['unit_price.user']['value']);
        $math_prices = $math_prices - $prices['unit_price.user']['value'];

        return [
            'total_price' => $math_prices
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon Sayfası
     *
     * @return view
     */
    public static function adminView(int $id)
    {
        $prices = Option::select('key', 'value')->where('key', 'LIKE', 'unit_price.%')->get()->keyBy('key')->toArray();

        $organisation = Organisation::where('id', $id)->firstOrFail();
        $reference = $organisation->author->reference;

        $partner_percent = $reference ? System::option('formal.partner.'.$reference->partner.'.percent') : 0;

        return view('organisation.admin.view', compact('organisation', 'prices', 'partner_percent'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon Güncelle
     *
     * @return array
     */
    public static function adminUpdate(int $id, AdminUpdateRequest $request)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        $calculate = self::calculate($request);

        $organisation->name = $request->name;
        $organisation->user_capacity = $request->user_capacity;
        $organisation->status = $request->status ? true : false;
        $organisation->end_date = $request->end_date.' '.$request->end_time;
        $organisation->historical_days = $request->historical_days;
        $organisation->pin_group_limit = $request->pin_group_limit;
        $organisation->saved_searches_limit = $request->saved_searches_limit;

        $organisation->data_pool_youtube_channel_limit = $request->data_pool_youtube_channel_limit;
        $organisation->data_pool_youtube_video_limit = $request->data_pool_youtube_video_limit;
        $organisation->data_pool_youtube_keyword_limit = $request->data_pool_youtube_keyword_limit;
        $organisation->data_pool_twitter_keyword_limit = $request->data_pool_twitter_keyword_limit;
        $organisation->data_pool_twitter_user_limit = $request->data_pool_twitter_user_limit;
        $organisation->data_pool_instagram_follow_limit = $request->data_pool_instagram_follow_limit;

        $organisation->unit_price = $calculate['total_price'];

        $organisation->module_real_time = $request->module_real_time ? true : false;
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

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon Oluştur
     *
     * @return array
     */
    public static function adminCreate(AdminCreateRequest $request)
    {
        $user = User::where('name', $request->user_name)->first();

        if (@$user)
        {
            $organisation = new Organisation;
            $organisation->name = $request->organisation_name;
            $organisation->user_id = $user->id;
            $organisation->start_date = date('Y-m-d H:i:s');
            $organisation->end_date = date('Y-m-d H:i:s', strtotime('+1 day'));
            $organisation->save();

            $user->organisation_id = $organisation->id;
            $user->save();

            return [
                'status' => 'ok',
                'data' => [
                    'route' => route('admin.organisation', $organisation->id)
                ]
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'reason' => 'Kullanıcı bulunamadı!'
            ];
        }
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, kayıtlı aramalar.
     *
     * @return view
     */
    public static function adminSavedSearches(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.savedSearches', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, kayıtlı arama form.
     *
     * @return view
     */
    public static function adminSavedSearch(int $id, int $search_id = null)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();
        $search = $search_id ? $organisation->savedSearches()->where('id', $search_id)->firstOrFail() : null;
        $states = States::where('country_id', 223)->orderBy('name', 'ASC')->get();

        return view('organisation.admin.savedSearch', compact('organisation', 'search', 'states'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, kayıtlı arama güncelle.
     *
     * @return array
     */
    public static function adminSavedSearchSave(int $id, int $search_id = null, AdminSaveRequest $request)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();
        $search = $search_id ? $organisation->savedSearches()->where('id', $search_id)->firstOrFail() : null;

        if (!$search)
        {
            Validator::extend('limit', function($attribute) use ($organisation) {
                return $organisation->savedSearches->count() < $organisation->saved_searches_limit;
            }, 'Bu organizasyon arama kaydetme üst limitine ulaştı.');

            $request->validate([
                'name' => 'limit'
            ]);
        }

        if ($search)
        {
            $status = 'updated';
        }
        else
        {
            $status = 'created';

            $search = new SavedSearch;
            $search->organisation_id = $organisation->id;
        }

        $search->fill($request->all());
        $search->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $status
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, ödeme geçmişi.
     *
     * @return view
     */
    public static function adminInvoiceHistory(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.invoiceHistory', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, fatura onayı.
     *
     * @return redirect
     */
    public static function adminInvoiceApprove(int $id, InvoiceApproveRequest $request)
    {
        $invoice = Invoice::where('invoice_id', $id)->firstOrFail();

        $invoice->serial = $request->serial;
        $invoice->no = $request->no;

        if ($request->approve && $invoice->paid_at == null)
        {
            $organisation = $invoice->organisation;

            $author = $organisation->author;

            if ($author->reference && @$author->reference->partner)
            {
                /* --- */
                $consumer = $author;
                $reference = $author->reference;

                $each = true;

                $paydaslar = [];

                while ($each)
                {
                    $pay_orani = $reference->sub_partner_percent ? $reference->sub_partner_percent : System::option('formal.partner.'.$reference->partner.'.percent');

                    $paydaslar[] = [
                        'user_id' => $reference->id,
                        'name' => $reference->name,
                        'email' => $reference->email,
                        'percent' => $pay_orani,
                        'consumer' => [
                            'email' => $consumer->email
                        ]
                    ];

                    $consumer = $reference;
                    $reference = User::where('id', $reference->partner_user_id)->first();

                    if (!$reference) break;
                }

                $paydaslar = array_reverse($paydaslar);

                $kalan_pay = $invoice->total_price / 100 * $paydaslar[0]['percent'];

                foreach ($paydaslar as $key => $paydas)
                {
                    if ($key > 0)
                    {
                        $kalan_pay = $kalan_pay / 100 * $paydas['percent'];
                    }

                    $paydaslar[$key]['pay'] = $kalan_pay;
                }

                foreach ($paydaslar as $key => $paydas)
                {
                    if (@$paydaslar[$key+1])
                    {
                        $paydaslar[$key]['pay'] = $paydas['pay'] - $paydaslar[$key+1]['pay'];
                    }
                }

                foreach ($paydaslar as $key => $paydas)
                {
                    $pay = new PartnerPayment;
                    $pay->currency = config('formal.currency_text');
                    $pay->amount = $paydas['pay'];
                    $pay->status = 'success';
                    $pay->message = $paydas['consumer']['email'].' tarafından bir ödeme alındı.';
                    $pay->user_id = $paydas['user_id'];
                    $pay->save();
                }
                /* --- */
            }

            $organisation->status = true;
            $organisation->start_date = $organisation->invoices()->count() == 1 ? date('Y-m-d H:i:s') : $organisation->start_date;

            $add_month = new Carbon($organisation->invoices()->count() == 1 ? $organisation->start_date : $organisation->end_date);
            $add_month = $add_month->addMonths($invoice->month);

            $organisation->end_date = $add_month;
            $organisation->save();

            $title = 'Olive: Fatura Onayı';
            $greeting = 'Faturanız Onaylandı!';
            $message = 'Organizasyonunuzu aktifleştirdik. İyi araştırmalar dileriz...';

            if ($author->notification('important'))
            {
                $author->notify(
                    (
                        new MessageNotification(
                            $title,
                            $greeting,
                            $message
                        )
                    )->onQueue('email')
                );
            }

            Activity::push(
                $greeting,
                [
                    'user_id' => $author->id,
                    'icon' => 'check',
                    'markdown' => $message
                ]
            );

            if (!$author->badge(999))
            {
                $author->addBadge(999); // destekçi
            }

            $invoice->paid_at = date('Y-m-d H:i:s');
            $invoice->method = 'HAVALE/EFT';
        }

        $invoice->save();

        return redirect()->route('organisation.invoice', $invoice->invoice_id);
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, Alarmları
     *
     * @return view
     */
    public static function alarms(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.alarms', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, Alarmları
     *
     * @return array
     */
    public function alarmListJson(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        $query = Alarm::with('search')->where('organisation_id', $organisation->id)->orderBy('id', 'DESC')->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => count($query)
        ];
    }

    ### ### ###

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, Pin Grupları
     *
     * @return view
     */
    public function pinGroups(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.pinGroups', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, Pin Grupları
     *
     * @return array
     */
    public function pinGroupListJson(int $id, SearchRequest $request)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        $take = $request->take;
        $skip = $request->skip;

        $query = PinGroup::withCount('pins');
        $query = $query->where('organisation_id', $organisation->id);
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC')
                       ->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => count($query)
        ];
    }
}
