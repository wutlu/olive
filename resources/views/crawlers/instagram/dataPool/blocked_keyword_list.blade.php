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
            'text' => '🐞 Engelli Trend Kelimeleri'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Engelli Trend Kelimeleri</span>
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
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="collections"
             data-href="{{ route('admin.instagram.trend.blocked_keywords') }}"
             data-skip="0"
             data-take="10"
             data-include="string"
             data-more-button="#more_button"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a href="#" class="collection-item model hide waves-effect justify-content-between" data-trigger="delete">
                <span class="align-self-center" data-name="keyword"></span>
                <time data-name="created-at" class="align-self-center timeago grey-text">-</time>
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
    @include('crawlers.instagram._menu', [ 'active' => 'trend.blocked_keywords' ])

    <div class="p-1">
        <label>
            <input name="saver" type="checkbox" value="on" />
            <span>Enter tuşu ile aramadaki kelimeyi kaydet.</span>
        </label>
    </div>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=delete]', function() {
        var __ = $(this);

        var mdl = modal({
                'id': 'delete',
                'body': 'Bu kaydı silmek istiyor musunuz?',
                'size': 'modal-small',
                'title': 'Sil',
                'options': {},
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat grey-text',
                        'html': keywords.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat red-text json',
                        'html': keywords.ok,
                        'data-href': '{{ route('admin.instagram.trend.blocked_keywords') }}',
                        'data-id': __.data('id'),
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
                ]
            })
    }).on('keyup', 'input[name=string]', function(e) {
        if (e.keyCode == 13 && $('input[name=saver]').is(':checked'))
        {
            vzAjax($('<div />', {
                'data-include': 'string',
                'data-method': 'put',
                'data-href': '{{ route('admin.instagram.trend.blocked_keywords') }}'
            }))
        }
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-delete').modal('close')

            var search = $('#collections');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }
    }

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
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)
                            .attr('data-name', o.keyword)

                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=keyword]').html(o.keyword)

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }
@endpush
