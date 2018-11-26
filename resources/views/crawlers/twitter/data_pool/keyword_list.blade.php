@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi'
        ],
        [
            'text' => 'Twitter Ayarları',
            'link' => route('admin.twitter.settings')
        ],
        [
            'text' => 'Takip Edilen Kelimeler'
        ]
    ],
    'dock' => true
])

@push('local.styles')
    p { margin: 0; }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Takip Edilen Kelimeler" />
            <span class="card-title">Takip Edilen Kelimeler</span>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#users"
                           placeholder="Arayın" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="users"
             data-href="{{ route('admin.twitter.stream.keywords') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#users-more_button"
             data-callback="__keywords"
             data-method="post"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Kelime Yok</p>
                </div>
            </div>
            <a
                href="#"
                class="collection-item model d-none waves-effect"
                data-trigger="textarea">
                <span class="align-self-center">
                    <p data-name="keyword"></p>
                    <p data-name="reason"></p>
                </span>
                <small data-name="follower" class="badge ml-auto"></small>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect d-none json"
                id="users-more_button"
                type="button"
                data-json-target="#users">Daha Fazla</button>
    </div>
@endsection

@section('dock')
    @include('crawlers.twitter._menu', [ 'active' => 'stream.keywords' ])
@endsection

@push('local.scripts')
    function __keywords(__, obj)
    {
        var ul = $('#users');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.id).attr('data-name', o.keyword)

                        item.find('[data-name=keyword]').html(o.keyword)
                        item.find('[data-name=follower]').html(o.organisation.name)
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '-').removeClass('green-text red-text').addClass(o.reason ? 'red-text' : 'green-text')

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }

    function __form(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-token').modal('close')

            $('[data-name=' + obj.data.keyword + ']').find('[data-name=reason]')
                                                     .html(obj.data.reason ? obj.data.reason : '-')
                                                     .removeClass('green-text red-text')
                                                     .addClass(obj.data.reason ? 'red-text' : 'green-text')
        }
    }

    $(document).on('click', '[data-trigger=textarea]', function() {
        var mdl = modal({
            'id': 'token',
            'title': 'Sorunlu Kelime',
            'body': $('<form />', {
                'action': '{{ route('admin.twitter.stream.keywords.reason') }}',
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
                    }),
                    $('<br />'),
                    $('<div />', {
                        'class': 'right-align',
                        'html': [
                           $('<a />', {
                               'href': '#',
                               'class': 'modal-close waves-effect btn-flat',
                               'html': buttons.cancel
                           }),
                           $('<span />', {
                               'html': ' '
                           }),
                           $('<button />', {
                               'type': 'submit',
                               'class': 'waves-effect btn',
                               'data-submit': 'form#form',
                               'html': buttons.ok
                           })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'options': {
                dismissible: false
            }
        });

        return mdl;
    })
@endpush