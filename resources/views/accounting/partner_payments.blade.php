@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Muhasebe'
        ],
        [
            'text' => '🐞 Partner Ödemeleri'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('accounting._menu', [ 'active' => 'partner_payments' ])

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">date_range</i>
                Tarih Aralığı
            </span>
            <div class="collection collection-unstyled">
                <div class="input-field">
                    <input data-update placeholder="Başlangıç Tarihi" name="start_date" id="start_date" type="date" class="validate" />
                    <span class="helper-text">Bu tarihten itibaren yapılan işlemler.</span>
                </div>
                <div class="input-field">
                    <input data-update placeholder="Bitiş Tarihi" name="end_date" id="end_date" type="date" class="validate" />
                    <span class="helper-text">Bu tarihe kadar yapılmış işlemler.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Durum
            </span>
        </div>
        <div class="collection collection-unstyled">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status" type="radio" checked value="" />
                <span>Tümü</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-success" type="radio" value="success" />
                <span>Başarılı</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-pending" type="radio" value="pending" />
                <span>Bekliyor</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-cancelled" type="radio" value="cancelled" />
                <span>İptal</span>
            </label>
        </div>
    </div>

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Yön
            </span>
        </div>
        <div class="collection collection-unstyled">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="direction" id="direction" type="radio" checked value="" />
                <span>Tümü</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="direction" id="direction-in" type="radio" value="in" />
                <span>Girişler</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="direction" id="direction-out" type="radio" value="out" />
                <span>Çıkışlar</span>
            </label>
        </div>
    </div>
@endsection

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">
                Partner Ödemeleri
                (<span data-name="count">0</span>)
            </span>
        </div>
    </div>
@endsection

@section('action-bar')
    <a
        href="#"
        class="btn-floating btn-large halfway-fab waves-effect white json"
        data-tooltip="İşlem Oluştur"
        data-trigger="action"
        data-method="post"
        data-href="{{ route('admin.user.autocomplete') }}"
        data-callback="__autocomplete">
        <i class="material-icons grey-text text-darken-2">account_balance_wallet</i>
    </a>
@endsection

@push('local.scripts')
    $(document).on('change', '[data-update]', function() {
        var search = $('#collection');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })

    function __collection(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)
                            .attr('data-message', o.message);

                        item.find('[data-name-alias=name]').attr('data-id', o.user.id).html(o.user.name)
                        item.find('[data-name=email]').html(o.user.email)
                        item.find('[data-name=avatar]').attr('src', o.user.avatar ? '{{ asset('/') }}' + o.user.avatar : '{{ asset('img/icons/people.svg') }}')

                        var color = '';
                        var title = '';

                        if (o.status == 'success')
                        {
                            if (o.amount < 0)
                            {
                                color = 'grey';
                                title = 'Çıkış Başarılı';
                            }
                            else if (o.amount > 0)
                            {
                                color = 'green';
                                title = 'Giriş Başarılı';
                            }
                        }
                        else if (o.status == 'pending')
                        {
                            color = 'blue';
                            title = 'Bekliyor...';
                        }
                        else if (o.status == 'cancelled')
                        {
                            color = 'red';
                            title = 'İptal Edildi';
                        }

                        item.find('[data-name=status]')
                            .removeClass('grey-text green-text blue-text red-text')
                            .addClass(color + '-text')
                            .attr('data-status', o.status)
                        item.find('[data-name=amount]')
                            .html(o.amount + ' ' + o.currency)
                            .attr('data-amount', o.amount)
                            .attr('data-currency', o.currency)
                        item.find('[data-name=process]').html(title)
                        item.find('[data-name=created_at]').html(o.created_at)

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
            $('[data-name=total-amount]').html(obj.sum)
        }
    }

    function __edit(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'İşlem Güncellendi', classes: 'green darken-2' })

            $('#modal-edit').modal('close')

            var item = $('li[data-id=' + obj.data.id + ']');
                item.find('[data-name=status]')
                    .removeClass('grey-text green-text blue-text red-text')
                    .addClass(obj.data.process.color + '-text')
                    .data('status', obj.data.status)
                    .find('[data-name=process]').html(obj.data.process.title)
                item.data('message', obj.data.message)
        }
    }

    $(document).on('click', '[data-trigger=edit]', function() {
        var __ = $(this).closest('li');

        var amount = __.find('[data-name=amount]');
        var amount_net = amount.data('amount') - (amount.data('amount') / 100 * {{ config('formal.stoppage') }});

        var mdl = modal({
            'id': 'edit',
            'body': $('<form />', {
                'method': 'post',
                'action': '{{ route('admin.partner.payments.edit') }}',
                'id': 'edit-form',
                'data-id': __.data('id'),
                'class': 'json',
                'data-callback': '__edit',
                'html': [
                    $('<ul />', {
                        'class': 'collection collection-unstyled',
                        'html': [
                            $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    $('<small />', {
                                        'class': 'grey-text',
                                        'html': 'İşlem Miktarı'
                                    }),
                                    $('<span />', {
                                        'class': 'd-block',
                                        'html': amount.data('amount') + ' ' + amount.data('currency')
                                    }),
                                    $('<small />', {
                                        'class': 'grey-text',
                                        'html': 'Şirketten Çıkacak Miktar'
                                    }),
                                    $('<span />', {
                                        'class': 'd-block red-text',
                                        'html': amount_net + ' ' + amount.data('currency')
                                    })
                                ]
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': $('<div />', {
                                    'class': 'input-field',
                                    'html': [
                                        $('<textarea />', {
                                            'id': 'message',
                                            'name': 'message',
                                            'class': 'materialize-textarea validate',
                                            'data-length': 255,
                                            'html': __.data('message')
                                        }),
                                        $('<label />', {
                                            'for': 'message',
                                            'html': 'Açıklama'
                                        }),
                                        $('<span />', {
                                            'class': 'helper-text',
                                            'html': 'Yapacağınız işlem sonucuda göre açıklamaya ekleme yapabilirsiniz.'
                                        })
                                    ]
                                })
                            }),
                            $('<li />', {
                                'class': 'collection-item d-flex',
                                'html': [
                                    $('<label />', {
                                        'class': 'flex-fill align-self-center',
                                        'html': [
                                            $('<input />', {
                                                'type': 'radio',
                                                'name': 'status',
                                                'id': 'status-success',
                                                'value': 'success'
                                            }),
                                            $('<span />', {
                                                'html': 'Başarılı'
                                            })
                                        ]
                                    }),
                                    $('<label />', {
                                        'class': 'flex-fill align-self-center',
                                        'html': [
                                            $('<input />', {
                                                'type': 'radio',
                                                'name': 'status',
                                                'id': 'status-pending',
                                                'value': 'pending'
                                            }),
                                            $('<span />', {
                                                'html': 'Bekliyor'
                                            })
                                        ]
                                    }),
                                    $('<label />', {
                                        'class': 'flex-fill align-self-center',
                                        'html': [
                                            $('<input />', {
                                                'type': 'radio',
                                                'name': 'status',
                                                'id': 'status-cancelled',
                                                'value': 'cancelled'
                                            }),
                                            $('<span />', {
                                                'html': 'İptal'
                                            })
                                        ]
                                    })
                                ]
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
                    'data-submit': 'form#edit-form',
                    'html': buttons.ok
                })
            ]
        })

        M.updateTextFields()
        M.textareaAutoResize($('textarea[name=message]'))
        mdl.find('textarea[name=message]').characterCounter()
        mdl.find('input#status-' + __.find('[data-name=status]').data('status')).prop('checked', true)
    }).on('keydown keyup click change', 'input[name=amount]', function() {
        var __ = $(this);

        $('[data-name=real_amount]').html(__.val() - (__.val() / 100 * {{ config('formal.stoppage') }}))
    })

    function __autocomplete(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = modal({
                'id': 'action',
                'title': 'Hareket Oluştur',
                'body': $('<form />', {
                    'data-callback': '__action',
                    'action': '{{ route('admin.partner.payments.action') }}',
                    'method': 'post',
                    'id': 'action-form',
                    'class': 'json',
                    'html': [
                        $('<ul />', {
                            'class': 'collection collection-unstyled',
                            'html': [
                                $('<li />', {
                                    'class': 'collection-item',
                                    'html': $('<div />', {
                                        'class': 'input-field',
                                        'html': [
                                            $('<input />', {
                                                'id': 'name',
                                                'name': 'name',
                                                'type': 'text',
                                                'class': 'validate autocomplete'
                                            }),
                                            $('<label />', {
                                                'for': 'name',
                                                'html': 'Kullanıcı Adı'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text',
                                                'html': 'Tanımlanacak kullanıcının kullanıcı adını girin.'
                                            })
                                        ]
                                    })
                                }),
                                $('<li />', {
                                    'class': 'collection-item',
                                    'html': $('<div />', {
                                        'class': 'input-field',
                                        'html': [
                                            $('<textarea />', {
                                                'id': 'message',
                                                'name': 'message',
                                                'class': 'materialize-textarea validate',
                                                'data-length': 255
                                            }),
                                            $('<label />', {
                                                'for': 'message',
                                                'html': 'Açıklama'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text',
                                                'html': 'İşlem açıklaması girin.'
                                            })
                                        ]
                                    })
                                }),
                                $('<li />', {
                                    'class': 'collection-item d-flex',
                                    'html': $('<div />', {
                                        'class': 'input-field',
                                        'html': [
                                            $('<span />', {
                                                'class': 'prefix',
                                                'html': '{{ config('formal.currency') }}'
                                            }),
                                            $('<input />', {
                                                'id': 'amount',
                                                'name': 'amount',
                                                'type': 'number',
                                                'class': 'validate',
                                                'value': 0,
                                                'placeholder': 'Miktar'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text',
                                                'html': 'İşlem yapmak istediğiniz miktar.'
                                            })
                                        ]
                                    })
                                }),
                                $('<li />', {
                                    'class': 'collection-item d-flex',
                                    'html': [
                                        $('<label />', {
                                            'class': 'flex-fill align-self-center',
                                            'html': [
                                                $('<input />', {
                                                    'type': 'radio',
                                                    'name': 'status',
                                                    'id': 'action_status-success',
                                                    'value': 'success'
                                                }),
                                                $('<span />', {
                                                    'html': 'Başarılı'
                                                })
                                            ]
                                        }),
                                        $('<label />', {
                                            'class': 'flex-fill align-self-center',
                                            'html': [
                                                $('<input />', {
                                                    'type': 'radio',
                                                    'name': 'status',
                                                    'id': 'action_status-pending',
                                                    'value': 'pending',
                                                    'checked': true
                                                }),
                                                $('<span />', {
                                                    'html': 'Bekliyor'
                                                })
                                            ]
                                        }),
                                        $('<label />', {
                                            'class': 'flex-fill align-self-center',
                                            'html': [
                                                $('<input />', {
                                                    'type': 'radio',
                                                    'name': 'status',
                                                    'id': 'action_status-cancelled',
                                                    'value': 'cancelled'
                                                }),
                                                $('<span />', {
                                                    'html': 'İptal'
                                                })
                                            ]
                                        })
                                    ]
                                })
                            ]
                        })
                    ]
                }),
                'size': 'modal-medium',
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
                       'data-submit': 'form#action-form',
                       'html': buttons.ok
                   })
                ]
            })

            $('input[name=name]').autocomplete({
                data: obj.data,
                limit: 4
            })

            return mdl;
        }
    }

    function __action(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'İşlem Oluşturuldu', classes: 'green darken-2' })

            $('#modal-action').modal('close')

            var collection = $('ul#collection');
                collection.data('skip', 0).addClass('json-clear');

            vzAjax(collection)
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-content">
            <span class="grey-text">Toplam İşlem Tutarı</span>
            <span class="card-title">{{ config('formal.currency') }} <span data-name="total-amount">0</span></span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#collection"
                           placeholder="Ara"
                           value="{{ $request->q }}" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul class="collection collection-unstyled load json-clear" 
             id="collection"
             data-href="{{ route('admin.partner.history') }}"
             data-method="post"
             data-skip="0"
             data-take="10"
             data-include="string,direction,status,start_date,end_date"
             data-more-button="#collection-more_button"
             data-callback="__collection"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="avatar collection-item justify-content-end model hide">
                <img alt="Avatar" data-name="avatar" class="circle" />
                <span class="mr-auto">
                    <a
                        href="#"
                        class="json"
                        data-href="{{ route('route.generate.id') }}"
                        data-method="post"
                        data-name="admin.user"
                        data-name-alias="name"
                        data-callback="__go"></a>
                    <span data-name="email" class="d-block grey-text"></span>
                </span>
                <span class="right-align" data-name="status">
                    <span data-name="amount" class="d-block"></span>
                    <i>
                        <small data-name="process"></small>
                        <small data-name="created_at" class="d-block"></small>
                    </i>
                </span>
                <a href="#" class="btn-floating btn-flat waves-effect ml-1" data-trigger="edit">
                    <i class="material-icons grey-text text-darken-2">edit</i>
                </a>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="more hide json"
       id="collection-more_button"
       data-json-target="#collection">Daha Fazla</a>
@endsection
