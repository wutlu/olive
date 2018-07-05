<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility;

use App\UserActivity;
use App\Organisation;
use App\OrganisationDiscountCoupon;

use App\Http\Requests\PlanRequest;
use App\Http\Requests\PlanCalculateRequest;

use Illuminate\Support\Facades\Redis;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;

use Request as RequestStatic;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    # başla
    public static function create(int $step = 1, PlanRequest $request)
    {
        if (auth()->user()->organisation_id)
        {
            return view('plan.start.already');
        }
        else
        {
            if (auth()->user()->verified)
            {
                $redis_invoice_key = implode(':', [ 'invoice', 'process', RequestStatic::ip() ]);

                if ($request->plan)
                {
                    session()->flash('plan', $request->plan);

                    return redirect()->route('organisation.create', [ 'step' => $step ]);
                }
                else
                {
                    if (session('plan'))
                    {
                        $plan = config('plans')[session('plan')];
                    }
                    else
                    {
                        if ($step != 1)
                        {
                            session()->flash('timeout', true);

                            Redis::del($redis_invoice_key);

                            return redirect()->route('organisation.create');
                        }
                    }
                }

                $invoice_session = json_decode(Redis::get($redis_invoice_key));

                switch ($step)
                {
                    case 1: return view('plan.start.step-1'); break;
                    case 2:
                        return view('plan.start.step-2', compact('plan'));
                    break;
                    case 3:
                    case 4:
                        if (@$invoice_session)
                        {
                            if ($step == 3)
                            {
                                return view('plan.start.step-3', compact('plan', 'invoice_session'));
                            }
                            elseif ($step == 4)
                            {
                                /*
                                 * Kupon girilmiş fakat son anda farklı
                                 * bir hesap tarafından kullanılmış ise
                                 * işlemi sonlandır.
                                 */

                                if (@$invoice_session->discount)
                                {
                                    $coupon = OrganisationDiscountCoupon::where('key', $invoice_session->discount->code)->first();

                                    $status = @$coupon ? true : false;
                                }
                                else
                                {
                                    $status = true;
                                }

                                /* --- */

                                if ($status == true)
                                {
                                    // banka sonucu buradan true veya false olarak dönecek
                                    $status = true;

                                    if ($status == true)
                                    {
                                        # organizasyonu oluştur
                                        $organisation = Organisation::create([
                                            'name' => 'Organizasyonum',
                                            'capacity' => $invoice_session->plan->capacity,
                                            'start_date' => date('Y-m-d H:i:s'),
                                            'day' => $invoice_session->month * 30,
                                            'user_id' => auth()->user()->id
                                        ]);

                                        # organizasyonu kullanıcıya bağla
                                        auth()->user()->update([
                                            'organisation_id' => $organisation->id
                                        ]);

                                        # kullanılmışsa kuponu sil
                                        if (@$coupon)
                                        {
                                            $coupon->delete();
                                        }

                                        # fatura markdown
                                        $data[] = '|                                  | Değer                                  | Birim  | Tutar                                       |';
                                        $data[] = '| -------------------------------- | -------------------------------------: | -----: | ------------------------------------------: |';
                                        $data[] = '| '.$invoice_session->plan->name.' | '.$invoice_session->month.' Ay         | ₺      | '.$invoice_session->total_price.'           |';

                                    if (@$invoice_session->discount)
                                    {
                                        $data[] = '| İndirim                          | '.$invoice_session->discount->rate.'%  | ₺      | '.$invoice_session->discount->amount.'      |';
                                    }

                                        $data[] = '| Vergiler                         | '.config('app.tax').'%                 | ₺      | '.$invoice_session->tax.'                   |';
                                        $data[] = '| Genel Toplam                     |                                        | ₺      | '.$invoice_session->total_price_with_tax.'  |';

                                        # --- [] --- #

                                        $data = implode(PHP_EOL, $data);

                                        # bildirim kaydet
                                        UserActivityUtility::push(
                                            'Satın alma gerçekleştirildi.',
                                            [
                                                'icon'      => 'credit_card',
                                                'markdown'  => $data
                                            ]
                                        );

                                        $user = auth()->user();

                                        # faturayı e-postala
                                        $user->notify(new OrganisationWasCreatedNotification($user->name));
                                        # bilgi e-postası gönder
                                        $user->notify(new OrganisationInvoiceNotification($user->name, $data));

                                        // fatura kaydet
                                        // fatura e-postasına ek kaydet
                                    }
                                }

                                Redis::del($redis_invoice_key);

                                return view('plan.start.step-4', compact('plan', 'status'));
                            }
                        }
                        else
                        {
                            session()->flash('timeout', true);

                            return redirect()->route('organisation.create');
                        }
                    break;
                }
            }
            else
            {
                return view('plan.start.non-verified');
            }
        }
    }

    # hesapla
    public static function calculate(PlanCalculateRequest $request)
    {
        $invoice = [];

        $plan = config('plans')[$request->plan];

        $invoice['plan'] = [
            'id' => $request->plan,
            'name' => $plan['name'],
            'capacity' => $plan['properties']['capacity']['value']
        ];

        $unit_price = $plan['price'];
        $total_price = $unit_price * $request->month;

        $discount = 0;

        if ($request->coupon)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon)->first();

            $rate = $request->month >= 12 ? ($coupon->rate + config('app.discount_with_year')) : $coupon->rate;
            $rate = $rate >= 100 ? 99.9 : $rate;

            $discount = ($total_price * $rate) / 100;

            $invoice['discount'] = [
                'amount' => number_format($discount),
                'rate' => $rate,
                'code' => $coupon->key
            ];
        }

        $discounted_price = $total_price - $discount;

        $tax = $discounted_price * config('app.tax') / 100;

        $total_price_with_tax = $discounted_price + $tax;

        $invoice['total_price']             = number_format($total_price);
        $invoice['unit_price']              = number_format($unit_price);
        $invoice['tax']                     = number_format($tax);
        $invoice['month']                   = $request->month;
        $invoice['discounted_price']        = number_format($discounted_price);
        $invoice['total_price_with_tax']    = number_format($total_price_with_tax);
        $invoice['user_agent']              = [ 'ip' => RequestStatic::ip() ];

        $redis_invoice_key = implode(':', [ 'invoice', 'process', RequestStatic::ip() ]);

        Redis::set($redis_invoice_key, json_encode($invoice));
        Redis::pexpire($redis_invoice_key, 300000);

        return [
            'status' => 'ok',
            'result' => $invoice
        ];
    }

}
