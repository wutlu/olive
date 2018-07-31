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
            classes: 'green'
        })
    @endif

    @if ($user->id == $user->organisation->user_id)

    $(document).on('click', 'a.name-change', function() {
        var mdl = modal({
                'id': 'detail',
                'body': $('<div />', {
                    'class': 'input-field',
                    'html': [
                        $('<input />', {
                            'id': 'organisation_name',
                            'name': 'organisation_name',
                            'type': 'text',
                            'class': 'validate',
                            'val': '{{ $user->organisation->name }}',
                            'data-length': 16
                        }),
                        $('<label />', {
                            'for': 'organisation_name',
                            'html': 'Organizasyon Adı'
                        }),
                        $('<span />', {
                            'class': 'helper-text'
                        })
                    ]
                }),
                'size': 'modal-medium',
                'title': 'Ad Değiştir',
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
                       'class': 'waves-effect btn json',
                       'data-href': '{{ route('organisation.update.name') }}',
                       'data-method': 'patch',
                       'data-include': 'organisation_name',
                       'data-callback': '__update__organisation_name',
                       'html': buttons.update
                   })
               ])

        M.updateTextFields()

        $('input[name=organisation_name]').characterCounter()
    })

    function __update__organisation_name(__, obj)
    {
        if (obj.status == 'ok')
        {
            var name = $('#organisation_name').val();

            $('#organisation-card').find('span.card-title').children('span').html(name)

            $('#modal-detail').modal('close')

            M.toast({
                html: 'Organizasyon Adı güncellendi',
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
            <a class="name-change material-icons" href="#">create</a>
            @endif
        </span>
    </div>

    <div class="card-tabs">
        <ul class="tabs tabs-fixed-width">
            <li class="tab">
                <a href="#tab-1">
                    <i class="material-icons">people</i>
                </a>
            </li>
            <li class="tab">
                <a href="#tab-2">
                    <i class="material-icons">settings</i>
                </a>
            </li>
            <li class="tab">
                <a href="#tab-3">
                    <i class="material-icons">tune</i>
                </a>
            </li>
        </ul>
    </div>
    <div id="tab-1" class="card-content grey lighten-4">
        <div class="card-content">
            <p class="grey-text">
                <span class="organisation-capacity">{{ count($user->organisation->users) }}</span>/<span class="organisation-max-capacity">{{ $user->organisation->capacity }}</span> kullanıcı
            </p>
        </div>

        <ul class="collection member-list">
            @foreach ($user->organisation->users as $u)
            <li class="collection-item avatar" id="organisation-user-{{ $u->id }}">
                <img src="{{ $u->avatar() }}" alt="avatar" class="circle">
                <span class="title">{{ $u->name }}</span>
                <p class="grey-text">{{ $u->email }}</p>
                <p class="grey-text">{{ ($u->id == $user->organisation->user_id) ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>

                @if ($user->id != $u->id && $user->id == $user->organisation->user_id)
                <a href="#" class="secondary-content dropdown-trigger" data-target="dropdown-user-{{ $u->id }}">
                    <i class="material-icons">more_vert</i>
                </a>

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
            </li>
            @endforeach
        </ul>

        @if ($user->id == $user->organisation->user_id)

            @push('local.scripts')
            $(document).on('click', '[data-button=__transfer]', function() {
                var __ = $(this);

                var org_name = $('#organisation-card').find('span.card-title').children('span').html();
                var user_name = __.closest('li.collection-item').find('span.title').html();

                var mdl = modal({
                        'id': 'transfer',
                        'body': 'Sahip olduğunuz [' + org_name + '] adlı organizasyonu, ' + user_name + ' adlı kullanıcıya devretmek üzeresiniz!',
                        'size': 'modal-small',
                        'title': 'Devret',
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
                               'data-href': '{{ route('settings.organisation.transfer') }}',
                               'data-user_id': __.data('user-id'),
                               'data-method': 'post',
                               'data-callback': '__transfer',
                               'html': buttons.ok
                           })
                       ])
            }).on('click', '[data-button=__remove_user]', function() {
                var __ = $(this);

                var user_name = __.closest('li.collection-item').find('span.title').html();
                var mdl = modal({
                        'id': 'remove',
                        'body': user_name + ' adlı kullanıcıyı organizasyondan çıkarmak üzeresiniz!',
                        'size': 'modal-small',
                        'title': 'Çıkar',
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
                               'data-href': '{{ route('settings.organisation.remove') }}',
                               'data-user_id': __.data('user-id'),
                               'data-method': 'post',
                               'data-callback': '__remove_user',
                               'html': buttons.ok
                           })
                       ])
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
                        $('form#invite-form').addClass('d-none')
                    }
                    else
                    {
                        $('form#invite-form').removeClass('d-none')
                    }

                    $('#organisation-user-' + __.data('user_id')).remove()
                    $('#modal-remove').modal('close')

                    M.toast({
                        html: 'Kullanıcı Çıkarıldı',
                        classes: 'green'
                    })
                }
            }
            @endpush

        @endif

        @if ($user->id == $user->organisation->user_id)
        <form
            id="invite-form"
            method="post"
            action="{{ route('settings.organisation.invite') }}"
            data-callback="__invite"
            class="json {{ count($user->organisation->users) >= $user->organisation->capacity ? 'd-none' : '' }}">
            <div class="input-field teal-text">
                <input name="email" id="email" type="email" class="validate" />
                <label for="email">E-posta</label>
                <small class="helper-text">Gireceğiniz e-posta adresine bağlı hesap organizasyonunuza eklenir.</small>
            </div>
            <button type="submit" class="waves-effect waves-dark btn-small">Ekle</button>
        </form>
        @push('local.scripts')

        function __invite(__, obj)
        {
            if (obj.status == 'ok')
            {
                M.toast({
                    html: 'Kullanıcı Eklendi',
                    classes: 'green'
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
                }).appendTo($('ul.member-list'))

                $('a.secondary-content').dropdown({
                    alignment: 'right'
                })

                var capacity = $('span.organisation-capacity');
                var new_capacity = parseInt(capacity.html()) + 1;

                    capacity.html(new_capacity);

                var max_capacity = $('span.organisation-max-capacity');

                if (new_capacity >= max_capacity.html())
                {
                    $('form#invite-form').addClass('d-none')
                }
                else
                {
                    $('form#invite-form').removeClass('d-none')
                }

                $('input[name=email]').val('')
            }
        }

        @endpush
        @endif
    </div>
    <div id="tab-2" class="card-content grey lighten-4">
        @if ($user->organisation->user_id == $user->id)
        <div class="d-flex justify-content-between">
            <span>
                <h6>Organizasyonu Silin</h6>
                <p class="grey-text">- Organizasyona ait tüm etkinlikler kalıcı olarak silinir.</p>
                <p class="grey-text">- Organizasyona dahil tüm kullanıcıların organizasyon bağlantıları kaldırılır.</p>
                <p class="grey-text">- Varsa kalan kullanım tutarı tüm masraflar düşüldükten sonra indirim kuponu olarak e-posta adresinize gönderilecektir.</p>
                <p class="grey-text">- İndirim kuponlarını sadece yeni bir organizasyon planı oluşturmak veya organizasyon kullanım süresini uzatmak için kullanılabilir.</p>
            </span>
            <a href="#" class="btn red darken-1 waves-effect" data-button="__delete">Sil</a>
        </div>

        @php
        $key = 'organizasyonu silmek istiyorum';
        @endphp

        @push('local.scripts')
        $(document).on('click', '[data-button=__delete]', function() {
            var mdl = modal({
                    'id': 'delete',
                    'body': [
                        $('<p />', {
                            'html': 'Organizasyonu silmek için aşağıdaki alana küçük harflerle "{{ $key }}" yazmalısınız.'
                        }),
                        $('<p />', {
                            'html': 'Bu işlem geri alınamaz!',
                            'class': 'red-text'
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'delete_key',
                                    'name': 'delete_key',
                                    'type': 'text',
                                    'class': 'validate',
                                    'pattern': '^\{{ $key }}$'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Organizasyonu silmek için belirlenen kelimeleri girin.'
                                })
                            ]
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'password',
                                    'name': 'password',
                                    'type': 'password',
                                    'class': 'validate'
                                }),
                                $('<label />', {
                                    'for': 'password',
                                    'html': 'Mevcut Şifreniz'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Hesap şifrenizi girin.'
                                })
                            ]
                        })
                    ],
                    'size': 'modal-small',
                    'title': 'Sil',
                    'options': {}
                });

                M.updateTextFields()

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
                           'class': 'waves-effect btn red darken-4 json',
                           'data-href': '{{ route('settings.organisation.delete') }}',
                           'data-include': 'delete_key,password',
                           'data-method': 'post',
                           'data-callback': '__delete',
                           'html': buttons.ok
                       })
                   ])
        })

        function __delete(__, obj)
        {
            var delete_key_input = $('input[name=delete_key]');

            if (delete_key_input.val() == '{{ $key }}')
            {
                if (obj.status == 'ok')
                {
                    $('#modal-delete').modal('close')

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

        @else
        <div class="d-flex justify-content-between">
            <span>
                <h6>Organizasyondan Ayrılın</h6>
                <p class="grey-text">Organizasyondan ayrıldıktan sonra yeni bir davet ile tekrar katılabilirsiniz.</p>
            </span>
            <a href="#" class="btn red darken-1 waves-effect" data-button="__leave">Ayrıl</a>
        </div>

        @php
        $key = 'organizasyondan ayrılmak istiyorum';
        @endphp

        @push('local.scripts')
        $(document).on('click', '[data-button=__leave]', function() {
            var mdl = modal({
                    'id': 'leave',
                    'body': [
                        $('<p />', {
                            'html': 'Organizasyondan ayrılmak için aşağıdaki alana küçük harflerle "{{ $key }}" yazmalısınız.'
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
                    'size': 'modal-small',
                    'title': 'Ayrıl',
                    'options': {}
                });

                M.updateTextFields()

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
                           'class': 'waves-effect btn red darken-4 json',
                           'data-href': '{{ route('settings.organisation.leave') }}',
                           'data-include': 'leave_key',
                           'data-method': 'post',
                           'data-callback': '__leave',
                           'html': buttons.ok
                       })
                   ])
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
    <div id="tab-3" class="card-content grey lighten-4">
        Süre ayarları
    </div>
</div>
@endsection

@section('dock')
    @include('layouts.dock.settings', [ 'active' => 'organisation' ])
@endsection

@push('local.scripts')

$('.tabs').tabs()
$('a.secondary-content').dropdown({
    alignment: 'right'
})

@endpush
