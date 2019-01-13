<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\UserActivityUtility as Activity;
use App\Utilities\Term;

use App\Models\Organisation\Organisation;
use App\Models\Organisation\OrganisationInvoice as Invoice;
use App\Models\Discount\DiscountCoupon as Coupon;
use App\Models\User\User;
use App\Models\RealTime\KeywordGroup;
use App\Models\Pin\Group as PinGroup;

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
use App\Http\Requests\Organisation\Admin\UpdateRequest as AdminUpdateRequest;
use App\Http\Requests\Organisation\Admin\InvoiceApproveRequest;

use App\Http\Requests\RealTime\KeywordGroup\AdminUpdateRequest as KeywordGroupAdminUpdateRequest;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

use App\Notifications\OrganisationInvoiceNotification;
use App\Notifications\OrganisationWasCreatedNotification;
use App\Notifications\OrganisationWasUpdatedNotification;
use App\Notifications\ReturnedDiscountCouponNotification;
use App\Notifications\MessageNotification;

use Request as RequestStatic;

use App\Models\BillingInformation;

use Carbon\Carbon;

use App\Jobs\CheckUpcomingPayments;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except([ 'invoice' ]);
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
        $this->middleware('can:organisation-owner')->only([
            'invite',
            'update',
            'delete',
            'updateName',
            'transfer',
            'remove'
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

        $plan = $user->organisation->lastInvoice->plan();

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

        if ($user->notification('important'))
        {
            $user->notify((new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message))->onQueue('email'));
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

    # 
    # organizasyondan ayrıl
    # 
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
            $user->notify((new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message))->onQueue('email'));
        }

        $user->organisation_id = null;
        $user->save();

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

        $organisation = $user->organisation;
        $organisation->user_id = $transferred_user->id;
        $organisation->save();

        /*
         * devreden için bilgilendirme
         */
        $title = 'Organizasyon Devredildi';
        $message = $user->organisation->name.', '.$transferred_user->name.' üzerine devredildi.';

        if ($user->notification('important'))
        {
            $user->notify((new MessageNotification('Olive: '.$title, 'Merhaba, '.$user->name, $message))->onQueue('email'));
        }

        Activity::push(
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

        if ($transferred_user->notification('important'))
        {
            $transferred_user->notify((new MessageNotification('Olive: '.$title, 'Merhaba, '.$transferred_user->name, $message))->onQueue('email'));
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

        if ($removed_user->notification('important'))
        {
            $removed_user->notify((new MessageNotification('Olive: '.$title, 'Merhaba, '.$removed_user->name, $message))->onQueue('email'));
        }

        Activity::push(
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
                $u->notify((new MessageNotification('Olive: '.$title, 'Merhaba, '.$u->name, $message))->onQueue('email'));
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
            $coupon = Coupon::where('key', $request->coupon_code)->whereNull('invoice_id')->first();
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

        $organisation = new Organisation;
        $organisation->name = $user->id.str_random(4, 12);
        $organisation->capacity = $plan['properties']['capacity']['value'];
        $organisation->start_date = date('Y-m-d H:i:s');
        $organisation->end_date = Carbon::now()->addMonths($request->month);
        $organisation->user_id = $user->id;
        $organisation->save();

        $user->organisation_id = $organisation->id;
        $user->save();

        $invoice_id = 0;

        while ($invoice_id == 0)
        {
            $invoice_id = date('ymdhis').rand(10, 99);

            $invoice_count = Invoice::where('invoice_id', $invoice_id)->count();

            if ($invoice_count == 0)
            {
                Invoice::create([
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

                if ($user->notification('important'))
                {
                    $user->notify((new OrganisationWasCreatedNotification($user->name, $invoice_id))->onQueue('email'));
                }

                Activity::push(
                    'Organizasyon Oluşturuldu',
                    [
                        'icon' => 'flag',
                        'markdown' => implode(PHP_EOL, [
                            'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.',
                            'Sanal faturanız hazır. Ödemenizi gerçekleştirdikten sonra sanal faturanız, resmi fatura olarak güncellenecek ve organizasyon aktif hale gelecektir.'
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
            $coupon = Coupon::where('key', $request->coupon_code)->first();
            $coupon->invoice_id = $invoice_id;
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

                    $key = Coupon::where('key', $generate_key)->count();

                    if ($key == 0)
                    {
                        $coupon = new Coupon;
                        $coupon->key = $generate_key;
                        $coupon->rate_year = config('formal.discount_with_year');
                        $coupon->invoice_id = $invoice_id;
                        $coupon->save();

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

        $session['plan'] = json_decode($user->organisation->lastInvoice->plan);

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

        $plan = json_decode($user->organisation->lastInvoice->plan);

        $billing_information = new BillingInformation;
        $billing_information->user_id = $user->id;
        $billing_information->fill($request->all());
        $billing_information->save();

        $organisation = $user->organisation;

        $invoice_id = 0;

        while ($invoice_id == 0)
        {
            $invoice_id = date('ymdhis').rand(10, 99);

            $invoice_count = Invoice::where('invoice_id', $invoice_id)->count();

            if ($invoice_count == 0)
            {
                Invoice::create([
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

                        $key = Coupon::where('key', $generate_key)->count();

                        if ($key == 0)
                        {
                            $coupon = new Coupon;
                            $coupon->key = $generate_key;
                            $coupon->rate_year = config('formal.discount_with_year');
                            $coupon->invoice_id = $invoice_id;
                            $coupon->save();

                            $ok = true;
                        }
                    }
                }

                $ok = true;

                if ($user->notification('important'))
                {
                    $user->notify((new OrganisationWasUpdatedNotification($user->name, $invoice_id))->onQueue('email'));
                }

                Activity::push(
                    'Fatura oluşturuldu',
                    [
                        'icon' => 'flag',
                        'markdown' => implode(PHP_EOL, [
                            'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.',
                            'Sanal faturanız hazır. Ödemenizi gerçekleştirdikten sonra sanal faturanız, resmi fatura olarak güncellenecek ve organizasyon süresi uzatılacaktır.'
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

    # 
    # fatura iptali
    # 
    public static function invoiceCancel()
    {
        $user = auth()->user();

        $invoice = Invoice::where('invoice_id', $user->organisation->lastInvoice->invoice_id)->whereNull('paid_at')->delete();

        if ($user->notification('important'))
        {
            $user->notify(
                (
                    new MessageNotification(
                        'Olive: Fatura İptal Edildi',
                        'Organizasyon Faturasını İptal Ettiniz!',
                        'Organizasyon ödemesi için oluşturduğunuz fatura, ödeme tamamlanmadan iptal edildi.'
                    )
                )->onQueue('email')
            );
        }

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function adminListView()
    {
        return view('organisation.admin.list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
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
        $query = $request->string ? $query->were('name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin view
    # 
    public static function adminView(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.view', compact('organisation'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin update
    # 
    public static function adminUpdate(int $id, AdminUpdateRequest $request)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();
        $organisation->name = $request->name;
        $organisation->capacity = $request->capacity;
        $organisation->status = $request->status ? true : false;
        $organisation->end_date = $request->end_date.' '.$request->end_time;

        $organisation->twitter_follow_limit_user = $request->twitter_follow_limit_user;
        $organisation->twitter_follow_limit_keyword = $request->twitter_follow_limit_keyword;

        $organisation->youtube_follow_limit_channel = $request->youtube_follow_limit_channel;
        $organisation->youtube_follow_limit_keyword = $request->youtube_follow_limit_keyword;
        $organisation->youtube_follow_limit_video = $request->youtube_follow_limit_video;

        $organisation->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin invoice history
    # 
    public static function adminInvoiceHistory(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.invoiceHistory', compact('organisation'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin invoice approve
    # 
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
            $greeting = 'Fatura Onaylandı!';
            $message = 'Organizasyonu aktif bir şekilde kullanabilirsiniz. İyi araştırmalar...';

            if ($organisation->author->notification('important'))
            {
                $organisation->author->notify((new MessageNotification($title, $greeting, $message))->onQueue('email'));
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # kelime grupları
    # 
    public static function keywordGroups(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.keywordGroups', compact('organisation'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # kelime grupları güncelle
    # 
    public static function keywordGroupsUpdate(KeywordGroupAdminUpdateRequest $request)
    {
        KeywordGroup::where('id', $request->id)->update([ 'keywords' => $request->keywords ]);

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # pin grupları
    # 
    public function pinGroups(int $id)
    {
        $organisation = Organisation::where('id', $id)->firstOrFail();

        return view('organisation.admin.pinGroups', compact('organisation'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # pin grupları json çıktısı
    # 
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
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }
}
