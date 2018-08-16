@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Kullanıcı Listesi',
            'link' => route('admin.user.list')
        ],
        [
            'text' => $user->name,
            'link' => route('admin.user', $user->id)
        ],
        [
            'text' => 'Destek Talepleri'
        ]
    ],
    'dock' => true
])

@section('content')
<div class="card">
    <div class="card-image">
        <img src="{{ asset('img/md-s/10.jpg') }}" alt="Destek Talepleri" />
        <span class="card-title">Destek Talepleri</span>
    </div>
    @if (count($tickets))
        <div class="collection">
            @foreach($tickets as $ticket)
            <a href="{{ route('admin.ticket', $ticket->id) }}" class="collection-item d-flex waves-effect {{ $ticket->status == 'open' ? 'black' : 'grey' }}-text">
                <i class="material-icons align-self-center">{{ $ticket->status == 'open' ? 'lock_open' : 'lock' }}</i>
                <span class="align-self-center">
                    <p>
                        {{ $ticket->subject }} / {{ config('app.ticket.types')[$ticket->type] }}
                        @if (count($ticket->replies))
                        <span class="badge green {{ $ticket->status == 'closed' ? 'lighten-2' : '' }} white-text">{{ $ticket->replies()->count() }} cevap</span>
                        @endif
                    </p>
                    <p>{{ $ticket->user->name }}</p>
                    <p class="grey-text">{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</p>
                </span>
                <small class="badge ml-auto">{{ $ticket->status == 'open' ? 'AÇIK' : 'KAPALI' }}</small>
            </a>
            @endforeach
        </div>
    @else
        <div class="card-content">
            <div class="not-found">
                <i class="material-icons">cloud</i>
                <i class="material-icons">cloud</i>
                <i class="material-icons">wb_sunny</i>
                <p>Fatura Geçmişi Yok</p>
            </div>
        </div>
    @endif
</div>
	{!! $tickets->links('vendor.pagination.materializecss') !!}
@endsection

@section('dock')
    @include('user.admin._menu', [ 'active' => 'tickets', 'id' => $user->id ])
@endsection

@push('local.scripts')

$('select').formSelect()
$('.tabs').tabs()

@endpush
