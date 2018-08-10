<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TicketSubmitRequest;
use App\Http\Requests\TicketReplyRequest;
use App\Ticket;

use App\Notifications\TicketNotification;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
        $ticket->invoice_id = $user->organisation->invoices(1)[0]->paid_at ? null : $user->organisation->invoices(1)[0]->invoice_id;
        $ticket->save();
        $ticket->id = $ticket->id.rand(100, 999);
        $ticket->save();

        $user->notify(new TicketNotification($request->subject, $request->message, $ticket->id));

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
        $ticket = Ticket::where('id', $id)->where('user_id', auth()->user()->id)->firstOrFail();

        return view('ticket.view', compact('ticket'));
    }

    # 
    # ticket
    # 
    public static function close(int $id)
    {
        $ticket = Ticket::where('id', $id)->where('user_id', auth()->user()->id)->firstOrFail();
        $ticket->status = 'closed';
        $ticket->save();

        return [
            'status' => 'ok'
        ];
    }
}
