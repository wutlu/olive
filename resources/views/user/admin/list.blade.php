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
    function __users(__, obj)
    {
        var ul = $('#users');
        var item_model = ul.children('.model');

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
                        item.find('[data-name=sum]').removeClass(o.partner ? 'hide' : '').children('span').html(o.partner_paymet_history_sum)

                        item.appendTo(ul)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }

    $(document).on('click', '[data-trigger=create]', function() {
        return modal({
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
                                'class': 'validate'
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
                                'data-length': 32
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
    })

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('#users');
                collection.data('skip', 0).addClass('json-clear')

            vzAjax(collection)

            $('#modal-user').modal('close')
        }
    }

    $(document).on('change', '[data-update]', function() {
        var search = $('#users');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })
@endpush

@section('content')
    <div class="card with-bg">
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
                           data-json-target="#users"
                           placeholder="Ara"
                           value="{{ $request->q }}" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <div class="collection collection-unstyled load json-clear" 
             id="users"
             data-href="{{ route('admin.user.list.json') }}"
             data-method="post"
             data-skip="0"
             data-take="5"
             data-include="string,partner,sort"
             data-more-button="#users-more_button"
             data-callback="__users"
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
                    <p data-name="name"></p>
                    <p data-name="email" class="grey-text"></p>
                </span>
                <span class="ml-auto">
                    <p class="right-align" data-name="verified"></p>
                    <p class="right-align hide" data-name="sum">{{ config('formal.currency') }} <span></span> ciro</p>
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
       id="users-more_button"
       data-json-target="#users">Daha Fazla</a>
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
                Ciro Sıralaması
            </span>
        </div>
        <div class="collection collection-unstyled">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="sort" id="sort" type="radio" checked value="" />
                <span>Normal</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="sort" id="sort" type="radio" value="asc" />
                <span>Artan</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="sort" id="sort" type="radio" value="desc" />
                <span>Azalan</span>
            </label>
        </div>
    </div>
@endsection
