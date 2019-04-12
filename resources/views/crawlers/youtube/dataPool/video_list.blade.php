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
            'text' => '🐞 Takip Edilen Videolar'
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
            <span class="card-title">Takip Edilen Videolar</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#videos"
                           placeholder="Ara"
                           value="{{ $organisation ? '@'.@$organisation->name : '' }}" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="videos"
             data-href="{{ route('admin.youtube.followed_videos') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#videos-more_button"
             data-callback="__videos"
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
                <img class="circle rounded-0 align-self-center" alt="Video Resmi" data-name="image" />
                <span class="align-self-center">
                    <p data-name="video-title"></p>
                    <p data-name="video-id" class="grey-text"></p>
                    <p data-name="reason"></p>
                </span>
                <span class="d-flex flex-column align-items-end">
                    <span data-name="follower" class="grey-text"></span>
                    <small data-name="hit" class="grey-text"></small>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
            </a>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="btn-small white grey-text more hide json"
       id="videos-more_button"
       data-json-target="#videos">Daha Fazla</a>
@endsection

@section('dock')
    @include('crawlers.youtube._menu', [ 'active' => 'youtube.videos' ])
@endsection

@push('local.scripts')
    function __videos(__, obj)
    {
        var ul = $('#videos');
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
                            .attr('data-name', 'video-' + o.video_id)

                        item.find('[data-name=image]').attr('src', 'https://i.ytimg.com/vi/' + o.video_id + '/hqdefault.jpg')
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)
                        item.find('[data-name=hit]').html(o.hit + ' kontrol')

                        item.find('[data-name=video-title]').html(o.video_title)
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

            var el = $('[data-name=video-' + obj.data.video_id + ']');
                el.find('[data-name=reason]')
                  .html(obj.data.reason ? obj.data.reason : '-')
                  .removeClass('green-text red-text')
                  .addClass(obj.data.reason ? 'red-text' : 'green-text')
        }
    }

    $(document).on('click', '[data-trigger=textarea]', function() {
        return modal({
            'id': 'token',
            'title': 'Sorunlu Video',
            'body': $('<form />', {
                'action': '{{ route('admin.youtube.followed_videos.reason') }}',
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
                                'html': 'Bir neden girilirse bu video takipten çıkarılacaktır.'
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
