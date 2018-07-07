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

use Illuminate\Support\Facades\Redis;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;

use Request as RequestStatic;
use PDF;
use Alper\LaravelPdf\InvoicePrinter;


class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('invoice');
    }

    # fatura pdf
    public static function invoice(int $id)
    {

  $invoice = new InvoicePrinter('A4', '₺', 'tr');
  
  /* Header settings */
  $invoice->setLogo("img/veri.zone-logo.png");   //logo image path
  $invoice->setColor("#007fff");      // pdf color scheme
  $invoice->setType("Fatura");    // Invoice Type
  $invoice->setReference("INV-55033645");   // Reference
  $invoice->setDate(date('M dS ,Y', time()));   //Billing Date
  $invoice->setTime(date('h:i:s A', time()));   //Billing Time
  $invoice->setDue(date('M dS ,Y', strtotime('+3 months')));    // Due Date
  $invoice->setFrom([ 'Alper Mutlu Toksöz', 'Atayurt Mah. Yapracık Toki Konutları', 'B2-16 NO: 41', 'ETİMESGUT/ANKARA' ]);
  $invoice->setTo([ 'Alper Mutlu Toksöz', 'Atayurt Mah. Yapracık Toki Konutları', 'B2-16 NO: 41', 'ETİMESGUT/ANKARA' ]);
  
  $invoice->addItem("AMD Athlon X2DC-7450","2.4GHz/1GB/160GB/SMP-DVD/VB",6,0,580,0,3480);
  $invoice->addItem("PDC-E5300","2.6GHz/1GB/320GB/SMP-DVD/FDD/VB",4,0,645,0,2580);
  $invoice->addItem('LG 18.5" WLCD',"",10,0,230,0,2300);
  $invoice->addItem("HP LaserJet 5200","",1,0,1100,0,1100);
  
  $invoice->addTotal("Toplam", 9460);
  $invoice->addTotal("KDV 18%", 1986.6);
  $invoice->addTotal("Genel Toplam", 11446.6, true);
  
  $invoice->addBadge("Ödendi");
  
  $invoice->addTitle("Önemli Bilgi");
  
  $invoice->addParagraph("No item will be replaced or refunded if you don't have the invoice with you.");
  
  $invoice->setFooterNote("veri.zone");
  
  $invoice->render('example1.pdf','I'); 
  /* I => Display on browser, D => Force Download, F => local path save, S => return document path */
  exit();
        $invoice = OrganisationInvoice::where('invoice_id', $id)->where('user_id', auth()->user()->id)->firstOrFail();

        $json = json_decode($invoice->json);

        $pdf = view('plan.invoice', compact('invoice', 'json'));
        return $pdf;
        $pdf = PDF::load($pdf, 'A4')->filename(str_slug(config('app.name').' fatura '.$invoice->invoice_id))->show();

    }

    # başla
    public static function create(int $step = 1, PlanRequest $request)
    {
        if (auth()->user()->organisation_id)
        {
            return view('plan.create.already');
        }
        else
        {
            if (auth()->user()->verified)
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
                    case 1: return view('plan.create.step-1'); break;
                    case 2: return view('plan.create.step-2', compact('plan')); break;
                    case 3:
                        if (@$invoice_session)
                        {
                            $user = auth()->user();

                            /*
                             * Kupon girilmiş fakat son anda farklı
                             * bir hesap tarafından kullanılmış ise
                             * işlemi sonlandır.
                             */

                            if (@$invoice_session->discount)
                            {
                                $coupon = OrganisationDiscountCoupon::where('key', $invoice_session->discount->coupon_key)->first();

                                $status = @$coupon ? true : false;
                            }
                            else
                            {
                                $status = true;
                            }

                            /* --- */

                            if ($status == true)
                            {
                                # organizasyonu oluştur
                                $organisation = Organisation::create([
                                    'name'       => 'ORG#1',
                                    'capacity'   => $invoice_session->plan->capacity,
                                    'start_date' => date('Y-m-d H:i:s'),
                                    'day'        => $invoice_session->month * 30,
                                    'user_id'    => auth()->user()->id
                                ]);

                                # organizasyonu kullanıcıya bağla
                                $user->update([
                                    'organisation_id' => $organisation->id
                                ]);

                                # kullanılmışsa kuponu sil
                                if (@$coupon)
                                {
                                    $coupon->delete();
                                }

                                $invoice_id = 0;

                                while ($invoice_id == 0)
                                {
                                    $invoice_id = rand(10000, 99999);

                                    $invoice_count = OrganisationInvoice::where('invoice_id', $invoice_id)->count();

                                    if ($invoice_count == 0)
                                    {
                                        OrganisationInvoice::create([
                                            'organisation_id' => $organisation->id,
                                                 'invoice_id' => $invoice_id,
                                                    'user_id' => $$user->id,
                                                       'name' => $invoice_session->invoice->name,
                                                   'lastname' => $invoice_session->invoice->lastname,
                                                    'address' => $invoice_session->invoice->address,
                                                       'json' => json_encode($invoice_session->json),
                                                      'notes' => $invoice_session->invoice->notes,

                                                 'unit_price' => $invoice_session->unit_price,
                                                'total_price' => $invoice_session->total_price,
                                                   'discount' => @$invoice_session->discount ? $invoice_session->discount->amount : 0,
                                                        'tax' => $invoice_session->tax
                                        ]);

                                        $ok = true;

                                        // pdf oluştur
                                        // fatura e-postasına ek kaydet
                                        $user->notify(new OrganisationWasCreatedNotification($user->name));
                                    }
                                    else
                                    {
                                        $invoice_id = 0;
                                    }
                                }

                                # bildirim kaydet
                                UserActivityUtility::push(
                                    'Organizasyonunuz oluşturuldu.',
                                    [
                                        'icon' => 'flag',
                                        'markdown' => implode(PHP_EOL, [
                                            'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.',
                                            'Organizasyonunuz ödeme işlemi gerçekleştikten sonra aktif hale gelecektir.'
                                        ]),
                                        'button' => [
                                            'type' => 'http',
                                            'method' => 'GET',
                                            'action' => route('organisation.invoice', [ 'id' => $invoice_id ]),
                                            'class' => 'btn-flat waves-effect',
                                            'text' => 'Faturayı İndir'
                                        ]
                                    ]
                                );
                            }

                            Redis::del($redis_invoice_key);

                            return view('plan.create.step-3', compact('plan', 'invoice_session'));
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
                return view('plan.create.non-verified');
            }
        }
    }

    /*
     * - plan hesaplaması yap
     * - plan loglarını redis'e al.
     */

    public static function calculate(PlanCalculateRequest $request)
    {
        $plan = (object) config('plans')[$request->plan];

        $session['plan']['name'          ] = $plan->name;
        $session['plan']['capacity'      ] = $plan->properties['capacity']['value'];

        $session['unit_price'            ] = $plan->price;
        $session['month'                 ] = $request->month;
        $session['total_price'           ] = $session['unit_price'] * $request->month;

        # kupon varsa
        if ($request->coupon)
        {
            $coupon = OrganisationDiscountCoupon::where('key', $request->coupon)->first();

            $rate = $request->month >= 12 ? ($coupon->rate + config('app.discount_with_year')) : $coupon->rate;
            $rate = $rate >= 100 ? 99.9 : $rate;

            $session['discount']['rate'      ] = $rate;
            $session['discount']['amount'    ] = $session['total_price'] * $rate / 100;
            $session['discount']['coupon_key'] = $coupon->key;
        }

        $session['discounted_price'      ] = (@$session['discount'] ? $session['total_price'] - $session['discount']['amount'] : $session['total_price']);
        $session['tax'                   ] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) * config('app.tax') / 100;
        $session['total_price_with_tax'  ] = (@$session['discount'] ? $session['discounted_price'] : $session['total_price']) + $session['tax'];

        $session['invoice']['ip'         ] = RequestStatic::ip();
        $session['invoice']['name'       ] = $request->name;
        $session['invoice']['lastname'   ] = $request->lastname;
        $session['invoice']['address'    ] = $request->address;
        $session['invoice']['notes'      ] = $request->notes;

        # tüm detayları json formatında kaydet.
        $session['json'                  ] = $session;

        $redis_invoice_key = implode(
            ':',
            [
                str_slug(config('app.name')),
                'invoice',
                'process',
                $session['invoice']['ip']
            ]
        );

        Redis::set($redis_invoice_key, json_encode($session));
        Redis::pexpire($redis_invoice_key, 300000);

        return [
            'status' => 'ok',
            'result' => $session
        ];
    }

}
