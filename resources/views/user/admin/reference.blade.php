@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Referans Sistemi'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-content teal d-flex justify-content-between">
            <span class="white-text" data-tooltip="Tüm Müşteri Bakiyesi" data-position="right">{{ config('formal.currency') }} {{ $user->balance() }}</span>
            <span class="white-text" data-tooltip="Pay Oranı" data-position="left">{{ config('formal.reference_rate') }}%</span>
        </div>
        <ul class="collection load json-clear" 
            id="transactions"
            data-href="{{ route('admin.transactions') }}"
            data-skip="0"
            data-take="10"
            data-more-button="#transactions-more_button"
            data-callback="__transactions"
            data-method="post"
            data-nothing>
            <li class="collection-item nothing hide p-2">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'Henüz bir işlem gerçekleşmedi.')
                @endcomponent
            </li>
            <li class="collection-item model hide">
                <span class="d-flex justify-content-between">
                    <span class="d-flex">
                        <a href="#" class="btn-floating waves-effect mr-1 align-self-center" data-trigger="status">
                            <i class="material-icons">create</i>
                        </a>
                        <span class="align-self-center">
                            <a
                                href="#"
                                class="json"
                                data-href="{{ route('route.generate.id') }}"
                                data-method="post"
                                data-name="admin.user"
                                data-item="user"
                                data-callback="__go"></a>
                            <span class="d-table" data-name="price"></span>
                        </span>
                    </span>
                    <span class="d-flex flex-column align-items-end">
                        <time class="timeago grey-text"></time>
                        <span data-name="withdraw" class="hide"></span>
                    </span>
                </span>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('class', 'card-loader-unstyled')
            @slot('id', 'transactions-loader')
        @endcomponent
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="transactions-more_button"
                type="button"
                data-json-target="ul#transactions">Daha Fazla</button>
    </div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'reference' ])
@endsection

@push('local.scripts')
    function __transactions(__, obj)
    {
        var ul = $('ul#transactions');
        var item_model = ul.children('li.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                        item.find('[data-name=price]').html(o.currency + ' ' + o.price).removeClass('green-text red-text').addClass(o.price >= 0 ? 'green-text' : 'red-text')
                        item.find('[data-item=user]').html(o.user.name).attr('data-id', o.user.id)
                        item.find('[data-trigger=status]')
                            .attr('data-id', o.id)
                            .attr('data-status-message', o.status_message)
                            .attr('data-iban', o.iban)
                            .attr('data-iban-name', o.iban_name)
                            .attr('data-withdraw', o.withdraw)
                            .attr('data-price', o.price)
                            .attr('data-currency', o.currency)

                        if (o.withdraw)
                        {
                            item.find('[data-name=withdraw]')
                                .html(o.withdraw == 'wait' ? 'Onay Bekliyor...' : o.withdraw == 'failed' ? 'Sorunlu!' : 'Gerçekleşti!')
                                .removeClass('red-text green-text orange-text hide')
                                .addClass(o.withdraw == 'wait' ? 'orange-text' : o.withdraw == 'failed' ? 'red-text' : 'green-text')
                        }

                        item.find('time').attr('data-time', o.created_at).html(o.created_at)

                        item.appendTo(ul)
                })
            }

            $('#transactions-loader').hide()
        }
    }

    function __transaction(__, obj)
    {
        if (obj.status == 'ok')
        {

        }
    }

    $(document).on('click', '[data-trigger=status]', function() {
        var __ = $(this);

        modal({
            'id': 'form',
            'body': [
                $('<ul />', {
                    'class': 'mb-2',
                    'html': [
                        $('<li />', {
                            'html': [
                                $('<small />', {
                                    'html': 'IBAN Ad',
                                    'class': 'grey-text d-table'
                                }),
                                $('<span />', {
                                    'html': __.data('iban-name')
                                }),
                                $('<small />', {
                                    'html': 'IBAN',
                                    'class': 'grey-text d-table'
                                }),
                                $('<span />', {
                                    'html': __.data('iban')
                                }),
                                $('<small />', {
                                    'html': 'Tutar',
                                    'class': 'grey-text d-table'
                                }),
                                $('<span />', {
                                    'html': __.data('currency') + ' ' + __.data('price')
                                })
                            ]
                        })
                    ]
                }),
                $('<form />', {
                    'method': 'post',
                    'action': '{{ route('admin.transaction') }}',
                    'id': 'form',
                    'class': 'json',
                    'data-callback': '__transaction',
                    'html': [
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<select />', {
                                    'id': 'withdraw',
                                    'name': 'withdraw',
                                    'type': 'text',
                                    'class': 'validate',
                                    'html': [
                                        $('<option />', {
                                            'value': '',
                                            'html': 'Seçin',
                                            'selected': true
                                        }),
                                        $('<option />', {
                                            'value': 'wait',
                                            'html': 'Bekliyor'
                                        }),
                                        $('<option />', {
                                            'value': 'success',
                                            'html': 'Başarılı'
                                        }),
                                        $('<option />', {
                                            'value': 'failed',
                                            'html': 'Başarısız'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'for': 'withdraw',
                                    'html': 'İşlem Durumu'
                                })
                            ]
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'status_message',
                                    'name': 'status_message',
                                    'type': 'text',
                                    'class': 'validate',
                                    'data-length': 128,
                                    'value': __.data('status-message')
                                }),
                                $('<label />', {
                                    'for': 'status_message',
                                    'html': 'İşlem Mesajı'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'İşlem sonucunu bu alanda belirtin.'
                                })
                            ]
                        })
                    ]
                })
            ],
            'size': 'modal-medium',
            'title': 'İşlem Yap',
            'options': {
                dismissible: false
            },
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat cyan-text',
                    'data-submit': 'form#form',
                    'html': buttons.ok
                })
            ]
        })

        M.updateTextFields()
        $('select[name=withdraw]').formSelect()
    })
@endpush
