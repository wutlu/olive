@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Destek Talepleri',
            'link' => route('admin.tickets')
        ],
        [
            'text' => '🐞 #'.$ticket->id
        ]
    ]
])

@section('content')
    @push('local.scripts')
        function __close(__, obj)
        {
            if (obj.status == 'ok')
            {
                var card = $('.ticket-card');
                    card.css({ 'opacity': 0 })
                        .animate({ 'opacity': 1 }, 1000)
                        .children('.card-image')
                        .children('img')
                        .attr('src', '{{ asset('img/md-s/34.jpg') }}')
                        .next('a')
                        .removeClass('white')
                        .addClass('disabled')
                        .children('i.material-icons')
                        .html('lock')
    
                $('#modal-close').modal('close')
    
                $('.reply-form').fadeOut()
            }
        }
    
        function close()
        {
            var mdl = modal({
                'id': 'close',
                'body': 'Destek talebini kapatmak üzeresiniz?',
                'size': 'modal-small',
                'title': 'Desteği Kapat',
                'options': {},
                'footer': [
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat grey-text',
                       'html': buttons.cancel
                   }),
                   $('<span />', {
                       'html': ' '
                   }),
                   $('<a />', {
                       'href': '#',
                       'class': 'waves-effect btn-flat json',
                       'data-href': '{{ route('settings.support.ticket.close', $ticket->id) }}',
                       'data-method': 'patch',
                       'data-callback': '__close',
                       'html': buttons.ok
                   })
                ]
            });
        }
    @endpush
    <div class="card ticket-card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/'.($ticket->status == 'open' ? '32' : '34').'.jpg') }}" alt="Destek Talebi" />

            <a href="javascript:close()" class="btn-floating btn-large halfway-fab waves-effect {{ $ticket->status == 'open' ? 'white' : 'disabled' }}">
                <i class="material-icons grey-text text-darken-2">{{ $ticket->status == 'open' ? 'lock_open' : 'lock' }}</i>
            </a>

            <span class="card-title">#{{ $ticket->id }}</span>
        </div>
        <div class="card-content grey lighten-5">
            <ul class="item-group">
                <li class="item">
                    <small class="grey-text">İlgili Organizasyon</small>
                    @isset($ticket->invoice)
                        <a href="{{ route('admin.organisation', $ticket->invoice->organisation->id) }}" class="d-block">{{ $ticket->invoice->organisation->name }}</a>
                    @else
                        <p>-</p>
                    @endisset
                </li>
                <li class="item">
                    <small class="grey-text">İlgili Kullanıcı</small>
                    <a href="{{ route('admin.user', $ticket->user_id) }}" class="d-block">{{ $ticket->user->name }}</a>
                </li>
                <li class="item">
                    <small class="grey-text">Açıldığı Tarih</small>
                    <p>{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</p>
                </li>
                <li class="item">
                    <small class="grey-text">İlgili Kategori</small>
                    @isset($ticket->invoice)
                        <a href="{{ route('organisation.invoice', $ticket->invoice_id) }}" class="d-block">{{ @config('system.ticket.types')[$ticket->type] ? config('system.ticket.types')[$ticket->type] : '-' }}</a>
                    @else
                        <p>{{ @config('system.ticket.types')[$ticket->type] ? config('system.ticket.types')[$ticket->type] : '-' }}</p> 
                    @endisset
                </li>
            </ul>
        </div>
        <div class="card-content">
            <span class="card-title">{{ $ticket->subject }}</span>
            <div class="md-area">{!! Term::markdown($ticket->message) !!}</div>
        </div>
    </div>
    @forelse ($ticket->replies as $row)
    <div class="card {{ $row->user_id == auth()->user()->id ? '' : 'cyan lighten-4' }}" id="message-{{ $row->id }}">
        <div class="card-content">
            <small class="mb-4">{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</small>
            <div class="md-area">{!! Term::markdown($row->message) !!}</div>
        </div>
    </div>
    @empty
        @component('components.nothing')
            @slot('cloud_class', 'white-text')
        @endcomponent
    @endforelse

    @if ($ticket->status == 'open')
        <div class="card reply-form">
            <div class="card-content">
                <form id="reply-form" method="put" data-ticket_id="{{ $ticket->id }}" action="{{ route('settings.support.ticket.reply', $ticket->id) }}" class="json" data-callback="__reply">
                    <div class="input-field">
                        <textarea name="message" id="message" data-length="500" class="materialize-textarea validate"></textarea>
                        <label for="message">Cevap</label>
                        <span class="helper-text">Destek için cevap metniniz.</span>
                    </div>
                    <div class="right-align">
                        <button type="submit" class="btn-flat waves-effect">Gönder</button>
                    </div>
                </form>
            </div>
        </div>

        @push('local.scripts')
        function __reply(__, obj)
        {
            if (obj.status == 'ok')
            {
                $('textarea#message').val('')

                location.reload()
            }
        }
        @endpush
    @endif
@endsection
