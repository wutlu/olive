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
            'text' => 'YouTube Ayarları',
            'link' => route('admin.youtube.settings')
        ],
        [
            'text' => '🐞 Takip Edilen Kelimeler'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        @if ($organisation)
            <div class="card-content">
                <a href="{{ route('admin.youtube.followed_channels') }}" class="chip">{{ $organisation->name }}</a>
            </div>
        @endif
        <div class="card-content">
            <span class="card-title">Takip Edilen Kelimeler</span>
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
        <div class="collection load json-clear" 
             id="collections"
             data-href="{{ route('admin.youtube.followed_keywords') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#more_button"
             data-callback="__collection"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </div>
            <a
                href="#"
                class="collection-item model hide waves-effect justify-content-between"
                data-trigger="textarea">
                <span class="align-self-center">
                    <span class="d-block" data-name="keyword"></span>
                    <p class="mb-0" data-name="reason"></p>
                </span>
                <span class="d-flex flex-column align-items-end">
                    <time data-name="created-at" class="timeago grey-text"></time>
                    <span data-name="follower" class="grey-text"></span>
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
       id="more_button"
       data-json-target="#collections">Daha Fazla</a>
@endsection

@section('dock')
    @include('crawlers.youtube._menu', [ 'active' => 'youtube.keywords' ])
@endsection

@push('local.scripts')
    function __collection(__, obj)
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
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)
                            .attr('data-name', o.keyword)

                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=keyword]').html(o.keyword)
                        item.find('[data-name=follower]').html(o.organisation.name)
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '').removeClass('hide red-text').addClass(o.reason ? 'red-text' : 'hide')

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

            var el = $('[data-name=' + obj.data.keyword + ']');
                el.find('[data-name=reason]')
                  .html(obj.data.reason ? obj.data.reason : '')
                  .removeClass('hide red-text')
                  .addClass(obj.data.reason ? 'red-text' : 'hide')
        }
    }

    $(document).on('click', '[data-trigger=textarea]', function() {
        return modal({
            'id': 'token',
            'title': 'Sorunlu Kelime',
            'body': $('<form />', {
                'action': '{{ route('admin.youtube.followed_keywords.reason') }}',
                'id': 'form',
                'class': 'json',
                'data-id': $(this).data('id'),
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
                                'html': 'Bir neden girilirse bu kelime takipten çıkarılacaktır.'
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
