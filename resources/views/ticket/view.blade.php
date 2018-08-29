@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Destek',
            'link' => route('settings.support')
        ],
        [
            'text' => '#'.$ticket->id
        ]
    ],
    'dock' => true
])

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
                    .attr('src', '{{ asset('img/md/34.jpg') }}')
                    .next('a')
                    .removeClass('green')
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
            'options': {}
        });

            mdl.find('.modal-footer')
               .html([
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat',
                       'html': buttons.cancel
                   }),
                   $('<span />', {
                       'html': ' '
                   }),
                   $('<a />', {
                       'href': '#',
                       'class': 'waves-effect btn blue darken-4 json',
                       'data-href': '{{ route('settings.support.ticket.close', $ticket->id) }}',
                       'data-method': 'patch',
                       'data-callback': '__close',
                       'html': buttons.ok
                   })
               ])
    }
@endpush

@section('content')
    <div class="card ticket-card">
        <div class="card-image">
            <img src="{{ asset('img/md/'.($ticket->status == 'open' ? '7' : '34').'.jpg') }}" alt="Destek Talebi" />
            <a href="javascript:close()" class="btn-floating btn-large halfway-fab waves-effect waves-light {{ $ticket->status == 'open' ? 'green' : 'disabled' }}">
                <i class="material-icons">{{ $ticket->status == 'open' ? 'lock_open' : 'lock' }}</i>
            </a>
            <span class="card-title">#{{ $ticket->id }}</span>
        </div>
        <div class="card-content grey lighten-5">
            <ul class="item-group">
                <li class="item">
                    <small class="grey-text">İlgili Organizasyon</small>
                    <p>{{ @$ticket->invoice ? $ticket->invoice->organisation->name : '-' }}</p>
                </li>
                <li class="item">
                    <small class="grey-text">İlgili Kullanıcı</small>
                    <p>{{ $ticket->user->name }}</p>
                </li>
                <li class="item">
                    <small class="grey-text">Açıldığı Tarih</small>
                    <p>{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</p>
                </li>
                @isset(config('app.ticket.types')[$ticket->type])
                <li class="item">
                    <small class="grey-text">İlgili Kategori</small>
                    <p>{{ @config('app.ticket.types')[$ticket->type] ? config('app.ticket.types')[$ticket->type] : '-' }}</p> 
                </li>
                @endisset
            </ul>
        </div>
        <div class="card-content">
            <span class="card-title">{{ $ticket->subject }}</span>
            <div class="md-area">{!! Term::markdown($ticket->message) !!}</div>
        </div>
    </div>
    @forelse ($ticket->replies as $row)
    <div class="card {{ $row->user_id == auth()->user()->id ? '' : 'green lighten-4' }}" id="message-{{ $row->id }}">
        <div class="card-content">
            <small class="mb-4">{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</small>
            <div class="md-area">{!! Term::markdown($row->message) !!}</div>
        </div>
    </div>
    @empty
    <div class="not-found">
        <i class="material-icons">cloud</i>
        <i class="material-icons">cloud</i>
        <i class="material-icons">wb_sunny</i>
        <p>Talep Cevap Bekliyor...</p>
    </div>
    @endforelse

    @if ($ticket->status == 'open')
        <div class="card reply-form">
            <div class="card-content">
                <form id="reply-form" method="put" data-ticket_id="{{ $ticket->id }}" action="{{ route('settings.support.ticket.reply', $ticket->id) }}" class="json" data-callback="__reply">
                    <div class="input-field">
                        <textarea name="message" id="message" data-length="500" class="materialize-textarea validate"></textarea>
                        <label for="message">Cevap</label>
                        <span class="helper-text">Yanıt verebilir, veya ekeleme yapabilirsiniz.</span>
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

@section('dock')
    @include('settings._menu', [ 'active' => 'support' ])
@endsection
