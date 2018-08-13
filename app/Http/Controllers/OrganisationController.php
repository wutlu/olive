<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility;
use App\Utilities\Term;

use App\UserActivity;
use App\Organisation;
use App\OrganisationInvoice;
use App\OrganisationDiscountCoupon;
use App\User;

use App\Http\Requests\PlanRequest;
use App\Http\Requests\PlanCalculateRequest;
use App\Http\Requests\PlanCalculateRenewRequest;
use App\Http\Requests\Organisation\BillingRequest;
use App\Http\Requests\Organisation\BillingUpdateRequest;
use App\Http\Requests\Organisation\NameRequest;
use App\Http\Requests\Organisation\TransferAndRemoveRequest;
use App\Http\Requests\Organisation\InviteRequest;
use App\Http\Requests\Organisation\LeaveRequest;
use App\Http\Requests\Organisation\DeleteRequest;
use App\Http\Requests\IdRequest;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;
use App\Notifications\OrganisationWasUpdatedNotification;
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
            'create',
            'calculate'
        ]);
        $this->middleware('organisation:have')->only([
            'settings',
            'updateName',
            'leave',
            'transfer',
            'remove',
            'delete',
            'invite',
            'calculateRenew',
            'update',
            'invoiceCancel'
        ]);
    }

    # 
    # organizasyon ödeme bildirimleri
    # 
    public static function checkUpcomingPayments()
    {
        $organisations = Organisation::whereBetween('end_date', [ Carbon::now()->subDays(14), Carbon::now()->addDays(1) ])->get();

        if (count($organisations))
        {
            foreach ($organisations as $organisation)
            {
                echo Term::line($organisation->name);

                if ($organisation->days() <= 0)
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

                if ($organisation->status == true)
                {
                    $message = [
                        'title' => 'Organizasyonu Yenileyin',
                        'info' => 'Organizasyon Süresi Dolmak Üzere',
                        'body' => implode(PHP_EOL, [
                            'Kesinti yaşamamak için organizasyon sürenizi uzatmanız gerekiyor.'
                        ])
                    ];
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
        else
        {
            echo Term::line('İşlem yok!');
        }
    }

    # 
    # organizasyon ayarlar view
    # 
    public static function settings()
    {
        $user = auth()->user();

        $plan = $user->organisation->invoices(1)[0]->plan();

        return view('organisation.settings', compact('user', 'plan'));
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

            return view('organisation.create.details', compact('plan', 'id'));
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
        $session['plan'] = (object) config('plans')[$request->plan_id];

        $session['unit_price'] = $session['plan']->price;
        $session['month'] = $request->month;
        $session['total_price'] = $session['unit_price'] * $request->month;

        # kupon varsa
        if ($request->coupon_code)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon_code)->whereNull('organisation_id')->first();
            $discount_with_year = config('formal.discount_with_year');

            if ($request->month >= 12)
            {
                $rate = $coupon->rate + $discount_with_year;

                $session['discount']['rate_year'] = intval($discount_with_year);
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
        $user = auth()->user();

        $plan = config('plans')[$request->plan_id];

        $billing_information = new BillingInformation;
        $billing_information->user_id = $user->id;
        $billing_information->fill($request->all());
        $billing_information->save();

        $organisation = Organisation::create([
                  'name' => 'ORG#1',
              'capacity' => $plan['properties']['capacity']['value'],
            'start_date' => date('Y-m-d H:i:s'),
              'end_date' => Carbon::now()->addMonths($request->month),
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

                                'unit_price' => $plan['price'],
                                     'month' => $request->month,
                               'total_price' => $request->month * $plan['price'],
                                       'tax' => config('formal.tax'),

                    'billing_information_id' => $billing_information->id,

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
                            'Sanal faturanız oluşturuldu. Ödemenizi gerçekleştirdikten sonra sanal faturanız, resmi fatura olarak güncellenecek ve organizasyon aktif hale gelecektir.'
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

        if ($request->coupon_code)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon_code)->first();

            $coupon->organisation_id = $organisation->id;
            $coupon->rate_year = $request->month >= 12 ? config('formal.discount_with_year') : 0;
            $coupon->save();
        }
        else
        {
            if ($request->month >= 12)
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
                            'rate_year' => config('formal.discount_with_year'),
                            'invoice_id' => $invoice_id
                        ]);

                        $ok = true;
                    }
                }
            }
        }

        session()->flash('created', true);

        return [
            'status' => 'ok',
            'created' => true
        ];
    }

    #
    # plan hesapla
    #
    public static function calculateRenew(PlanCalculateRenewRequest $request)
    {
        $user = auth()->user();

        $session['plan'] = json_decode($user->organisation->invoices(1)[0]->plan);

        $session['unit_price'] = $session['plan']->price;
        $session['month'] = $request->month;
        $session['total_price'] = $session['unit_price'] * $request->month;

        $discount_with_year = config('formal.discount_with_year');

        if ($request->month >= 12)
        {
            $rate = $discount_with_year;

            $session['discount']['rate'] = intval($discount_with_year);

            $rate = $rate >= 100 ? 100 : $rate;

            $session['discount']['rate'] = $rate;
            $session['discount']['amount'] = ($session['total_price'] * $rate / 100);
        }

        $session['discounted_price'] = (@$session['discount'] ? $session['total_price'] - $session['discount']['amount'] : $session['total_price']);
        $session['discounted_price'] = $session['discounted_price'] < 0 ? 0 : $session['discounted_price'];
        $session['amount_of_tax'] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) * config('formal.tax') / 100;
        $session['total_price_with_tax'] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) + $session['amount_of_tax'];

        return [
            'status' => 'ok',
            'result' => $session
        ];
    }

    #
    # organizasyon süresi uzat
    #
    public static function update(BillingUpdateRequest $request)
    {
        $user = auth()->user();

        $plan = json_decode($user->organisation->invoices(1)[0]->plan);

        $billing_information = new BillingInformation;
        $billing_information->user_id = $user->id;
        $billing_information->fill($request->all());
        $billing_information->save();

        $organisation = $user->organisation;

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

                                'unit_price' => $plan->price,
                                     'month' => $request->month,
                               'total_price' => $request->month * $plan->price,
                                       'tax' => config('formal.tax'),

                    'billing_information_id' => $billing_information->id,

                                      'plan' => json_encode($plan)
                ]);

                if ($request->month >= 12)
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
                                'rate_year' => config('formal.discount_with_year'),
                                'invoice_id' => $invoice_id
                            ]);

                            $ok = true;
                        }
                    }
                }

                $ok = true;

                $user->notify(new OrganisationWasUpdatedNotification($user->name, $invoice_id));

                UserActivityUtility::push(
                    'Fatura oluşturuldu',
                    [
                        'icon' => 'flag',
                        'markdown' => implode(PHP_EOL, [
                            'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.',
                            'Sanal faturanız oluşturuldu. Ödemenizi gerçekleştirdikten sonra sanal faturanız, resmi fatura olarak güncellenecek ve organizasyon süresi uzatılacaktır.'
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

        return [
            'status' => 'ok',
            'updated' => true
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
    # fatura
    # 
    public static function invoice(int $id)
    {
        $user = auth()->user();
        $invoice = OrganisationInvoice::where('invoice_id', $id)
                                      ->where(function ($query) use ($user) {
                                            $query->orWhere('organisation_id', $user->organisation_id)
                                                  ->orWhere('user_id', $user->id);
                                      })
                                      ->firstOrFail();

        return view('organisation.invoice', compact('invoice'));
    }

    # 
    # fatura iptali
    # 
    public static function invoiceCancel()
    {
        $user = auth()->user();

        $invoice = OrganisationInvoice::where('invoice_id', $user->organisation->invoices()[0]->invoice_id)->whereNull('paid_at')->delete();

        $user->notify(new MessageNotification('Olive: Fatura İptal Edildi', 'Organizasyon Faturasını İptal Ettiniz!', 'Organizasyon ödemesi için oluşturduğunuz fatura, ödeme tamamlanmadan iptal edildi.'));

        return [
            'status' => 'ok'
        ];
    }
}
