<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility;

use App\UserActivity;
use App\Organisation;
use App\OrganisationInvoice;
use App\OrganisationDiscountCoupon;
use App\User;

use App\Http\Requests\PlanRequest;
use App\Http\Requests\PlanCalculateRequest;
use App\Http\Requests\Organisation\BillingRequest;
use App\Http\Requests\Organisation\NameRequest;
use App\Http\Requests\Organisation\TransferAndRemoveRequest;
use App\Http\Requests\Organisation\InviteRequest;
use App\Http\Requests\Organisation\LeaveRequest;
use App\Http\Requests\Organisation\DeleteRequest;
use App\Http\Requests\IdRequest;

use Illuminate\Support\Facades\Redis;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;
use App\Notifications\ReturnedDiscountCouponNotification;
use App\Notifications\MessageNotification;

use Request as RequestStatic;

use App\BillingInformation;

use Carbon\Carbon;

use Validator;

use App\Jobs\CheckUpcomingPayments;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('organisation:have_not')->only([
            'select',
            'details',
            'create'
        ]);
        $this->middleware('organisation:have')->only([
            'settings',
            'updateName',
            'leave',
            'transfer',
            'remove',
            'delete',
            'invite'
        ]);
    }

    # 
    # organizasyon ayarlar view
    # 
    public static function checkUpcomingPayments()
    {
        $organisations = Organisation::where('status', true)->get();

        if (count($organisations))
        {
            foreach ($organisations as $organisation)
            {
                $end_date = Carbon::parse(date('Y-m-d H:i:s', strtotime('+'.$organisation->day.' days', strtotime($organisation->start_date))));
                $days = $end_date->diffInDays(Carbon::now());

                if ($days <= 0)
                {
                    $message = [
                        'title' => 'Organizasyon Askıya Alındı',
                        'info' => 'Organizasyon Süreniz Doldu',
                        'body' => implode(PHP_EOL, [
                            'Tüm araçlardan tekrar faydalanabilmek için organizasyon süresini uzatmanız gerekiyor.'
                        ])
                    ];

                    $organisation->status = false;
                    $organisation->save();
                }
                elseif ($days <= 7)
                {
                    $message = [
                        'title' => 'Süreyi Uzatın',
                        'info' => 'Organizasyon Süresi Dolmak Üzere',
                        'body' => implode(PHP_EOL, [
                            'Kesinti yaşamamak için organizasyon sürenizi uzatmanız gerekiyor.'
                        ])
                    ];
                }
                else
                {
                    echo '[ok]['.$organisation->name.']'.PHP_EOL;
                }

                if (@$message)
                {
                    print_r($message);

                    $organisation->author->notify(new MessageNotification('Olive: '.$message['title'], $message['info'], $message['body']));

                    UserActivityUtility::push(
                        $message['title'],
                        [
                            'icon' => 'access_time',
                            'markdown' => $message['body'],
                            'user_id' => $organisation->user_id,
                            'key' => implode('-', [ $organisation->user_id, 'upcoming_payments' ]),
                            'button' => [
                                'type' => 'http',
                                'method' => 'GET',
                                'action' => route('settings.organisation').'#tab-3',
                                'class' => 'btn-flat waves-effect',
                                'text' => 'Uzatın'
                            ]
                        ]
                    );

                    unset($message);
                }
            }
        }
    }

    # 
    # organizasyon ayarlar view
    # 
    public static function settings()
    {
        $user = auth()->user();

        return view('organisation.settings', compact('user'));
    }

    # 
    # organizasyon adı güncelle
    # 
    public static function updateName(NameRequest $request)
    {
        auth()->user()->organisation->update([ 'name' => $request->organisation_name ]);

        return [
            'status' => 'ok'
        ];
    }

    # 
    # organizasyona davet
    # 
    public static function invite(InviteRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $user->organisation_id = auth()->user()->organisation_id;
        $user->save();

        $title = 'Organizasyona Eklendiniz';
        $message = $user->organisation->name.'; '.auth()->user()->name.' tarafından eklendiniz.';

        $user->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message));

        UserActivityUtility::push(
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

    # 
    # organizasyondan ayrıl
    # 
    public static function leave(LeaveRequest $request)
    {
        $user = auth()->user();

        session()->flash('leaved', true);

        $title = 'Organizasyondan Ayrıldınız';
        $message = $user->organisation->name.'; kendi isteğiniz üzerine ayrıldınız.';

        UserActivityUtility::push(
            $title,
            [
                'icon' => 'exit_to_app',
                'markdown' => $message
            ]
        );

        $user->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message));

        $user->update([
            'organisation_id' => null
        ]);

        return [
            'status' => 'ok'
        ];
    }

    # 
    # organizasyon devret
    # 
    public static function transfer(TransferAndRemoveRequest $request)
    {
        $user = auth()->user();

        $transferred_user = User::where('id', $request->user_id)->first();
        $user->organisation->update([ 'user_id' => $transferred_user->id ]);

        /*
         * devreden için bilgilendirme
         */
        $title = 'Organizasyon Devredildi';
        $message = $user->organisation->name.', '.$transferred_user->name.' üzerine devredildi.';

        $user->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message));

        UserActivityUtility::push(
            $title,
            [
                'icon' => 'accessibility',
                'markdown' => $message
            ]
        );

        /*
         * devralan için bilgilendirme
         */
        $title = 'Organizasyon Devredildi';
        $message = $user->organisation->name.', '.$user->name.' tarafından size devredildi.';

        $transferred_user->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$transferred_user->name, $message));

        UserActivityUtility::push(
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

    # 
    # kullanıcı çıkar
    # 
    public static function remove(TransferAndRemoveRequest $request)
    {
        $user = auth()->user();

        $removed_user = User::where('id', $request->user_id)->first();
        $removed_user->organisation_id = null;
        $removed_user->save();

        $title = 'Organizasyondan Çıkarıldınız';
        $message = $user->organisation->name.'; '.$user->name.' tarafından çıkarıldınız.';

        $removed_user->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$removed_user->name, $message));

        UserActivityUtility::push(
            $title,
            [
                'icon' => 'exit_to_app',
                'markdown' => $message,
                'user_id' => $removed_user->id
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    # 
    # organizasyon sil
    # 
    public static function delete(DeleteRequest $request)
    {
        $user = auth()->user();
        $organisation = $user->organisation;

        session()->flash('deleted', true);

        $users = User::where('organisation_id', $user->organisation_id)->get();

        foreach ($users as $u)
        {
            $u->update([
                'organisation_id' => null
            ]);

            $title = 'Organizasyon Silindi';

            if ($user->id == $u->id)
            {
                $message = 'Organizasyonunuz başarılı bir şekilde silindi. Varsa diğer kullanıcılara gerekli açıklama bildirim ve e-posta yoluyla iletilecektir.';
            }
            else
            {
                $message = $organisation->name.', '.$user->name.' tarafından silindi.';
            }

            $u->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$u->name, $message));

            UserActivityUtility::push(
                $title,
                [
                    'icon' => 'delete',
                    'markdown' => $message,
                    'user_id' => $u->id
                ]
            );
        }

        # 
        # kalan iade
        # 
        if ($organisation->status)
        {
            $all_order_prices = OrganisationInvoice::where('organisation_id', $user->organisation_id)->sum('total_price');
            $amount_of_return = ($all_order_prices/$organisation->day) * ($organisation->day - Carbon::parse($organisation->start_date)->diffInDays(Carbon::now()));
            $tax = ($amount_of_return*config('formal.tax'))/100;
            $total_return = $amount_of_return + $tax;

            $ok = false;

            while ($ok == false)
            {
                $generate_key = str_random(8);

                $key = OrganisationDiscountCoupon::where('key', $generate_key)->count();

                if ($key == 0)
                {
                    OrganisationDiscountCoupon::create([
                        'key' => $generate_key,
                        'rate' => 0,
                        'price' => $total_return
                    ]);

                    $ok = true;

                    $discount[] = '| Kupon Kodu        | İade Miktarı                     |';
                    $discount[] = '| ----------------: |:-------------------------------- |';
                    $discount[] = '| '.$generate_key.' | ₺ '.$total_return.'              |';

                    # --- [] --- #

                    $discount = implode(PHP_EOL, $discount);

                    $user->notify(new ReturnedDiscountCouponNotification($user->name, $discount));
                }
            }
        }

        $organisation->delete();

        return [
            'status' => 'ok'
        ];
    }

    # 
    # paket seçimi
    # 
    public static function select()
    {
        return view('organisation.create.select');
    }

    #
    # plan detayı
    #
    public static function details(int $id)
    {
        if (isset(config('plans')[$id]))
        {
            $user = auth()->user();

            if (!$user->verified)
            {
                return view('organisation.create.non-verified');
            }

            $plan = config('plans')[$id];

            $billing_informations = BillingInformation::where('user_id', $user->id)->where('protected', true)->get();

            return view('organisation.create.details', compact('plan', 'id', 'billing_informations'));
        }
        else
        {
            return abort(404);
        }
    }

    #
    # plan hesapla
    #
    public static function calculate(PlanCalculateRequest $request)
    {
        $plan = (object) config('plans')[$request->plan_id];

        $session['plan'] = $plan;

        $session['unit_price'] = $plan->price;
        $session['month'] = $request->month;
        $session['total_price'] = $session['unit_price'] * $request->month;

        # kupon varsa
        if ($request->coupon_code)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon_code)->first();
            $discount_with_year = config('formal.discount_with_year');

            if ($request->month >= $discount_with_year)
            {
                $rate = $coupon->rate + $discount_with_year;

                $session['discount']['rate_extra'] = intval($discount_with_year);
            }
            else
            {
                $rate = $coupon->rate;
            }

            $rate = $rate >= 100 ? 100 : $rate;

            $session['discount']['rate'] = $rate;
            $session['discount']['price'] = $coupon->price;
            $session['discount']['amount'] = ($session['total_price'] * $rate / 100) + $session['discount']['price'];
            $session['discount']['coupon_key'] = $coupon->key;
        }

        $session['discounted_price'] = (@$session['discount'] ? $session['total_price'] - $session['discount']['amount'] : $session['total_price']);
        $session['discounted_price'] = $session['discounted_price'] < 0 ? 0 : $session['discounted_price'];
        $session['amount_of_tax'] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) * config('formal.tax') / 100;
        $session['total_price_with_tax'] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) + $session['amount_of_tax'];

        $session['customer']['ip'] = RequestStatic::ip();

        $redis_invoice_key = implode(
            ':',
            [
                str_slug(config('app.name')),
                'invoice',
                'process',
                $session['customer']['ip']
            ]
        );

        Redis::set($redis_invoice_key, json_encode($session));
        Redis::pexpire($redis_invoice_key, 300000);

        return [
            'status' => 'ok',
            'result' => $session
        ];
    }

    #
    # organizasyon ve fatura oluştur
    #
    public static function create(BillingRequest $request)
    {
        $redis_invoice_key = implode(
            ':',
            [
                str_slug(config('app.name')),
                'invoice',
                'process',
                RequestStatic::ip()
            ]
        );

        $invoice_session = json_decode(Redis::get($redis_invoice_key));

        if (!@$invoice_session)
        {
            return [
                'status' => 'ok',
                'created' => false
            ];
        }

        $user = auth()->user();
        $plan = config('plans')[$request->plan_id];

        $billing_information = BillingInformation::firstOrNew([
            'user_id' => $user->id,
            'name' => $request->name
        ]);
        $billing_information->user_id = $user->id;
        $billing_information->protected = $request->protected ? true : false;
        $billing_information->fill($request->all());
        $billing_information->save();

        $billing_information_array = $billing_information->toArray();

        unset($billing_information_array['name']);
        unset($billing_information_array['created_at']);
        unset($billing_information_array['updated_at']);
        unset($billing_information_array['country_id']);
        unset($billing_information_array['state_id']);

        $billing_information_array['country'] = $billing_information->country->name;
        $billing_information_array['state'] = $billing_information->state->name;

        if ($request->coupon_code)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon_code)->delete();
        }

        $organisation = Organisation::create([
                  'name' => 'ORG#1',
              'capacity' => $plan['properties']['capacity']['value'],
            'start_date' => date('Y-m-d H:i:s'),
                   'day' => $request->month * 30,
               'user_id' => $user->id
        ]);

        $user->update([
            'organisation_id' => $organisation->id
        ]);

        $invoice_id = 0;

        while ($invoice_id == 0)
        {
            $invoice_id = date('ymdhis').rand(10, 99);

            $invoice_count = OrganisationInvoice::where('invoice_id', $invoice_id)->count();

            if ($invoice_count == 0)
            {
                OrganisationInvoice::create([
                         'invoice_id' => $invoice_id,
                    'organisation_id' => $organisation->id,
                            'user_id' => $user->id,

                         'unit_price' => $invoice_session->unit_price,
                              'month' => $request->month,
                        'total_price' => $invoice_session->total_price,
                      'amount_of_tax' => $invoice_session->amount_of_tax,

                           'discount' => @$invoice_session->discount ? json_encode($invoice_session->discount) : null,
                'billing_information' => json_encode($billing_information_array),

                               'plan' => json_encode($plan)
                ]);

                $ok = true;

                $user->notify(new OrganisationWasCreatedNotification($user->name, $invoice_id));

                UserActivityUtility::push(
                    'Organizasyon oluşturuldu',
                    [
                        'icon' => 'flag',
                        'markdown' => implode(PHP_EOL, [
                            'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.',
                            'Sanal faturanız oluşturuldu. Ödemeniz gerçekleştikten sonra sanal faturanız, resmi fatura olarak güncellenecek ve organizasyon aktif hale gelecektir.'
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
            }
            else
            {
                $invoice_id = 0;
            }
        }

        Redis::del($redis_invoice_key);

        session()->flash('created', true);

        return [
            'status' => 'ok',
            'created' => true
        ];
    }

    #
    # sonuç göster
    #
    public static function result()
    {
        return view('organisation.create.result');
    }

    #
    # kayıtlı fatura json
    #
    public static function billingInformation(IdRequest $request)
    {
        $billing_information = BillingInformation::where('user_id', auth()->user()->id)->where('id', $request->id)->where('protected', true)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $billing_information
        ];
    }

    # 
    # fatura
    # 
    public static function invoice(int $id)
    {
        $invoice = OrganisationInvoice::where('invoice_id', $id)->where('user_id', auth()->user()->id)->firstOrFail();

        $plan                = json_decode($invoice->plan);
        $pay_notice          = json_decode($invoice->pay_notice);
        $formal_paid         = json_decode($invoice->formal_paid);
        $discount            = json_decode($invoice->discount);
        $billing_information = json_decode($invoice->billing_information);

        return view('organisation.invoice', compact(
            'invoice',
            'billing_information',
            'plan',
            'pay_notice',
            'formal_paid',
            'discount'
        ));
    }

}
