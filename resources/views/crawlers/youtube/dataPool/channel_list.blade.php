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
            'text' => '🐞 Takip Edilen Kanallar'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card with-bg">
        @if ($organisation)
            <div class="card-content">
                <a href="{{ route('admin.youtube.followed_channels') }}" class="chip">
                    {{ $organisation->name }}
                </a>
            </div>
        @endif
        <div class="card-content">
            <span class="card-title">Takip Edilen Kanallar</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#channels"
                           placeholder="Ara"
                           value="{{ $organisation ? '@'.@$organisation->name : '' }}" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="channels"
             data-href="{{ route('admin.youtube.followed_channels') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#channels-more_button"
             data-callback="__channels"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item avatar model hide waves-effect justify-content-between"
                data-trigger="textarea">
                <img class="circle align-self-center" alt="Kanal Resmi" data-name="image" />
                <span class="align-self-center">
                    <p data-name="channel-title"></p>
                    <p data-name="channel-id" class="grey-text"></p>
                    <p data-name="reason"></p>
                </span>
                <span class="d-flex flex-column align-items-end">
                    <span data-name="follower" class="grey-text"></span>
                    <time data-name="created-at" class="timeago grey-text"></time>
                    <small data-name="hit" class="grey-text"></small>
                </span>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="channels-more_button"
                type="button"
                data-json-target="#channels">Daha Fazla</button>
    </div>
@endsection

@section('dock')
    @include('crawlers.youtube._menu', [ 'active' => 'youtube.channels' ])
@endsection

@push('local.scripts')
    function __channels(__, obj)
    {
        var ul = $('#channels');
        var item_model = ul.children('.model');

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
                            .attr('data-name', 'channel-' + o.channel_id)

                        item.find('[data-name=hit]').html(o.hit + ' kontrol')

                        item.find('[data-name=image]').attr('src', o.channel_image)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=channel-title]').html(o.channel_title)
                        item.find('[data-name=channel-id]').html(o.channel_id)

                        item.find('[data-name=follower]').html(o.organisation.name)
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '-').removeClass('green-text red-text').addClass(o.reason ? 'red-text' : 'green-text')

                        item.appendTo(ul)
                })
            }
        }
    }

    function __form(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-token').modal('close')

            var el = $('[data-name=channel-' + obj.data.channel_id + ']');
                el.find('[data-name=reason]')
                  .html(obj.data.reason ? obj.data.reason : '-')
                  .removeClass('green-text red-text')
                  .addClass(obj.data.reason ? 'red-text' : 'green-text')
        }
    }

    $(document).on('click', '[data-trigger=textarea]', function() {
        return modal({
            'id': 'token',
            'title': 'Sorunlu Kanal',
            'body': $('<form />', {
                'action': '{{ route('admin.youtube.followed_channels.reason') }}',
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
                                'html': 'Bir neden girilirse bu kanal takipten çıkarılacaktır.'
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
                   'class': 'waves-effect btn-flat cyan-text',
                   'data-submit': 'form#form',
                   'html': buttons.ok
               })
            ]
        })
    })
@endpush
