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
            'text' => 'Video Havuzu'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Video Havuzu</span>
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
             data-href="{{ route('youtube.video.list') }}"
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
            <a href="#" class="collection-item avatar model hide waves-effect justify-content-between" data-trigger="delete">
            	<img class="circle align-self-center" alt="Video Resmi" data-name="image" />
                <span class="align-self-center">
                    <p class="mb-0" data-name="video-title"></p>
                    <p class="mb-0 grey-text" data-name="video-id"></p>
                    <p class="mb-0" data-name="reason"></p>
                </span>
                <time data-name="created-at" class="timeago grey-text"></time>
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
	@include('dataPool._menu', [ 'active' => 'youtube.videos' ])

    <div class="p-1">
        <label>
            <input name="saver" type="checkbox" value="on" />
            <span>Enter tuşu ile aramadaki kelimeyi (kelime video adresi ise) kaydet.</span>
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
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': keywords.ok,
                    'data-href': '{{ route('youtube.video.delete') }}',
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
                'data-callback': '__create',
                'data-href': '{{ route('youtube.video.create') }}'
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

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('input[name=string]').val(obj.data.channel_id)

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

                        item.find('[data-name=image]').attr('src', 'https://i.ytimg.com/vi/' + o.video_id + '/hqdefault.jpg')
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=video-title]').html(o.video_title)
                        item.find('[data-name=video-id]').html(o.video_id)

                        item.find('[data-name=reason]')
                        	.html(o.reason ? o.reason : '')
                        	.removeClass('hide red-text')
                        	.addClass(o.reason ? 'red-text' : 'hide')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.hits.length + ' / {{ auth()->user()->organisation->data_pool_youtube_video_limit }}')
        }
    }
@endpush
