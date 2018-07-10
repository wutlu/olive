<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility;

use App\UserActivity;
use App\Organisation;
use App\OrganisationInvoice;
use App\OrganisationDiscountCoupon;

use App\Http\Requests\PlanRequest;
use App\Http\Requests\PlanCalculateRequest;
use App\Http\Requests\Organisation\BillingRequest;

use Illuminate\Support\Facades\Redis;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;

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
        return view('organisation.settings');
    }

    # 
    # plan seçimi
    # 
    public static function select()
    {
        return view('organisation.create.select');
    }

    #
    # result
    #
    public static function result()
    {
        return view('organisation.create.result');
    }

    #
    # plan detayı
    #
    public static function details(int $id)
    {
        if (isset(config('plans')[$id]))
        {
            if (!auth()->user()->verified)
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

        $billing_information = new BillingInformation;
        $billing_information->user_id = $user->id;
        $billing_information->protected = $request->protected ? true : false;
        $billing_information->fill($request->all());
        $billing_information->save();

        if ($request->coupon_key)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon_key)->delete();
        }

        $organisation = Organisation::create([
            'name'       => 'ORG#1',
            'capacity'   => $plan['properties']['capacity']['value'],
            'start_date' => date('Y-m-d H:i:s'),
            'day'        => $request->month * 30,
            'user_id'    => $user->id
        ]);

        $user->update([
            'organisation_id' => $organisation->id
        ]);

        $invoice_id = 0;

        while ($invoice_id == 0)
        {
            $invoice_id = rand(10000, 99999);

            $invoice_count = OrganisationInvoice::where('invoice_id', $invoice_id)->count();

            if ($invoice_count == 0)
            {
                OrganisationInvoice::create([
                         'invoice_id' => $invoice_id,
                    'organisation_id' => $organisation->id,
                            'user_id' => $user->id,
             'billing_information_id' => $billing_information->id,

                         'unit_price' => $invoice_session->unit_price,
                              'month' => $request->month,
                        'total_price' => $invoice_session->total_price,
                      'amount_of_tax' => $invoice_session->amount_of_tax,

                           'discount' => @$invoice_session->discount ? json_encode($invoice_session->discount) : null,

                               'plan' => json_encode($plan)
                ]);

                $ok = true;

                $user->notify(new OrganisationWasCreatedNotification($user->name));

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

            if ($request->month >= config('app.discount_with_year'))
            {
                $rate = $coupon->rate + config('app.discount_with_year');

                $session['discount']['rate_extra'] = intval(config('app.discount_with_year'));
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
        $session['amount_of_tax'] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) * config('app.tax') / 100;
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
        $oi = OrganisationInvoice::where('invoice_id', $id)->where('user_id', auth()->user()->id)->firstOrFail();

        $json = json_decode($oi->log);

        $data = (object) [
            'invoice' => [
                'id' => $oi->invoice_id,
                'formal' => [
                    'serial' => 'A',
                    'no' => '567'
                ]
            ],
            'orderDate' => date('d.m.Y', strtotime($oi->created_at)),
            'paidDate' => date('d.m.Y', strtotime('+30 days', strtotime($oi->created_at))),
            'dueDate' => date('d.m.Y', strtotime('+30 days', strtotime($oi->created_at))),
            'consumer' => [
                'name' => 'OYT Yazılım Teknolojileri A.Ş.',
                'address' => [
                    'Tomtom Mah. Nur-i Ziya Sok. 16/1',
                    '34433 Beyoğlu İstanbul'
                ]
            ],
            'products' => [
                [
                    'description' => [ 'Test', 'Test' ],
                    'quantity' => '1 Ay',
                    'unitPrice' => 2242,
                    'total' => 14214,
                ],
                [
                    'description' => [ 'Test', 'Test' ],
                    'quantity' => '1 Ay',
                    'unitPrice' => 2242,
                    'total' => 14214,
                    'tax' => 10
                ]
            ],
            'subtotal' => 1100,
            'totalTax' => 114,
            'total' => 10000,
            'discount' => 100,
            'notes' => [
                [
                    'title' => 'Bilgi',
                    'note' => '"'.$json->discount->coupon_key.'" kupon kodu ile '.$json->discount->rate.'% oranında ₺ '.$json->discount->amount.' değerinde bir indirim sağlandı.'
                ],
                [
                    'title' => 'Hesap Bilgisi',
                    'note' => 'Ödemenizi; fatura numarası açıklamada olacak şekilde aşağıdaki hesap numaralarından herhangi birine yapabilirsiniz.'
                ],
                [
                    'note' => 'Panelin sağ üst köşesinde bulunan kullanıcı menüsünden ödeme bildirimi sayfasına erişerek ödeme bildirimi yapmayı unutmayın.'
                ],
                [
                    'note' => 'TR 1345 1561 2451 5112 51 - veri.zone LTD.'
                ]
            ]
        ];

        return view('plan.invoice', compact('data'));
    }

}
