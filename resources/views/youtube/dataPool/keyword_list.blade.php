@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu',
            'link' => route('data_pool.dashboard')
        ],
        [
            'text' => 'YouTube'
        ],
        [
            'text' => 'Kelime Havuzu'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kelime Havuzu</span>
            <p class="grey-text text-darken-2" data-name="count"></p>
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

        <div class="collection mb-0 load"
             id="collections"
             data-href="{{ route('youtube.keyword.list') }}"
             data-skip="0"
             data-take="15"
             data-include="string"
             data-more-button="#more_button"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </div>
            <a href="#" class="collection-item model hide waves-effect justify-content-between" data-trigger="delete">
                <span class="align-self-center">
                    <p class="mb-0" data-name="title"></p>
                    <p class="mb-0" data-name="reason"></p>
                </span>
                <time data-name="created-at" class="timeago grey-text right-align"></time>
            </a>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
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
	@include('dataPool._menu', [ 'active' => 'youtube.keywords' ])

    <div class="p-1">
        <label>
            <input name="saver" type="checkbox" value="on" />
            <span>Enter tuşu ile aramadaki kelimeyi kaydet.</span>
        </label>
    </div>
@endsection

@push('local.scripts')
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
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('youtube.keyword.delete') }}',
                    'data-id': $(this).data('id'),
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
                'data-href': '{{ route('youtube.keyword.create') }}'
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
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].collection-item'),

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)

                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=title]').html(o.keyword)

                        item.find('[data-name=reason]')
                        	.html(o.reason ? o.reason : '')
                        	.removeClass('hide red-text')
                        	.addClass(o.reason ? 'red-text' : 'hide')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.hits.length + ' / {{ auth()->user()->organisation->data_pool_youtube_keyword_limit }}')
        }
    }
@endpush
