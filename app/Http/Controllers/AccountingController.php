<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\QRequest;
use App\Http\Requests\User\Partner\EditRequest;
use App\Http\Requests\User\Partner\ActionRequest;

use App\Notifications\MessageNotification;

use App\Models\User\PartnerPayment;
use App\Models\User\User;
use App\Models\Organisation\OrganisationInvoice;

use App\Utilities\UserActivityUtility as Activity;

class AccountingController extends Controller
{

    /**
     * partmer ödemeleri
     *
     * @return view
     */
    public static function partnerPaymentsHistory(QRequest $request)
    {
    	return view('accounting.partner_payments', compact('request'));
    }

    /**
     * partmer ödemeleri, data
     *
     * @return array
     */
    public static function partnerPaymentsHistoryData(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new PartnerPayment;
        $query = $query->with('user');

        if ($request->string)
        {
            $query = $query->whereHas('user', function ($query) use ($request) {
                $query->where('name', 'ILIKE', '%'.$request->string.'%')
                      ->orWhere('email', 'ILIKE', '%'.$request->string.'%');
            });
        }

        if ($request->direction)
        {
            $direction = $request->direction == 'in' ? '>' : '<';
            $query = $query->where('amount', $direction, 0);
        }

        if ($request->start_date)
        {
            $query = $query->where('created_at', '>=', $request->start_date.' 00:00:00');
        }

        if ($request->end_date)
        {
            $query = $query->where('created_at', '<=', $request->end_date.' 23:59:59');
        }

        if ($request->status)
        {
            $query = $query->where('status', $request->status);
        }

        $total = $query->count();
        $sum = $query->sum('amount');

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC')
                       ->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => $total,
            'sum' => $sum
        ];
    }

    /**
     * partmer ödemeleri, edit
     *
     * @return array
     */
    public static function partnerPaymentsEdit(EditRequest $request)
    {
        $q = PartnerPayment::find($request->id);

        if ($request->status != $q->status)
        {
            $user = $q->user;

            switch ($request->status)
            {
                case 'cancelled':
                    $message = [
                        'title' => 'Ödemeniz İptal Edildi',
                        'body' => [ 'Ödemeniz iptal edildi:', $request->message ]
                    ];
                break;
                case 'pending':
                    $message = [
                        'title' => 'Ödemeniz Onay Bekliyor',
                        'body' => [ 'Ödemeniz en kısa sürede muhasebe ekibimiz tarafından işleme alınacaktır.' ]
                    ];
                break;
                case 'success':
                    $message = [
                        'title' => 'Ödemeniz Onaylandı!',
                        'body' => [ 'Tebrikler, ödemenizi işleme aldık. Ücretin hesabınıza geçmesi, eft süreleri içerisinde biraz zaman alabilir.', 'İlginiz için teşekkür eder, iyi çalışmalar dileriz.' ]
                    ];
                break;
            }

            if ($user->notification('important'))
            {
                $user->notify(
                    (
                        new MessageNotification(
                            $message['title'],
                            'Merhaba, '.$user->name,
                            implode(PHP_EOL.PHP_EOL, $message['body'])
                        )
                    )->onQueue('email')
                );
            }

            Activity::push(
                $message['title'],
                [
                    'icon' => 'credit_card',
                    'markdown' => implode(PHP_EOL, $message['body']),
                    'user_id' => $user->id,
                    'key' => implode('-', [ $user->id, 'partner_payments' ])
                ]
            );
        }

        $q->message = $request->message;
        $q->status = $request->status;
        $q->save();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $q->id,
                'status' => $q->status,
                'process' => $q->process,
                'message' => $q->message
            ]
        ];
    }

    /**
     * partmer ödemeleri, action
     *
     * @return array
     */
    public static function partnerPaymentsAction(ActionRequest $request)
    {
        $user = User::where('name', $request->name)->first();

        $q = new PartnerPayment;
        $q->currency = config('formal.currency_text');
        $q->amount = $request->amount;
        $q->status = $request->status;
        $q->message = $request->message;
        $q->user_id = $user->id;
        $q->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * faturalar
     *
     * @return view
     */
    public static function invoices()
    {
        return view('accounting.invoices');
    }

    /**
     * partmer ödemeleri, data
     *
     * @return array
     */
    public static function invoicesData(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new OrganisationInvoice;
        $query = $query->with('user');

        if ($request->string)
        {
            $query = $query->whereHas('user', function ($query) use ($request) {
                $query->where('name', 'ILIKE', '%'.$request->string.'%')
                      ->orWhere('email', 'ILIKE', '%'.$request->string.'%');
            });
        }

        if ($request->start_date)
        {
            $query = $query->where('created_at', '>=', $request->start_date.' 00:00:00');
        }

        if ($request->end_date)
        {
            $query = $query->where('created_at', '<=', $request->end_date.' 23:59:59');
        }

        switch ($request->status)
        {
            case 'on':
                $query = $query->whereNotNull('paid_at');
            break;
            case 'off':
                $query = $query->whereNull('paid_at');
            break;
        }

        $total = $query->count();
        $sum = $query->sum('total_price');

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('invoice_id', 'DESC')
                       ->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => $total,
            'sum' => $sum
        ];
    }
}
