@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Partner Sistemi'
        ]
    ]
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Partner Logları</span>
        </div>
        <ul class="collection load json-clear" 
            id="transactions"
            data-href="{{ route('admin.transactions') }}"
            data-skip="0"
            data-take="10"
            data-more-button="#transactions-more_button"
            data-callback="__transactions"
            data-method="post"
            data-loader="#transactions-loader"
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
                        <a href="#" class="btn-floating white waves-effect mr-1 align-self-center" data-trigger="status">
                            <i class="material-icons grey-text text-darken-2">create</i>
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
                        <time class="timeago grey-text text-darken-2"></time>

                        <small data-status="success" class="hide green-text">BAŞARILI</small>
                        <small data-status="failed" class="hide red-text">BAŞARISIZ</small>
                        <small data-status="wait" class="hide orange-text">BEKLİYOR</small>
                    </span>
                </span>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'transactions-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="btn-small white grey-text more hide json"
       id="transactions-more_button"
       data-json-target="#transactions">Daha Fazla</a>
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
                            item.find('[data-status=' + o.withdraw + ']').removeClass('hide')
                        }

                        item.find('time').attr('data-time', o.created_at).html(o.created_at)

                        item.appendTo(ul)
                })
            }
        }
    }

    function __transaction(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-form').modal('close')

            M.toast({
                html: 'İşlem Güncellendi',
                classes: 'green darken-2'
            })

            var item = $('[data-id=list-item-' + obj.data.id + ']')

                item.find('[data-status]').addClass('hide')
                item.find('[data-status=' + obj.data.withdraw + ']').removeClass('hide')
        }
    }

    $(document).on('click', '[data-trigger=status]', function() {
        var __ = $(this);

        modal({
            'id': 'form',
            'body': $('<form />', {
                'method': 'post',
                'action': '{{ route('admin.transaction') }}',
                'id': 'form',
                'class': 'json',
                'data-callback': '__transaction',
                'data-id': __.data('id'),
                'html': [
                    $('<ul />', {
                        'class': 'collection',
                        'html': [
                            $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    $('<small />', {
                                        'html': 'IBAN Ad',
                                        'class': 'grey-text ' + (__.data('iban-name') ? 'd-table' : 'hide')
                                    }),
                                    $('<span />', {
                                        'html': __.data('iban-name'),
                                        'class': (__.data('iban-name') ? 'd-table' : 'hide')
                                    }),
                                    $('<small />', {
                                        'html': 'IBAN',
                                        'class': 'grey-text ' + (__.data('iban') ? 'd-table' : 'hide')
                                    }),
                                    $('<span />', {
                                        'html': __.data('iban'),
                                        'class': (__.data('iban') ? 'd-table' : 'hide')
                                    }),
                                    $('<small />', {
                                        'html': 'Tutar',
                                        'class': 'grey-text d-table'
                                    }),
                                    $('<span />', {
                                        'html': __.data('currency') + ' ' + __.data('price')
                                    })
                                ]
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': $('<div />', {
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
                                                    'html': 'Eylemsiz',
                                                    'selected': __.data('withdraw') == '' ? true : false
                                                }),
                                                $('<option />', {
                                                    'value': 'wait',
                                                    'html': 'Bekliyor',
                                                    'selected': __.data('withdraw') == 'wait' ? true : false
                                                }),
                                                $('<option />', {
                                                    'value': 'success',
                                                    'html': 'Başarılı',
                                                    'selected': __.data('withdraw') == 'success' ? true : false
                                                }),
                                                $('<option />', {
                                                    'value': 'failed',
                                                    'html': 'Başarısız',
                                                    'selected': __.data('withdraw') == 'failed' ? true : false
                                                })
                                            ]
                                        }),
                                        $('<label />', {
                                            'for': 'withdraw',
                                            'html': 'İşlem Durumu'
                                        })
                                    ]
                                })
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': $('<div />', {
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
                            })
                        ]
                    })
                ]
            }),
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
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#form',
                    'html': buttons.ok
                })
            ]
        })

        M.updateTextFields()
        $('select[name=withdraw]').formSelect()
    })
@endpush
