@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu',
            'link' => route('data_pool.dashboard')
        ],
        [
            'text' => 'Instagram'
        ],
        [
            'text' => 'Bağlantı Havuzu'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Bağlantı Havuzu</span>
            <span data-name="count" class="grey-text text-darken-2">0 / 0</span>
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

        <ul class="collection load json-clear loading" 
             id="collections"
             data-href="{{ route('instagram.url.list') }}"
             data-skip="0"
             data-take="10"
             data-include="string"
             data-more-button="#more_button"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </li>
            <li class="collection-item model hide">
                <div class="d-flex justify-content-between">
                    <span>
                        <a href="#" data-name="id" target="_blank"></a>
                        <span data-name="method" class="d-block grey-text"></span>
                        <p class="mb-0" data-name="reason"></p>
                    </span>
                    <span class="right-align">
                        <span data-name="hit">0</span> <span class="grey-text">medya</span>
                        <time class="d-block timeago grey-text" data-name="created-at">-</time>
                        <a href="#" data-trigger="delete">
                            <i class="material-icons">delete</i>
                        </a>
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
    @include('dataPool._menu', [ 'active' => 'instagram.urls' ])

    <div class="p-1">
        <label>
            <input name="saver" type="checkbox" value="on" />
            <span>Enter tuşu ile aramadaki kelimeyi kaydet.</span>
        </label>
    </div>

    <div class="yellow-text text-darken-2 mt-1">
        @component('components.alert')
            @slot('icon', 'help')
            @slot('text', '<span class="yellow darken-2 white-text">https://www.instagram.com/explore/tags/deneme/</span> gibi bağlantı veya direk kullanıcı adı şeklinde ekleme yapabilirsiniz.')
        @endcomponent
    </div>
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
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '').addClass(o.reason ? 'red-text' : 'hide')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total + ' / {{ auth()->user()->organisation->data_pool_instagram_follow_limit }}')
        }
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
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
                    'data-href': '{{ route('instagram.url.delete') }}',
                    'data-id': $(this).closest('li').data('id'),
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
                'data-href': '{{ route('instagram.url.create') }}',
                'data-callback': '__create'
            }))
        }
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Bağlantı Silindi!', classes: 'red' })

            $('#modal-delete').modal('close')

            $('li[data-id=' + __.data('id') + ']').slideUp()
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Bağlantı takibe alındı!', classes: 'green' })

            setTimeout(function() {
                vzAjax($('#collections'))
            }, 200)
        }
    }
@endpush
