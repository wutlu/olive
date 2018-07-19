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
use App\Http\Requests\IdRequest;

use Illuminate\Support\Facades\Redis;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;
use App\Notifications\MessageNotification;

use Request as RequestStatic;

use App\BillingInformation;

use Carbon\Carbon;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('organisation:have_not')->only([ 'select', 'details', 'create' ]);
        $this->middleware('organisation:have')->only([ 'settings' ]);
    }

    # 
    # ayarlar
    # 
    public static function settings()
    {
        $user = auth()->user();

        return view('organisation.settings', compact('user'));
    }

    # 
    # ad güncelle
    # 
    public static function updateName(NameRequest $request)
    {
        auth()->user()->organisation->update([ 'name' => $request->organisation_name ]);

        return [
            'status' => 'ok'
        ];
    }

    # 
    # ayrıl
    # 
    public static function leave(Request $request)
    {
        $validatedData = $request->validate([
            'leave_key' => 'required|in:organizasyondan ayrılmak istiyorum',
        ]);

        $user = auth()->user();

        if ($user->id == $user->organisation->user_id)
        {
            $status = 'owner';
        }
        else
        {
            session()->flash('leaved', true);

            $title = 'Organizasyondan Ayıldınız.';
            $message = '['.$user->organisation->name.'] adlı organizasyondan kendi isteğinizle ayrıldınız.';

            UserActivityUtility::push(
                $title,
                [
                    'icon' => 'exit_to_app',
                    'markdown' => implode(PHP_EOL, [
                        $message
                    ])
                ]
            );

            $user->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message));

            $user->update([
                'organisation_id' => null
            ]);

            $status = 'ok';
        }

        return [
            'status' => $status
        ];
    }

    # 
    # sil
    # 
    public static function delete(Request $request)
    {
        $validatedData = $request->validate([
            'delete_key' => 'required|in:organizasyonu silmek istiyorum',
        ]);

        $user = auth()->user();

        if ($user->id == $user->organisation->user_id)
        {
            session()->flash('deleted', true);

            $users = User::where('organisation_id', $user->organisation_id)->get();

            foreach ($users as $u)
            {
                $u->update([
                    'organisation_id' => null
                ]);

                $title = 'Organizasyon silindi.';

                if ($user->id == $u->id)
                {
                    $message = 'Organizasyonunuz başarılı bir şekilde silindi. Varsa diğer kullanıcılara gerekli açıklama bildirim ve e-posta yoluyla ulaştırıldı.';
                }
                else
                {
                    $message = '['.$user->organisation->name.'] adlı organizasyon ['.$user->name.'] tarafından silindi.';
                }

                $u->notify(new MessageNotification('Olive: '.$title, 'Merhaba, '.$u->name, $message));

                UserActivityUtility::push(
                    $title,
                    [
                        'icon' => 'delete',
                        'markdown' => implode(PHP_EOL, [
                            $message
                        ]),
                        'user_id' => $u->id
                    ]
                );
            }

            $user->organisation->delete();

            $status = 'ok';
        }
        else
        {
            $status = 'owner';
        }

        return [
            'status' => $status
        ];
    }

    # 
    # organizasyon oluştur
    # 
    public static function select() { return view('organisation.create.select'); }
    public static function result() { return view('organisation.create.result'); }

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
    # kayıtlı fatura bilgileri
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
    # create
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
                    'Organizasyon oluşturuldu.',
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
    # plan hesaplaması
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
            $session['discount']['amount'] = $session['total_price'] * $rate / 100;
            $session['discount']['coupon_key'] = $coupon->key;
        }

        $session['discounted_price'] = (@$session['discount'] ? $session['total_price'] - $session['discount']['amount'] : $session['total_price']);
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
