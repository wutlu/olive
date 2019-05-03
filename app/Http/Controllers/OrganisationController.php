<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility as Activity;
use App\Utilities\Term;

use App\Models\Organisation\Organisation;
use App\Models\Organisation\OrganisationInvoice as Invoice;
use App\Models\User\User;
use App\Models\RealTime\KeywordGroup;
use App\Models\Pin\Group as PinGroup;
use App\Models\BillingInformation;
use App\Models\Alarm;

use App\Http\Requests\Organisation\BillingUpdateRequest;
use App\Http\Requests\Organisation\NameRequest;
use App\Http\Requests\Organisation\TransferAndRemoveRequest;
use App\Http\Requests\Organisation\InviteRequest;
use App\Http\Requests\Organisation\LeaveRequest;
use App\Http\Requests\Organisation\DeleteRequest;
use App\Http\Requests\Organisation\Admin\UpdateRequest as AdminUpdateRequest;
use App\Http\Requests\Organisation\Admin\CreateRequest as AdminCreateRequest;
use App\Http\Requests\Organisation\Admin\InvoiceApproveRequest;

use App\Http\Requests\RealTime\KeywordGroup\AdminUpdateRequest as KeywordGroupAdminUpdateRequest;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

use App\Notifications\OrganisationWasCreatedNotification;
use App\Notifications\OrganisationWasUpdatedNotification;
use App\Notifications\MessageNotification;
use App\Notifications\SendPasswordNotification;

use Carbon\Carbon;

use App\Jobs\CheckUpcomingPayments;

use App\Http\Requests\PaymentCallbackRequest;

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
        ]);

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyonu Olmayanlar
         */
        $this->middleware('organisation:have_not')->only([
            'offer'
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
        return view('organisation.create.offer');
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

                if ($organisation->status == true)
                {
                    if ($organisation->days() <= 0)
                    {
                        $message = [
                            'title' => 'Üzgünüm :(',
                            'info' => 'Organizasyon Süreniz Doldu',
                            'body' => implode(PHP_EOL, [
                                'Araçlardan tekrar faydalanabilmek için organizasyon sürenizi uzatmanız gerekiyor.'
                            ])
                        ];

                        $organisation->status = false;
                        $organisation->save();
                    }
                    else
                    {
                        $day_message = $organisation->days() ? $organisation->days().' gün kaldı!' : 'Son gün!';
                        $message = [
                            'title' => 'Yenileyin',
                            'info' => $day_message,
                            'body' => implode(PHP_EOL, [
                                'Kesinti yaşamamak için organizasyon sürenizi uzatmanız gerekiyor.'
                            ])
                        ];
                    }
                }

                if (@$message)
                {
                    $author = $organisation->author;

                    if ($author->notification('important'))
                    {
                        $author->notify((new MessageNotification('Olive: '.$message['title'], $message['info'], $message['body']))->onQueue('email'));
                    }

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

                    unset($message);
                }
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

        return view('organisation.settings', compact('user'));
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

            while ($new_name === null)
            {
                $generated_name = str_random(6);

                $new_name = @User::where('name', $generated_name)->exists() ? null : $generated_name;
            }

            $user = new User;
            $user->name = $new_name;
            $user->email = $request->email;
            $user->verified = true;
            $user->password = bcrypt($new_password);
            $user->session_id = str_random(100);
            $user->save();

            $user = User::find($user->id);

            $user->notify((new SendPasswordNotification($new_name, $new_password))->onQueue('email'));
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
     * Organizasyon, Sil
     *
     * @return array
     */
    public static function delete(DeleteRequest $request)
    {
        $user = auth()->user();
        $organisation = $user->organisation;

        session()->flash('deleted', true);

        $users = User::where('organisation_id', $user->organisation_id)->get();

        foreach ($users as $u)
        {
            $title = 'Organizasyon Silindi';

            if ($user->id == $u->id)
            {
                $message = 'Organizasyon başarılı bir şekilde silindi.';

                if (count($users) > 1)
                {
                    $message .= ' ';
                    $message .= 'Diğer kullanıcılara gerekli açıklama bildirim ve e-posta yoluyla iletilecektir.';
                }
            }
            else
            {
                $message = $organisation->name.', '.$user->name.' tarafından silindi.';
            }

            if ($u->notification('important'))
            {
                $u->notify(
                    (
                        new MessageNotification(
                            'Olive: '.$title,
                            'Merhaba, '.$u->name,
                            $message
                        )
                    )->onQueue('email')
                );
            }

            Activity::push(
                $title,
                [
                    'icon' => 'delete',
                    'markdown' => $message,
                    'user_id' => $u->id
                ]
            );
        }

        $organisation->delete();

        return [
            'status' => 'ok'
        ];
    }

    /**
     *
     * Organizasyon, Ödeme Bildirim Sayfası
     *
     * @return view
     */
    public static function paymentStatus(string $status, Request $request)
    {
        return $status;
    }

    /**
     *
     * Organizasyon, Ödeme Bildirim Sayfası (Service)
     *
     * @return string
     */
    public static function paymentCallback(PaymentCallbackRequest $request)
    {
        $merchant_key = config('services.paytr.merchant.key');
        $merchant_salt = config('services.paytr.merchant.salt');

        $invoice = Invoice::where('invoice_id', $request->merchant_oid)->firstOrFail();
        $organisation = $invoice->organisation;

        file_put_contents('payment.txt', implode(PHP_EOL, [
            $request->merchant_oid,
            $request->status,
            $request->total_amount,
            $request->hash,
            $request->failed_reason_code,
            $request->failed_reason_msg
        ]), FILE_APPEND | LOCK_EX);

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

            return 'FAIL';
        }

        if ($request->status == 'success')
        {
            // $request->total_amount // taksitli durumlarda farklı değer gelebilir.

            $organisation->status = true;
            $organisation->start_date = $organisation->invoices()->count() == 1 ? date('Y-m-d H:i:s') : $organisation->start_date;

            $add_month = new Carbon($organisation->invoices()->count() == 1 ? $organisation->start_date : $organisation->end_date);
            $add_month = $add_month->addMonths($invoice->month);

            $organisation->end_date = $add_month;
            $organisation->save();

            $title = 'Olive: Fatura Onayı';
            $greeting = 'Faturanız Onaylandı!';
            $message = 'Organizasyonunuz aktif edildi. İyi araştırmalar dileriz...';

            if ($organisation->author->notification('important'))
            {
                $organisation->author->notify(
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
                    'user_id' => $organisation->author->id,
                    'icon' => 'check',
                    'markdown' => $message
                ]
            );

            if (!$organisation->author->badge(999))
            {
                $organisation->author->addBadge(999); // destekçi
            }

            $invoice->paid_at = date('Y-m-d H:i:s');
            $invoice->save();

            session()->flash('success', 'Ödemeniz başarılı bir şekilde gerçekleştirildi.');

            return 'OK';
        }
        else
        {
            $invoice->reason_code = $request->failed_reason_code;
            $invoice->reason_msg = $request->failed_reason_msg;
            $invoice->save();

            return 'FAIL';
        }
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
            $reason = 'Ödenmemiş faturanız bulunmamaktadır.';
        }
        else
        {
            $merchant_id = config('services.paytr.merchant.id');
            $merchant_key = config('services.paytr.merchant.key');
            $merchant_salt = config('services.paytr.merchant.salt');

            $ip = $request->ip();

            $user_ip = '212.2.212.122';

            $merchant_oid = $invoice->invoice_id;

            $user_name = $invoice->info->merchant_name ? $invoice->info->merchant_name : ($invoice->info->person_name.' '.$invoice->info->person_lastname);
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
                            'Organizasyon #'.$organisation->id,
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

            $result = json_decode($result);

            if ($result->status == 'success')
            {
                $token = $result->token;
            }
            else
            {
                $reason = $result->reason;
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

        $discount_rate = $request->month >= config('formal.discount_with_year') ? config('formal.discount_with_year') : 0;

        $billing_information = new BillingInformation;
        $billing_information->user_id = $user->id;
        $billing_information->fill($request->all());
        $billing_information->save();

        $invoice_id = date('ymdhis').$user->id.$user->organisation_id.rand(10, 99);

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

        $invoice = Invoice::where('invoice_id', $id)
                          ->where(function ($query) use ($key) {
                                if (auth()->check())
                                {
                                    $user = auth()->user();

                                    if (!$user->root())
                                    {
                                        $query->orWhere('organisation_id', $user->organisation_id)
                                              ->orWhere('user_id', $user->id);
                                    }
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
     * Organizasyon Listesi
     *
     * @return view
     */
    public static function adminListView()
    {
        return view('organisation.admin.list');
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
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.view', compact('organisation'));
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

        $organisation->name = $request->name;
        $organisation->user_capacity = $request->user_capacity;
        $organisation->status = $request->status ? true : false;
        $organisation->end_date = $request->end_date.' '.$request->end_time;
        $organisation->historical_days = $request->historical_days;
        $organisation->real_time_group_limit = $request->real_time_group_limit;
        $organisation->alarm_limit = $request->alarm_limit;
        $organisation->pin_group_limit = $request->pin_group_limit;

        $organisation->data_pool_youtube_channel_limit = $request->data_pool_youtube_channel_limit;
        $organisation->data_pool_youtube_video_limit = $request->data_pool_youtube_video_limit;
        $organisation->data_pool_youtube_keyword_limit = $request->data_pool_youtube_keyword_limit;
        $organisation->data_pool_twitter_keyword_limit = $request->data_pool_twitter_keyword_limit;
        $organisation->data_pool_twitter_user_limit = $request->data_pool_twitter_user_limit;
        $organisation->unit_price = $request->unit_price;

        $organisation->module_real_time = $request->module_real_time ? true : false;
        $organisation->module_search = $request->module_search ? true : false;
        $organisation->module_trend = $request->module_trend ? true : false;
        $organisation->module_alarm = $request->module_alarm ? true : false;
        $organisation->module_pin = $request->module_pin ? true : false;
        $organisation->module_model = $request->module_model ? true : false;
        $organisation->module_forum = $request->module_forum ? true : false;

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
            $organisation->end_date = date('Y-m-d H:i:s', strtotime('+7 day'));
            $organisation->status = false;
            $organisation->user_capacity = 1;
            $organisation->unit_price = 0;

            $organisation->data_twitter = true;
            $organisation->data_sozluk = true;
            $organisation->data_news = true;
            $organisation->data_youtube_video = true;
            $organisation->data_youtube_comment = true;
            $organisation->data_shopping = true;
            $organisation->data_forum = false;
            $organisation->data_facebook = false;
            $organisation->data_instagram = false;
            $organisation->data_blog = false;

            $organisation->real_time_group_limit = 1;
            $organisation->alarm_limit = 1;
            $organisation->pin_group_limit = 1;

            $organisation->data_pool_youtube_channel_limit = 10;
            $organisation->data_pool_youtube_video_limit = 10;
            $organisation->data_pool_youtube_keyword_limit = 10;
            $organisation->data_pool_twitter_keyword_limit = 10;
            $organisation->data_pool_twitter_user_limit = 10;
            $organisation->data_pool_facebook_keyword_limit = 10;
            $organisation->data_pool_facebook_user_limit = 10;
            $organisation->data_pool_instagram_keyword_limit = 10;
            $organisation->data_pool_instagram_user_limit = 10;

            $organisation->module_real_time = true;
            $organisation->module_search = true;
            $organisation->module_trend = true;
            $organisation->module_alarm = true;
            $organisation->module_pin = true;
            $organisation->module_model = true;
            $organisation->module_forum = true;

            $organisation->historical_days = 1;

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
            $organisation->status = true;
            $organisation->start_date = $organisation->invoices()->count() == 1 ? date('Y-m-d H:i:s') : $organisation->start_date;

            $add_month = new Carbon($organisation->invoices()->count() == 1 ? $organisation->start_date : $organisation->end_date);
            $add_month = $add_month->addMonths($invoice->month);

            $organisation->end_date = $add_month;
            $organisation->save();

            $title = 'Olive: Fatura Onayı';
            $greeting = 'Faturanız Onaylandı!';
            $message = 'Organizasyonunuzu aktifleştirdik. İyi araştırmalar dileriz...';

            if ($organisation->author->notification('important'))
            {
                $organisation->author->notify(
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
                    'user_id' => $organisation->author->id,
                    'icon' => 'check',
                    'markdown' => $message
                ]
            );

            if (!$organisation->author->badge(999))
            {
                $organisation->author->addBadge(999); // destekçi
            }

            $invoice->paid_at = date('Y-m-d H:i:s');
        }

        $invoice->save();

        return redirect()->route('organisation.invoice', $invoice->invoice_id);
    }

    ### ### ###

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, Kelime Grupları
     *
     * @return view
     */
    public static function keywordGroups(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.keywordGroups', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Organizasyon, Kelime Grupları: güncelle.
     *
     * @return array
     */
    public static function keywordGroupsUpdate(KeywordGroupAdminUpdateRequest $request)
    {
        KeywordGroup::where('id', $request->id)->update(
            [
                'keywords' => $request->keywords
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    ### ### ###

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

        $query = Alarm::where('organisation_id', $organisation->id)->orderBy('id', 'DESC')->get();

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
