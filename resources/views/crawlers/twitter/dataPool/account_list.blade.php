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
            'text' => 'Twitter Ayarları',
            'link' => route('admin.twitter.settings')
        ],
        [
            'text' => '🐞 Takip Edilen Kullanıcılar'
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
            <span class="card-title">Takip Edilen Kullanıcılar</span>
            <span data-name="count" class="grey-text text-darken-2">0</span>
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
                           value="{{ $organisation ? '@'.@$organisation->name : '' }}" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="users"
             data-href="{{ route('admin.twitter.stream.accounts') }}"
             data-skip="0"
             data-take="10"
             data-include="string"
             data-more-button="#users-more_button"
             data-callback="__accounts"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item model hide waves-effect justify-content-between"
                data-trigger="textarea">
                <span class="align-self-center">
                    <p data-name="user-id" class="grey-text"></p>
                    <p data-name="screen-name"></p>
                    <p data-name="reason"></p>
                </span>
                <span class="d-flex flex-column align-items-end">
                    <span data-name="follower" class="badge"></span>
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
       id="users-more_button"
       data-json-target="#users">Daha Fazla</a>
@endsection

@section('dock')
    @include('crawlers.twitter._menu', [ 'active' => 'stream.accounts' ])
@endsection

@push('local.scripts')
    function __accounts(__, obj)
    {
        var ul = $('#users');
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
                            .attr('data-name', 'user-' + o.user_id)


                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=screen-name]').html(o.screen_name)
                        item.find('[data-name=user-id]').html(o.user_id)
                        item.find('[data-name=follower]').html(o.organisation.name)
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '-').removeClass('green-text red-text').addClass(o.reason ? 'red-text' : 'green-text')

                        item.appendTo(ul)
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

            var el = $('[data-name=user-' + obj.data.user_id + ']');
                el.find('[data-name=reason]')
                  .html(obj.data.reason ? obj.data.reason : '-')
                  .removeClass('green-text red-text')
                  .addClass(obj.data.reason ? 'red-text' : 'green-text')
        }
    }

    $(document).on('click', '[data-trigger=textarea]', function() {
        return modal({
            'id': 'token',
            'title': 'Sorunlu Profil',
            'body': $('<form />', {
                'action': '{{ route('admin.twitter.stream.accounts.reason') }}',
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
                                'html': 'Bir neden girilirse bu profil takipten çıkarılacaktır.'
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
