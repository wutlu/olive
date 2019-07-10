@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Kullanıcılar'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@push('local.scripts')
    function __collection(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=email]').html(o.email)
                        item.find('[data-name=avatar]').attr('src', o.avatar ? '{{ asset('/') }}' + o.avatar : '{{ asset('img/icons/people.svg') }}')
                        item.find('[data-name=verified]')
                            .html(o.partner ? o.partner : (o.verified ? 'Doğrulandı!' : 'Doğrulanmadı!'))
                            .addClass(o.partner ? 'yellow-text text-darken-2' : (o.verified ? 'green-text' : 'red-text'))

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }

    $(document).on('click', '[data-trigger=create]', function() {
        var mdl = modal({
            'id': 'user',
            'title': 'Kullanıcı Oluştur',
            'body': $('<form />', {
                'data-callback': '__create',
                'action': '{{ route('admin.user.register') }}',
                'method': 'post',
                'id': 'user-form',
                'class': 'json',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'name',
                                'name': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'value': 'test'
                            }),
                            $('<label />', {
                                'for': 'name',
                                'html': 'Kullanıcı Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Benzersiz bir kullanıcı adı girin.'
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
                                'class': 'validate',
                                'data-length': 32,
                                'value': '1234'
                            }),
                            $('<label />', {
                                'for': 'password',
                                'html': 'Şifre'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Kullanıcı için bir şifre girin.'
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
                   'data-submit': 'form#user-form',
                   'html': buttons.ok
               })
            ]
        })

        M.updateTextFields()

        return mdl;
    })

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('#collection');
                collection.data('skip', 0).addClass('json-clear')

            vzAjax(collection)

            $('#modal-user').modal('close')
        }
    }

    $(document).on('change', '[data-update]', function() {
        var search = $('#collection');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })
@endpush

@section('content')
    <div class="card">
        <div class="card-image mb-1">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">people</i>
                Kullanıcılar
                (<span data-name="count">0</span>)
            </span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
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
        <div class="collection collection-unstyled load json-clear" 
             id="collection"
             data-href="{{ route('admin.user.list.json') }}"
             data-method="post"
             data-skip="0"
             data-take="10"
             data-include="string,partner,auth"
             data-more-button="#collection-more_button"
             data-callback="__collection"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item avatar model hide json"
                data-href="{{ route('route.generate.id') }}"
                data-method="post"
                data-name="admin.user"
                data-callback="__go">
                <img alt="Avatar" data-name="avatar" class="circle" />
                <span>
                    <span data-name="name" class="d-block"></span>
                    <span data-name="email" class="grey-text"></span>
                </span>
                <span class="ml-auto right-align">
                    <span data-name="verified" class="d-block"></span>
                </span>
            </a>
        </div>

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

@section('dock')
    @if ($user)
        <div class="card yellow lighten-4">
            <div class="card-content">
                <a href="{{ route('admin.user', $user->id) }}" class="d-flex justify-content-start">
                    <img style="width: 64px; height: 64px;" class="mr-1 align-self-center" alt="{{ $user->partner }}" src="{{ asset('img/partner-'.$user->partner.'.png') }}" />
                    <span class="card-title align-self-center">{{ $user->name }}</span>
                </a>
            </div>
        </div>
    @endif

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Partner
            </span>
        </div>
        <div class="collection collection-unstyled">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="partner" id="partner" type="radio" checked value="" />
                <span>Tümü</span>
            </label>
            @foreach ($partners as $key => $partner)
                <label class="collection-item waves-effect d-block" data-update="true">
                    <input name="partner" id="partner-{{ $key }}" type="radio" value="{{ $key }}" />
                    <span>{{ $partner }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Yetkili
            </span>
        </div>
        <div class="collection collection-unstyled">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="auth" id="auth" type="radio" checked value="" />
                <span>Tümü</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="auth" id="auth-root" type="radio" value="root" />
                <span>Sistem Sorumlusu</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="auth" id="auth-admin" type="radio" value="admin" />
                <span>Yönetici</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="auth" id="auth-moderator" type="radio" value="moderator" />
                <span>Moderatör</span>
            </label>
        </div>
    </div>
@endsection
