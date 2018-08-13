@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Destek'
        ]
    ],
    'dock' => true
])

@section('content')
    @push('local.scripts')
        function form()
        {
            var mdl = modal({
                'id': 'form',
                'body': $('<form />', {
                    'method': 'POST',
                    'action': '{{ route('settings.support.submit') }}',
                    'class': 'json',
                    'id': 'form',
                    'data-callback': '__ticket',
                    'html': [
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'subject',
                                    'name': 'subject',
                                    'type': 'text',
                                    'class': 'validate',
                                    'data-length': 70,
                                    'val': '{{ @config('app.ticket.types')[session('form')] }}'
                                }),
                                $('<label />', {
                                    'for': 'subject',
                                    'html': 'Konu'
                                }),
                                $('<span />', {
                                    'class': 'helper-text'
                                })
                            ]
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<textarea />', {
                                    'id': 'message',
                                    'name': 'message',
                                    'class': 'materialize-textarea validate',
                                    'data-length': 500
                                }),
                                $('<label />', {
                                    'for': 'message',
                                    'html': 'Mesaj'
                                }),
                                $('<span />', {
                                    'class': 'helper-text'
                                })
                            ]
                        }),
                        $('<div />', {
                            'class': 'input-field d-table',
                            'html': [
                                $('<select />', {
                                    'name': 'type',
                                    'id': 'type'
                                }),
                                $('<label />', {
                                    'for': 'type',
                                    'html': 'Destek Türü'
                                })
                            ]
                        })
                    ]
                }),
                'size': 'modal-medium',
                'title': 'Destek Talebi',
                'options': {
                    dismissible: false
                }
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
                           'class': 'waves-effect btn',
                           'data-submit': 'form#form',
                           'html': buttons.ok
                       })
                   ])

            M.updateTextFields()

            $('input[name=subject], textarea[name=message]').characterCounter()

            var select = $('select[name=type]');
                select.formSelect()

            $.each({!! json_encode(config('app.ticket.types')) !!}, function(key, name) {
                var option = $('<option />', {
                    'value': key,
                    'html': name
                });

                var type = '{{ session('form') }}';

                if (type == key)
                {
                    option.attr('selected', true)
                }

                $('select[name=type]').append(option)
            })

                select.formSelect()
        }

        function __ticket(__, obj)
        {
            if (obj.status == 'ok')
            {
                M.toast({
                    html: 'Talep oluşturuluyor.',
                    classes: 'blue'
                })

                __[0].reset()

                $('#modal-form').modal('close')

                window.location.href = obj.data.url;
            }
        }

        @if (session('form'))
        form()
        @endif
    @endpush

    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/9.jpg') }}" alt="Destek Taleplerim" />
            <a href="javascript:form()" class="btn-floating btn-large halfway-fab waves-effect waves-light red">
                <i class="material-icons">add</i>
            </a>
        </div>
        <div class="card-content">
            <span class="card-title">Destek Taleplerim</span>
            <p class="grey-text">{{ count($tickets).'/'.$tickets->total() }}</p>
        </div>
        @if (count($tickets))
        <div class="collection">
            @foreach ($tickets as $ticket)
            <a href="{{ route('settings.support.ticket', $ticket->id) }}" class="collection-item d-flex waves-effect {{ $ticket->status == 'open' ? 'black' : 'grey' }}-text">
                <i class="material-icons align-self-center">{{ $ticket->status == 'open' ? 'lock_open' : 'lock' }}</i>
                <span class="align-self-center">
                    <p>
                        {{ $ticket->subject }} / {{ config('app.ticket.types')[$ticket->type] }}
                        @if (count($ticket->replies))
                        <span class="badge green {{ $ticket->status == 'closed' ? 'lighten-2' : '' }} white-text">{{ $ticket->replies()->count() }} cevap</span>
                        @endif
                    </p>
                    <p class="grey-text">{{ date('d.m.Y H:i', strtotime($ticket->created_at)) }}</p>
                </span>
                <small class="badge ml-auto">{{ $ticket->status == 'open' ? 'AÇIK' : 'KAPALI' }}</small>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {!! $tickets->links('vendor.pagination.materializecss') !!}

    @if (!count($tickets))
    <div class="not-found">
        <i class="material-icons">cloud</i>
        <i class="material-icons">cloud</i>
        <i class="material-icons">wb_sunny</i>
        <p>Talep Bulunamadı.</p>
    </div>
    @endif
@endsection

@section('dock')
    @include('layouts.dock.settings', [ 'active' => 'support' ])
@endsection
