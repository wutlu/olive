<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Newsletter;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Newsletter\SaveRequest;

use App\Models\User\User;
use App\Notifications\MessageNotification;

use Term;
use Mail;
use App\Mail\NewsletterMail;
use Carbon\Carbon;

class NewsletterController extends Controller
{
	public function __construct()
	{
	}

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni ana sayfa.
    # 
    public static function dashboard()
    {
        return view('newsletter.dashboard');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni json çıktısı.
    # 
    public static function json(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new Newsletter;
        $query = $request->string ? $query->where('subject', 'ILIKE', '%'.$request->string.'%') : $query;
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
    # e-posta bülteni formu.
    # 
    public static function form(int $id = 0)
    {
        $newsletter = null;

        if ($id)
        {
            $newsletter = Newsletter::where('id', $id)->firstOrFail();
        }

        return view('newsletter.form', compact('newsletter'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni kayıt.
    # 
    public static function save(SaveRequest $request)
    {
        if ($request->id)
        {
            $query = Newsletter::where('id', $request->id)->firstOrFail();
            $status = 'updated';

            if ($query->status == 'process')
            {
                return response(
                    [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'xxx' => [ 'Bülten işleniyor. Bu aşamada güncellenemez.' ]
                        ]
                    ],
                    422
                );
            }
        }
        else
        {
            $query = new Newsletter;
            $status = 'created';
        }

        $query->subject = $request->subject;
        $query->body = $request->body;
        $query->email_list = $request->email_list;
        $query->send_date = date('Y-m-d H:i:s', strtotime($request->send_date.' '.$request->send_time));

        if ($request->status)
        {
            $query->status = 'triggered';
            $query->sent_line = 0;
        }
        else
        {
            $query->status = null;
        }

        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $status
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni bülten durumu.
    # 
    public static function status(IdRequest $request)
    {
        $query = Newsletter::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $query->status,
                'sent_line' => $query->sent_line,
                'total_line' => count(explode(PHP_EOL, $query->email_list))
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni sil.
    # 
    public static function delete(IdRequest $request)
    {
        $query = Newsletter::where('id', $request->id)->firstOrFail();

        if ($query->status == 'process')
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'xxx' => [ 'Bülten işleniyor. Bu aşamada silinemez.' ]
                    ]
                ],
                422
            );
        }

        $query->delete();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # e-posta bülteni için tüm kullanıcıların e-posta listesi.
    # 
    public static function users()
    {
        $users = User::select('email')->where('verified', true)->get()->pluck('email');

        return [
            'status' => 'ok',
            'data' => [
                'hits' => $users
            ]
        ];
    }

    /**
     ****************************************************
     * SYSTEM FUNCTION
     ****************************************************
     *
     * bülten göndermek üzere e-posta tetikleyici.
     *
     */
    public static function processTrigger()
    {
        $newsletters = Newsletter::where(function ($query) {
            $query->orWhere('status', 'triggered');
            $query->orWhere(function($query) {
                $date = Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s');

                $query->where('status', 'process');
                $query->where('updated_at', '<=', $date);
            });
        })->where('send_date', '<=', date('Y-m-d H:i:s'))->get();

        if (count($newsletters))
        {
            foreach ($newsletters as $newsletter)
            {
                $newsletter->update([ 'status' => 'process' ]);

                echo Term::line($newsletter->subject);

                $emails = explode(PHP_EOL, $newsletter->email_list);

                foreach ($emails as $key => $email)
                {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        echo $email.PHP_EOL;

                        $newsletter->update([ 'sent_line' => $key+1 ]);

                        Mail::queue(
                            new NewsletterMail(
                                $newsletter->subject,
                                $newsletter->body,
                                $email
                            )
                        );
                    }
                }

                $newsletter->update([ 'status' => 'ok' ]);
            }
        }
    }
}
