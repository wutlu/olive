@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Organizasyon Ayarları'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    @if (session('organisation') == 'have')
        M.toast({
            html: 'Zaten bir organizasyonunuz mevcut!',
            classes: 'blue'
        })
    @endif

    @if (session('transferred'))
        M.toast({
            html: 'Devir gerçekleşti',
            classes: 'green darken-2'
        })
    @endif

    @if ($user->id == $user->organisation->user_id)
        $(document).on('click', '[data-trigger=name-change]', function() {
            var mdl = modal({
                'id': 'detail',
                'body': $('<form />', {
                    'method': 'patch',
                    'action': '{{ route('organisation.update.name') }}',
                    'id': 'form',
                    'class': 'json',
                    'data-callback': '__update__organisation_name',
                    'html': $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'organisation_name',
                                'name': 'organisation_name',
                                'type': 'text',
                                'class': 'validate'
                            }),
                            $('<label />', {
                                'for': 'organisation_name',
                                'html': 'Organizasyon Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    })
                }),
                'size': 'modal-medium',
                'title': 'Ad Değiştir',
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
                        'html': buttons.update
                    })
                ]
            })

            var name = $('input#organisation_name');
                name.val($('#organisation-card').find('span.card-title').children('span').html())

            $('input[name=organisation_name]').focus()
        })

        function __update__organisation_name(__, obj)
        {
            if (obj.status == 'ok')
            {
                var name = $('input#organisation_name');

                $('#organisation-card').find('span.card-title').children('span').html(name.val())

                $('#modal-detail').modal('close')

                M.toast({
                    html: 'Organizasyon Adı Güncellendi',
                    classes: 'green darken-2'
                })
            }
        }
    @endif
@endpush

@section('content')
<div class="card" id="organisation-card">
    <div class="card-content">
        <span class="card-title">
            <span>{{ $user->organisation->name }}</span>

            @if ($user->id == $user->organisation->user_id)
                <a data-trigger="name-change" class="material-icons" href="#">create</a>
            @endif
        </span>

        @if ($user->id == $user->organisation->user_id)
            @if ($user->organisation->status)
                <p class="grey-text">{{ $user->organisation->days() }} gün kaldı.</p>
            @else
                <p class="red-text">Pasif</p>
            @endif
        @endif
    </div>

    <div class="card-tabs">
        <ul class="tabs tabs-fixed-width">
            <li class="tab">
                <a href="#tab-1" class="waves-effect">
                    <i class="material-icons">people</i>
                </a>
            </li>

            @if ($user->id == $user->organisation->user_id)
                <li class="tab">
                    <a href="#tab-2" class="waves-effect">
                        <i class="material-icons">tune</i>
                    </a>
                </li>
            @endif

            <li class="tab">
                <a href="#tab-3" class="waves-effect">
                    <i class="material-icons">settings</i>
                </a>
            </li>

            @if ($user->id == $user->organisation->user_id)
                <li class="tab">
                    <a href="#tab-4" class="waves-effect">
                        <i class="material-icons">update</i>
                    </a>
                </li>
            @endif
        </ul>
    </div>
    <div id="tab-1" class="card-content grey lighten-4">
        <div class="card-content">
            <p class="grey-text">
                <span class="organisation-capacity">{{ count($user->organisation->users) }}</span> / <span class="organisation-max-capacity">{{ $user->organisation->user_capacity }}</span> kullanıcı
            </p>
        </div>

        <ul class="collection" data-name="member-list">
            @foreach ($user->organisation->users as $u)
                <li class="collection-item avatar" id="organisation-user-{{ $u->id }}">
                    <img src="{{ $u->avatar() }}" alt="avatar" class="circle">
                    <span class="title">{{ $u->name }}</span>
                    <p class="grey-text">{{ $u->email }}</p>
                    <p class="grey-text">{{ ($u->id == $user->organisation->user_id) ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>

                    @if ($user->id != $u->id && $user->id == $user->organisation->user_id)
                        <a href="#" class="secondary-content dropdown-trigger" data-align="right" data-target="dropdown-user-{{ $u->id }}">
                            <i class="material-icons">more_vert</i>
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>

        @foreach ($user->organisation->users as $u)
            @if ($user->id != $u->id && $user->id == $user->organisation->user_id)
                <ul id="dropdown-user-{{ $u->id }}" class="dropdown-content">
                    <li>
                        <a href="#" data-user-id="{{ $u->id }}" data-button="__transfer">
                            <i class="material-icons">fingerprint</i> Devret
                        </a>
                    </li>
                    <li>
                        <a href="#" data-user-id="{{ $u->id }}" data-button="__remove_user">
                            <i class="material-icons">delete_forever</i> Çıkar
                        </a>
                    </li>
                </ul>
            @endif
        @endforeach

        @if ($user->id == $user->organisation->user_id)
            @push('local.scripts')
                $(document).on('click', '[data-button=__transfer]', function() {
                    var __ = $(this);

                    var org_name = $('#organisation-card').find('span.card-title').children('span').html();
                    var user_name = __.closest('li.collection-item').find('span.title').html();

                    return modal({
                        'id': 'transfer',
                        'body': 'Sahip olduğunuz [' + org_name + '] adlı organizasyonu, ' + user_name + ' adlı kullanıcıya devretmek üzeresiniz!',
                        'size': 'modal-small',
                        'title': 'Devret',
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
                               'data-href': '{{ route('settings.organisation.transfer') }}',
                               'data-user_id': __.data('user-id'),
                               'data-method': 'post',
                               'data-callback': '__transfer',
                               'html': buttons.ok
                           })
                        ]
                    })
                }).on('click', '[data-button=__remove_user]', function() {
                    var __ = $(this);

                    var user_name = __.closest('li.collection-item').find('span.title').html();

                    return modal({
                        'id': 'remove',
                        'body': user_name + ' adlı kullanıcıyı organizasyondan çıkarmak üzeresiniz!',
                        'size': 'modal-small',
                        'title': 'Çıkar',
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
                               'class': 'waves-effect btn-flat red-text json',
                               'data-href': '{{ route('settings.organisation.remove') }}',
                               'data-user_id': __.data('user-id'),
                               'data-method': 'delete',
                               'data-callback': '__remove_user',
                               'html': buttons.ok
                           })
                        ]
                    })
                })

                function __transfer(__, obj)
                {
                    if (obj.status == 'ok')
                    {
                        M.toast({
                            html: 'Organizasyon devri gerçekleştiriliyor...',
                            classes: 'blue darken-2'
                        })

                        setTimeout(function() {
                            location.reload()
                        }, 400)
                    }
                }

                function __remove_user(__, obj)
                {
                    if (obj.status == 'ok')
                    {
                        var capacity = $('span.organisation-capacity');
                        var new_capacity = capacity.html() - 1;

                            capacity.html(new_capacity);

                        var max_capacity = $('span.organisation-max-capacity');

                        if (new_capacity >= max_capacity.html())
                        {
                            $('form#invite-form').addClass('hide')
                        }
                        else
                        {
                            $('form#invite-form').removeClass('hide')
                        }

                        $('#organisation-user-' + __.data('user_id')).remove()
                        $('#modal-remove').modal('close')

                        M.toast({
                            html: 'Kullanıcı Çıkarıldı',
                            classes: 'green darken-2'
                        })
                    }
                }
            @endpush

            <form
                id="invite-form"
                method="post"
                action="{{ route('settings.organisation.invite') }}"
                data-callback="__invite"
                class="json {{ count($user->organisation->users) >= $user->organisation->user_capacity ? 'hide' : '' }}">
                <div class="input-field teal-text">
                    <input name="email" id="email" type="email" class="validate" />
                    <label for="email">E-posta</label>
                    <small class="helper-text">Gireceğiniz e-posta adresi ile kayıtlı bir kullanıcı varsa organizasyona eklenecek yoksa yeni bir kullanıcı oluşturulup organizasyona eklenecek ve kullanıcıya e-posta ile şifresi gönderilecek.</small>
                </div>
                <button type="submit" class="btn-flat waves-effect">Ekle</button>
            </form>
            @push('local.scripts')
                function __invite(__, obj)
                {
                    if (obj.status == 'ok')
                    {
                        M.toast({
                            html: 'Kullanıcı Eklendi',
                            classes: 'green darken-2'
                        })

                        $('<li />', {
                            'class': 'collection-item avatar',
                            'id': 'organisation-user-' + obj.data.id,
                            'html': [
                                $('<img />', { 'src': obj.data.avatar, 'alt': 'avatar', 'class': 'circle' }),
                                $('<span />', { 'class': 'title', 'html': obj.data.name  }),
                                $('<p />', { 'class': 'grey-text', 'html': obj.data.email }),
                                $('<p />', { 'class': 'grey-text', 'html': 'Kullanıcı' }),
                                $('<a />', {
                                    'href': '#',
                                    'class': 'secondary-content dropdown-trigger',
                                    'data-target': 'dropdown-user-' + obj.data.id,
                                    'data-align': 'right',
                                    'html': $('<i />', { 'class': 'material-icons', 'html': 'more_vert' })
                                }),
                                $('<ul />', {
                                    'id': 'dropdown-user-' + obj.data.id,
                                    'class': 'dropdown-content',
                                    'html': [
                                        $('<li />', {
                                            'html': $('<a />', {
                                                'href': '#',
                                                'data-user-id': obj.data.id,
                                                'data-button': '__transfer',
                                                'html': [
                                                    $('<i />', { 'class': 'material-icons', 'html': 'fingerprint' }),
                                                    $('<span />', { 'html': 'Devret' })
                                                ]
                                            })
                                        }),
                                        $('<li />', {
                                            'html': $('<a />', {
                                                'href': '#',
                                                'data-user-id': obj.data.id,
                                                'data-button': '__remove_user',
                                                'html': [
                                                    $('<i />', { 'class': 'material-icons', 'html': 'delete_forever' }),
                                                    $('<span />', { 'html': 'Çıkar' })
                                                ]
                                            })
                                        })
                                    ]
                                })
                            ]
                        }).appendTo($('[data-name=member-list]'))

                        $('a.secondary-content').dropdown({
                            alignment: 'right'
                        })

                        var capacity = $('span.organisation-capacity');
                        var new_capacity = parseInt(capacity.html()) + 1;

                            capacity.html(new_capacity);

                        var max_capacity = $('span.organisation-max-capacity');

                        if (new_capacity >= max_capacity.html())
                        {
                            $('form#invite-form').addClass('hide')
                        }
                        else
                        {
                            $('form#invite-form').removeClass('hide')
                        }

                        $('input[name=email]').val('')
                    }
                }
            @endpush
        @endif
    </div>

    @if ($user->id == $user->organisation->user_id)
        <div id="tab-2" class="card-content grey lighten-4">
            @if ($user->organisation->invoices()->count())
                @if ($user->organisation->invoices[0]->paid_at)
                    @include('organisation._inc.form', [ 'discount_with_year' => $discount_with_year ])
                @else
                    <div class="center-align">
                        <a href="{{ route('organisation.invoice', [ 'id' => $user->organisation->invoices[0]->invoice_id ]) }}" class="waves-effect btn-flat">Fatura</a>
                        <a href="{{ route('settings.support', [ 'type' => 'odeme-bildirimi' ]) }}" class="waves-effect btn-flat">Ödeme Bildirimi</a>
                        <a href="{{ route('organisation.invoice.payment') }}" class="waves-effect btn-flat green-text">Ödeme Sayfası</a>
                        <a href="#" class="btn-flat waves-effect red-text" id="cancel-button">İptal</a>
                    </div>

                    @push('local.scripts')
                        @isset($user->organisation->invoices[0]->reason_msg)
                            M.toast({
                                html: 'Son ödeme işlemizde bir şeyler ters gitti.',
                                classes: 'teal darken-2'
                            })

                            M.toast({
                                html: '{{ $user->organisation->invoices[0]->reason_msg }}',
                                classes: 'orange darken-2'
                            })
                        @endisset

                        $(document).on('click', '#cancel-button', function() {
                            return modal({
                                'id': 'alert',
                                'body': 'Şu anda ödeme bildiriminizi bekliyoruz. Faturayı iptal etmek istiyor musunuz?',
                                'size': 'modal-small',
                                'title': 'İptal',
                                'options': { dismissible: false },
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
                                       'class': 'waves-effect btn-flat red-text json',
                                       'data-href': '{{ route('settings.organisation.invoice.cancel') }}',
                                       'data-method': 'delete',
                                       'data-callback': '__cancel',
                                       'html': buttons.ok
                                   })
                                ]
                            })
                        })

                        function __cancel(__, obj)
                        {
                            if (obj.status == 'ok')
                            {
                                $('#modal-alert').modal('close')

                                M.toast({
                                    html: 'Fatura iptal edildi.',
                                    classes: 'green darken-2'
                                })

                                setTimeout(function() {
                                    location.href = '{{ route('settings.organisation') }}#tab-2';
                                    location.reload()
                                }, 400)
                            }
                        }
                    @endpush
                @endif
            @else
                @include('organisation._inc.form', [ 'discount_with_year' => $discount_with_year ])
            @endif
        </div>
    @endif

    <div id="tab-3" class="card-content grey lighten-4">
        <div class="card card-unstyled">
            <div class="card-content">
                @if ($user->organisation->user_id == $user->id)
                    <span>
                        <h6>Organizasyonu Sil</h6>
                        <p class="grey-text">Organizasyonu silemezsiniz ancak farklı bir kullanıcıya devredip ayrılabilirsiniz.</p>
                    </span>
                @else
                    <div class="d-flex justify-content-between">
                        <span>
                            <h6>Organizasyondan Ayrılın</h6>
                            <p class="grey-text">Organizasyondan ayrıldıktan sonra yeni bir davet ile tekrar katılabilirsiniz.</p>
                        </span>
                        <a href="#" class="btn red darken-1 waves-effect" data-button="__leave">Ayrıl</a>
                    </div>
                    @push('local.scripts')
                        @php
                        $key = 'organizasyondan ayrılmak istiyorum';
                        @endphp

                        $(document).on('click', '[data-button=__leave]', function() {
                            var mdl = modal({
                                'id': 'leave',
                                'body': [
                                    $('<p />', {
                                        'html': 'Organizasyondan ayrılmak için aşağıdaki alana küçük harflerle "{{ $key }}" yazmanız gerekiyor.'
                                    }),
                                    $('<p />', {
                                        'html': 'Bu işlem geri alınamaz!',
                                        'class': 'red-text'
                                    }),
                                    $('<div />', {
                                        'class': 'input-field',
                                        'html': [
                                            $('<input />', {
                                                'id': 'leave_key',
                                                'name': 'leave_key',
                                                'type': 'text',
                                                'class': 'validate',
                                                'pattern': '^\{{ $key }}$'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text',
                                                'html': 'Organizasyondan ayrılmak için belirlenen kelimeleri girin.'
                                            })
                                        ]
                                    })
                                ],
                                'size': 'modal-medium',
                                'title': 'Ayrıl',
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
                                       'class': 'waves-effect btn-flat red-text json',
                                       'data-href': '{{ route('settings.organisation.leave') }}',
                                       'data-include': 'leave_key',
                                       'data-method': 'post',
                                       'data-callback': '__leave',
                                       'html': buttons.ok
                                   })
                                ]
                            })

                            M.updateTextFields()

                            return mdl;
                        })

                        function __leave(__, obj)
                        {
                            var leave_key_input = $('input[name=leave_key]');

                            if (leave_key_input.val() == '{{ $key }}')
                            {
                                if (obj.status == 'ok')
                                {
                                    $('#modal-leave').modal('close')

                                    setTimeout(function() {
                                        window.location.href = '{{ route('dashboard') }}';
                                    }, 400)
                                }
                            }
                            else
                            {
                                M.toast({
                                    html: 'Onay alanı geçerli değil!',
                                    classes: 'red darken-2'
                                })
                            }
                        }
                    @endpush
                @endif
            </div>
        </div>
    </div>

    @if ($user->id == $user->organisation->user_id)
        <div id="tab-4" class="grey lighten-4">
            <div class="collection">
                @forelse ($user->organisation->invoices as $invoice)
                    <a target="_blank" href="{{ route('organisation.invoice', $invoice->invoice_id) }}" class="collection-item d-flex waves-effect {{ $invoice->paid_at ? 'grey-text' : 'red-text' }}">
                        <i class="material-icons align-self-center">history</i>
                        <span class="align-self-center">
                            <span>#{{ $invoice->invoice_id }}</span>
                            <span class="d-block grey-text">{{ date('d.m.Y H:i', strtotime($invoice->created_at)) }}</span>
                        </span>
                        <span class="ml-auto {{ $invoice->paid_at ? 'grey-text' : 'red-text' }}">{{ $invoice->paid_at ? date('d.m.Y H:i', strtotime($invoice->paid_at)) : 'ÖDENMEDİ' }}</span>
                    </a>
                @empty
                    <div class="collection-item">
                        @component('components.nothing')
                            @slot('size', 'small')
                            @slot('text', 'Organizasyonunuza ait fatura bulunmuyor.')
                        @endcomponent
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'organisation' ])
@endsection

@push('local.scripts')
    $('select').formSelect()
    $('.tabs').tabs()
@endpush
