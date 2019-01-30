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
    'dock' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Video Havuzu</span>
            <p class="grey-text" data-name="count"></p>
        </div>
        <div class="collection mb-0 load"
             id="collections"
             data-href="{{ route('youtube.video.list') }}"
             data-callback="__collections"
             data-method="post"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a href="#" class="collection-item avatar model hide waves-effect justify-content-between" data-trigger="delete">
            	<img class="circle align-self-center" alt="Video Resmi" data-name="image" />
                <span class="align-self-center">
                    <p data-name="video-title"></p>
                    <p data-name="reason"></p>
                </span>
                <time data-name="created-at" class="timeago grey-text right-align"></time>
            </a>
        </div>
        <div class="card-content">
            <form
                id="collection-form"
                method="put"
                action="{{ route('youtube.video.create') }}"
                data-callback="__create"
                class="json">
                <div class="input-field">
                    <input id="video_url" name="video_url" type="text" class="validate" />
                    <label for="video_url">YouTube Video Adresi</label>
                    <span class="helper-text">Örnek: "https://www.youtube.com/watch?v=YAtyQFmYD_U"</span>
                </div>
            </form>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@section('dock')
	@include('dataPool._menu', [ 'active' => 'youtube.videos' ])
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
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat red-text json',
                        'html': buttons.ok,
                        'data-href': '{{ route('youtube.video.delete') }}',
                        'data-id': __.data('id'),
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
                ]
            })
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-delete').modal('close')
            $('[data-id=' + obj.data.id + ']').remove()
        }
    }

    var collection_timer;

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.find('#video_url').val('')

            window.clearTimeout(collection_timer)

            vzAjax($('#collections'))

            collection_timer = window.setTimeout(function() {
                vzAjax($('#collections'))
            }, 10000)
        }
    }

    function __collections(__, obj)
    {
        var ul = $('#collections');
        var item_model = ul.children('.model');

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
                        item.find('[data-name=reason]')
                        	.html(o.reason ? o.reason : '-')
                        	.removeClass('green-text red-text')
                        	.addClass(o.reason ? 'red-text' : 'green-text')

                        if (!selector.length)
                        {
                            item.appendTo(ul)
                        }
                })

                $('[data-tooltip]').tooltip()
            }

            $('#home-loader').hide()

            $('[data-name=count]').html(obj.hits.length + '/{{ auth()->user()->organisation->youtube_follow_limit_video }}')
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#collections'))
        }, 10000)
    }
@endpush
