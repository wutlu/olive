<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\TicketSubmitRequest;
use App\Http\Requests\TicketReplyRequest;

use App\Models\Ticket;
use App\Jobs\MonitorJob;

use App\Notifications\TicketNotification;
use App\Notifications\MessageNotification;

use App\Utilities\UserActivityUtility;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('root')->only([
            'adminList',
            'adminView'
        ]);
    }

    # 
    # form
    # 
    public static function list(string $type = '')
    {
        if ($type)
        {
            session()->flash('form', $type);

            return redirect()->route('settings.support');
        }

        $tickets = auth()->user()->tickets();

        return view('ticket.list', compact('type', 'tickets'));
    }

    # 
    # form submit
    # 
    public static function submit(TicketSubmitRequest $request)
    {
        $user = auth()->user();

        $ticket = new Ticket;
        $ticket->user_id = $user->id;
        $ticket->status = 'open';
        $ticket->fill($request->all());

        if ($user->organisation)
        {
            if ($user->organisation->lastInvoice)
            {
                $ticket->invoice_id = $user->organisation->lastInvoice->paid_at ? null : $user->organisation->lastInvoice->invoice_id;
            }
        }

        $ticket->save();
        $ticket->id = $ticket->id.rand(100, 999);
        $ticket->save();

        $subject = 'Destek #'.$ticket->id.' [AÇILDI]';
        $markdown = 'Yeni bir destek talebi oluşturdunuz. Talebiniz, ekibimiz tarafından en kısa sürede incelenip yanıtlanacaktır.';

        if ($ticket->user->notification('important'))
        {
            $ticket->user->notify(new TicketNotification($subject, $markdown, $ticket->id));
        }

        UserActivityUtility::push(
            $subject,
            [
                'key'       => implode('-', [ 'user', 'support', $ticket->user->id ]),
                'icon'      => 'message',
                'markdown'  => $markdown,
                'button' => [
                    'type' => 'http',
                    'method' => 'GET',
                    'action' => route('settings.support.ticket', $ticket->id),
                    'class' => 'btn-flat waves-effect',
                    'text' => 'Desteği Gör'
                ]
            ]
        );

        MonitorJob::dispatch('monitor:ticket:count');

        return [
            'status' => 'ok',
            'data' => [
                'url' => route('settings.support.ticket', $ticket->id)
            ]
        ];
    }

    # 
    # ticket reply
    # 
    public static function reply(TicketReplyRequest $request)
    {
        $user = auth()->user();

        $ticket = new Ticket;
        $ticket->user_id = $user->id;
        $ticket->message = $request->message;
        $ticket->ticket_id = $request->ticket_id;
        $ticket->save();
        $ticket->id = $ticket->id.rand(100, 999);
        $ticket->save();

        if ($user->root())
        {
            $subject = 'Destek #'.$request->ticket_id.' [YANITLANDI]';
            $markdown = 'Destek talebiniz, '.$user->name.' tarafından yanıtlandı.';

            if ($ticket->user->notification('important'))
            {
                $ticket->user->notify(new TicketNotification($subject, $markdown, $request->ticket_id));
            }

            UserActivityUtility::push(
                $subject,
                [
                    'key'       => implode('-', [ 'user', 'support', $ticket->user->id ]),
                    'icon'      => 'message',
                    'markdown'  => $markdown,
                    'button' => [
                        'type' => 'http',
                        'method' => 'GET',
                        'action' => route('settings.support.ticket', $request->ticket_id),
                        'class' => 'btn-flat waves-effect',
                        'text' => 'Yanıtı Gör'
                    ]
                ]
            );
        }

        return [
            'status' => 'ok',
            'data' => [
                'id' => $ticket->id
            ]
        ];
    }

    # 
    # ticket
    # 
    public static function view(int $id)
    {
        $ticket = Ticket::where([
            'id' => $id,
            'user_id' => auth()->user()->id
        ])->firstOrFail();

        return view('ticket.view', compact('ticket'));
    }

    # 
    # ticket
    # 
    public static function close(int $id)
    {
        $user = auth()->user();

        $ticket = Ticket::where('id', $id);
        $ticket = $user->root() ? $ticket : $ticket->where('user_id', $user->id);
        $ticket = $ticket->firstOrFail();

        $ticket->status = 'closed';
        $ticket->save();

        if ($user->root())
        {
            $subject = 'Destek #'.$id.' [KAPANDI]';
            $markdown = 'Destek talebiniz, '.$user->name.' tarafından kapatıldı.';

            if ($ticket->user->notification('important'))
            {
                $ticket->user->notify(new TicketNotification($subject, $markdown, $id));
            }

            UserActivityUtility::push(
                $subject,
                [
                    'key'       => implode('-', [ 'user', 'support', $ticket->user->id ]),
                    'icon'      => 'message',
                    'markdown'  => $markdown,
                    'button' => [
                        'type' => 'http',
                        'method' => 'GET',
                        'action' => route('settings.support.ticket', $id),
                        'class' => 'btn-flat waves-effect',
                        'text' => 'Desteği Gör'
                    ]
                ]
            );
        }

        MonitorJob::dispatch('monitor:ticket:count');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list
    # 
    public static function adminList(string $status = 'open', int $pager = 10)
    {
        $tickets = Ticket::where('status', $status)->orderBy('updated_at', 'DESC')->paginate($pager);

        return view('ticket.admin.list', compact('tickets', 'status'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin view
    # 
    public static function adminView(int $id)
    {
        $ticket = Ticket::where('id', $id)->firstOrFail();

        return view('ticket.admin.view', compact('ticket'));
    }
}
