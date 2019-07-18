@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi',
            'link' => route('crawlers')
        ],
        [
            'text' => 'Instagram Ayarları',
            'link' => route('admin.instagram.settings')
        ],
        [
            'text' => '🐞 Takip Edilen Bağlantılar'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        @if ($organisation)
            <div class="card-content">
                <a href="{{ route('admin.instagram.urls') }}" class="chip">{{ $organisation->name }}</a>
            </div>
        @endif

        <div class="card-content">
            <span class="card-title">Takip Edilen Bağlantılar</span>
            <span data-name="count" class="grey-text text-darken-2">0</span>
        </div>
        <nav class="nav-half mb-0">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#collections"
                           placeholder="Ara"
                           value="{{ $organisation ? '@'.@$organisation->name : '' }}" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul class="collection load json-clear" 
             id="collections"
             data-href="{{ route('admin.instagram.urls') }}"
             data-skip="0"
             data-take="10"
             data-include="string"
             data-more-button="#more_button"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="collection-item model hide">
                <div class="d-flex justify-content-between">
                    <span>
                        <span data-name="method" class="d-block"></span>
                        <a href="#" class="d-block grey-text" data-name="id" target="_blank"></a>
                        <p class="mb-0" data-name="reason"></p>
                    </span>
                    <span class="right-align">
                        <span class="d-flex justify-content-end">
                            <span class="align-self-center mr-1" data-name="follower"></span>
                            <a class="align-self-center" href="#" data-trigger="reason">
                                <i class="material-icons">report_problem</i>
                            </a>
                        </span>
                        <span data-name="hit">0</span> <span class="grey-text">medya</span>
                        <time class="d-block timeago grey-text" data-name="created-at">-</time>
                    </span>
                </div>
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
       id="more_button"
       data-json-target="#collections">Daha Fazla</a>
@endsection

@section('dock')
    @include('crawlers.instagram._menu', [ 'active' => 'following.urls' ])
@endsection

@push('local.scripts')
    function __collections(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();

                        item.removeClass('model hide')
                            .addClass('_tmp')
                            .attr('data-id', o.id)

                        item.find('[data-name=created-at]').attr('data-time', o.created_at)
                        item.find('[data-name=hit]').html(o.hit)
                        item.find('[data-name=method]').html(o.method == 'location' ? 'Lokasyon' : o.method == 'user' ? 'Kullanıcı' : 'Hashtag')

                        item.find('[data-name=id]').attr('href', o.url).html(o.url.replace('https://www.instagram.com/', ''))
                        item.find('[data-name=follower]').html(o.organisation.name)
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '').addClass(o.reason ? 'red-text' : 'hide')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }

    function __form(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-token').modal('close')

            $('[data-id=' + obj.data.id + ']').find('[data-name=reason]')
                                              .html(obj.data.reason ? obj.data.reason : '')
                                              .removeClass('hide red-text')
                                              .addClass(obj.data.reason ? 'red-text' : 'hide')
        }
    }

    $(document).on('click', '[data-trigger=reason]', function() {
        return modal({
            'id': 'token',
            'title': 'Sorunlu Bağlantı',
            'body': $('<form />', {
                'action': '{{ route('admin.instagram.urls.reason') }}',
                'id': 'form',
                'class': 'json',
                'data-id': $(this).closest('li').data('id'),
                'data-method': 'patch',
                'data-callback': '__form',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'reason',
                                'name': 'reason',
                                'type': 'text',
                                'class': 'validate',
                                'maxlength': 255
                            }),
                            $('<label />', {
                                'for': 'reason',
                                'html': 'Neden?'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Bir neden girilirse bu bağlantı takipten çıkarılacaktır.'
                            })
                        ]
                    }),
                    $('<p />', {
                        'class': 'red-text',
                        'html': 'Bu işlem farklı organizasyonlardaki benzer kayıtları da etkileyecektir.'
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
                    'data-submit': 'form#form',
                    'html': buttons.ok
                })
            ]
        })
    })
@endpush
