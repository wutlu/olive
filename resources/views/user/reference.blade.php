@extends('layouts.app', $root ? [
        'sidenav_fixed_layout' => true,
        'breadcrumb' =>  [
            [
                'text' => 'Admin'
            ],
            [
                'text' => 'Kullanıcılar',
                'link' => route('admin.user.list')
            ],
            [
                'text' => $user->name,
                'link' => route('admin.user', $user->id)
            ],
            [
                'text' => '🐞 Partner Sistemi'
            ]
        ],
        'dock' => true
    ]
    :
    [
        'sidenav_fixed_layout' => true,
        'breadcrumb' =>  [
            [
                'text' => 'Partner Sistemi'
            ]
        ]
    ]
)

@section('content')
    <div class="card">
        @if ($user->reference_code)
            <div class="card-content cyan darken-2 d-flex justify-content-between">
                @if ($root)
                    <span class="white-text" data-tooltip="Bakiye" data-position="right">{{ config('formal.currency') }} {{ $user->balance() }}</span>
                @else
                    <a href="#" class="white-text" data-trigger="withdraw" data-tooltip="Bakiye" data-position="right">{{ config('formal.currency') }} {{ $user->balance() }}</a>
                @endif

                <span class="white-text" data-tooltip="Referans Kodu" data-position="right">{{ $user->reference_code }}</span>
                <span class="white-text" data-tooltip="Pay Oranı" data-position="left">{{ $user->partner_rate }}%</span>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width tabs-transparent cyan darken-2">
                    <li class="tab">
                        <a href="#referanslar" class="active waves-effect waves-light">Referanslar</a>
                    </li>
                    <li class="tab">
                        <a href="#islem-gecmisi" class="waves-effect waves-light">İşlem Geçmişi</a>
                    </li>
                </ul>
            </div>
            <div id="referanslar">
                <ul class="collection load json-clear" 
                    id="references"
                    data-href="{{ $root ? route('admin.settings.references', $user->id) : route('settings.references') }}"
                    data-skip="0"
                    data-take="10"
                    data-more-button="#references-more_button"
                    data-callback="__references"
                    data-method="post"
                    data-nothing>
                    <li class="collection-item nothing hide p-2">
                        @component('components.nothing')
                            @slot('size', 'small')
                            @slot('text', $root ? 'Kullanıcının referansıyla kaydolan kimse olmadı.' : 'Henüz sizin referansınızla kaydolan kimse olmadı.')
                        @endcomponent
                    </li>
                    <li class="collection-item model hide">
                        <div class="d-flex justify-content-between">
                            <p data-name="name"></p>
                            <time class="timeago grey-text"></time>
                        </div>
                    </li>
                </ul>

                @component('components.loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                    @slot('id', 'references-loader')
                @endcomponent

                <div class="center-align">
                    <button class="btn-flat waves-effect hide json"
                            id="references-more_button"
                            type="button"
                            data-json-target="ul#references">Daha Fazla</button>
                </div>
            </div>
            <div id="islem-gecmisi" style="display: none;">
                <ul class="collection load json-clear" 
                    id="transactions"
                    data-href="{{ $root ? route('admin.settings.transactions', $user->id) : route('settings.transactions') }}"
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
                            <span>
                                <span class="d-table" data-name="price"></span>
                                <span class="grey-text hide" data-name="status-message"></span>
                                <small class="grey-text hide" data-name="iban"></small>
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

                <div class="center-align">
                    <button class="btn-flat waves-effect hide json"
                            id="transactions-more_button"
                            type="button"
                            data-json-target="ul#transactions">Daha Fazla</button>
                </div>
            </div>

            @push('local.scripts')
                function __references(__, obj)
                {
                    var ul = $('ul#references');
                    var item_model = ul.children('li.model');

                    if (obj.status == 'ok')
                    {
                        item_model.addClass('hide')

                        if (obj.hits.length)
                        {
                            $.each(obj.hits, function(key, o) {
                                var item = item_model.clone();
                                    item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                                    item.find('[data-name=name]').html(o.name)
                                    item.find('time').attr('data-time', o.created_at).html(o.created_at)

                                    item.appendTo(ul)
                            })
                        }
                    }

                    $('#references-loader').hide()
                }

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

                                    if (o.status_message)
                                    {
                                        item.find('[data-name=status-message]').html(o.status_message).removeClass('hide').addClass('d-table')
                                    }

                                    if (o.iban)
                                    {
                                        item.find('[data-name=iban]').html(o.iban).removeClass('hide').addClass('d-table')
                                    }

                                    if (o.withdraw)
                                    {
                                        item.find('[data-name=withdraw]')
                                            .html(o.withdraw == 'wait' ? 'Onay Bekliyor...' : o.withdraw == 'failed' ? 'Başarısız!' : 'Gerçekleşti!')
                                            .removeClass('red-text green-text orange-text hide')
                                            .addClass(o.withdraw == 'wait' ? 'orange-text' : o.withdraw == 'failed' ? 'red-text' : 'green-text')
                                    }

                                    item.find('time').attr('data-time', o.created_at).html(o.created_at)

                                    item.appendTo(ul)
                            })
                        }
                    }

                    $('#transactions-loader').hide()
                }
            @endpush
        @else
            <div class="card-content">
                @component('components.nothing')
                    @slot('text_class', 'grey-text')
                    @slot('sun', 'attach_money')
                    @slot('cloud', 'beach_access')
                    @slot('cloud_class', 'grey-text text-darken-2')
                    @slot('size', 'small')
                    @slot('text', 'Şu an partner sistemine dahil değilsiniz.')
                @endcomponent

                <br />

                <p class="mb-0 teal-text">- Partnerlerimiz bizim satış temsilcilerimiz sayılır.</p>
                <p class="mb-0 teal-text">- Partner olmak için gerekli kriterlerinizi inceliyoruz.</p>
                <p class="mb-0 teal-text">- Lütfen başvuru esnasında karşılaşacağınız yönlendirmeleri takip edin.</p>

                <br />

                <a
                    href="#"
                    class="btn-flat btn-large waves-effect mx-auto d-table json"
                    data-href="{{ route('settings.reference.start') }}"
                    data-method="post"
                    data-callback="__start">BAŞLAYIN</a>
            </div>

            @push('local.scripts')
                function __start(__, obj)
                {
                    if (obj.status == 'ok')
                    {
                        location.reload()
                    }
                    else
                    {
                        return modal({
                            'id': 'err',
                            'body': [
                                $('<div />', {
                                    'class': 'teal-text',
                                    'html': 'Partner sistemini kullanabilmek için ilk olarak partner başvurusunda bulunmalısınız. Başvuru için aşağıda adı geçen evrakları "partner@veri.zone" e-posta adresine gönderin ve gelecek cevabı takip edin.',
                                }),
                                $('<ul />', {
                                    'html': [
                                        $('<li />', {
                                            'html': '- Kimlik Fotokopisi'
                                        }),
                                        $('<li />', {
                                            'html': '- Ticari Faaliyet Belgesi'
                                        }),
                                        $('<li />', {
                                            'html': '- Özgeçmiş'
                                        })
                                    ]
                                })
                            ],
                            'size': 'modal-small',
                            'options': {},
                            'footer': [
                               $('<a />', {
                                   'href': '#',
                                   'class': 'modal-close waves-effect btn-flat cyan-text',
                                   'html': buttons.ok
                               })
                            ]
                        })
                    }
                }
            @endpush
        @endif
    </div>
@endsection

@if ($root)
    @section('dock')
        @include('user.admin._menu', [ 'id' => $user->id, 'active' => 'reference' ])
    @endsection
@endif

@push('local.scripts')
    $('.tabs').tabs()

    $(document).on('click', '[data-trigger=withdraw]', function() {
        modal({
            'id': 'form',
            'body': $('<form />', {
                'method': 'post',
                'action': '{{ route('settings.transaction') }}',
                'id': 'form',
                'class': 'json',
                'data-callback': '__withdraw',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<span />', {
                                'class': 'prefix',
                                'html': '{{ config('formal.currency') }}'
                            }),
                            $('<input />', {
                                'id': 'price',
                                'name': 'price',
                                'type': 'number',
                                'class': 'validate',
                                'min': 100,
                                'value': 100
                            }),
                            $('<label />', {
                                'for': 'price',
                                'html': 'Miktar'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Çekmek istediğiniz tutarı girin.'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'iban_name',
                                'name': 'iban_name',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 128
                            }),
                            $('<label />', {
                                'for': 'iban_name',
                                'html': 'Hesap Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Miktarın aktarılacağı hesap sahibinin adı.'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'iban',
                                'name': 'iban',
                                'type': 'text',
                                'class': 'validate'
                            }),
                            $('<label />', {
                                'for': 'iban',
                                'html': 'Alıcı Hesap IBAN Numarası'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Miktarın aktarılacağı hesabın IBAN numarası.'
                            })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'title': 'Ödeme Al',
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

        $('input#iban').mask('TR 9999 9999 9999 9999 9999 9999', {
            'placeholder': ' '
        })
    })

    function __withdraw(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-form').modal('close')

            setTimeout(function() {
                location.reload()
            }, 1000)

            return modal({
                'id': 'process',
                'body': 'İşlem Alınıyor...',
                'size': 'modal-small',
                'options': {
                    dismissible: false
                }
            })
        }
    }

    @if (session('transaction') == 'success')
        modal({
            'id': 'status',
            'body': 'İşlem Başarılı!',
            'size': 'modal-small',
            'class': 'center-align',
            'options': {
                dismissible: false
            },
            'timeout': 2000
        })
    @endif
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
@endpush
