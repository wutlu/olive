@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Organizasyonlar'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@push('local.scripts')
    function __organisations(__, obj)
    {
        var ul = $('#organisations');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=status]').html(o.status ? 'Aktif' : 'Pasif').addClass(o.status ? 'green-text' : 'red-text')
                        item.find('[data-name=author]').html(o.author.name)
                        item.find('[data-name=avatar]').attr('src', o.author.avatar ? '{{ asset('/') }}' + o.author.avatar : '{{ asset('img/icons/people.svg') }}')

                        item.appendTo(ul)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }

    function __autocomplete(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = modal({
                'id': 'organisation',
                'title': 'Organizasyon Oluştur',
                'body': $('<form />', {
                    'data-callback': '__create',

                    'action': '{{ route('admin.organisation.create') }}',
                    'method': 'post',
                    'id': 'organisation-form',
                    'class': 'json',
                    'html': [
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'user_name',
                                    'name': 'user_name',
                                    'type': 'text',
                                    'class': 'validate autocomplete'
                                }),
                                $('<label />', {
                                    'for': 'user_name',
                                    'html': 'Kullanıcı Adı'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Tanımlanacak kullanıcının kullanıcı adını girin.'
                                })
                            ]
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'organisation_name',
                                    'name': 'organisation_name',
                                    'type': 'text',
                                    'class': 'validate',
                                    'data-length': 32
                                }),
                                $('<label />', {
                                    'for': 'organisation_name',
                                    'html': 'Organizasyon Adı'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Organizasyonun adını girin.'
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
                       'data-submit': 'form#organisation-form',
                       'html': buttons.ok
                   })
                ]
            })

            $('input[name=user_name]').autocomplete({
                data: obj.data,
                limit: 2
            })

            return mdl;
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-organisation').modal('close')

            window.location = obj.data.route
        }
    }

    $(document).on('change', '[data-update]', function() {
        var search = $('#organisations');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })
@endpush

@section('dock')
    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Filtrele
            </span>
        </div>
        <div class="collection">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-all" type="radio" value="" />
                <span>Tümü</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-on" type="radio" value="on" checked />
                <span>Aktif</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-off" type="radio" value="off" />
                <span>Pasif</span>
            </label>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-image mb-1">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">people</i>
                Organizasyonlar
                (<span data-name="count">0</span>)
            </span>
            <a
                href="#"
                class="btn-floating btn-large halfway-fab waves-effect white json"
                data-method="post"
                data-href="{{ route('admin.user.autocomplete') }}"
                data-callback="__autocomplete">
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
                           data-json-target="#organisations"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <div class="collection collection-unstyled load json-clear" 
             id="organisations"
             data-href="{{ route('admin.organisation.list.json') }}"
             data-method="post"
             data-skip="0"
             data-take="10"
             data-include="string,status"
             data-more-button="#organisations-more_button"
             data-callback="__organisations"
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
                data-name="admin.organisation"
                data-callback="__go">
                <img alt="Avatar" data-name="avatar" class="circle" />
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="author" class="grey-text"></p>
                </span>
                <span data-name="status" class="ml-auto"></span>
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
       id="organisations-more_button"
       data-json-target="#organisations">Daha Fazla</a>
@endsection

@push('local.scripts')
    $('select').formSelect()
@endpush
