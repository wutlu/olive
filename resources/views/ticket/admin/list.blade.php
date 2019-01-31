@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Destek Talepleri'
        ]
    ]
])

@section('content')
    <ul id="status-dropdown" class="dropdown-content">
        <li>
            <a href="{{ $status == 'open' ? '#' : route('admin.tickets', 'open') }}" class="waves-effect">
                <i class="material-icons">lock_open</i> Açık
            </a>
        </li>
        <li>
            <a href="{{ $status == 'closed' ? '#' : route('admin.tickets', 'closed') }}" class="waves-effect">
                <i class="material-icons">lock</i> Kapalı
            </a>
        </li>
    </ul>

    <div class="card with-bg">
        <div class="card-content">
            <div class="d-flex justify-content-between">
                <div>
                    <span class="card-title">Destek Talepleri</span>
                    <p class="grey-text text-darken-2">{{ count($tickets).'/'.$tickets->total() }}</p>
                </div>
                <div class="right-align">
                    <a class="dropdown-trigger btn-flat waves-effect" href="#" data-target="status-dropdown" data-align="right">{{ $status == 'open' ? 'Açık' : 'Kapalı' }}</a>
                </div>
            </div>

            @if (!count($tickets))
                @component('components.nothing')@endcomponent
            @endif
        </div>
        @if (count($tickets))
        <div class="collection">
            @foreach ($tickets as $ticket)
            <a href="{{ route('admin.ticket', $ticket->id) }}" class="collection-item d-flex waves-effect {{ $ticket->status == 'open' ? 'black' : 'grey' }}-text">
                <i class="material-icons align-self-center">{{ $ticket->status == 'open' ? 'lock_open' : 'lock' }}</i>
                <span class="align-self-center">
                    <p>
                        {{ $ticket->subject }} / {{ config('system.ticket.types')[$ticket->type] }}
                        @if (count($ticket->replies))
                        <span class="badge green {{ $ticket->status == 'closed' ? 'lighten-2' : '' }} white-text">{{ $ticket->replies()->count() }} cevap</span>
                        @endif
                    </p>
                    <p>{{ $ticket->user->name }}</p>
                    <p class="grey-text text-darken-2">{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</p>
                </span>
                <small class="badge ml-auto">{{ $ticket->status == 'open' ? 'AÇIK' : 'KAPALI' }}</small>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {!! $tickets->links('vendor.pagination.materializecss') !!}
@endsection
