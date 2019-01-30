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
    'dock' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kelime Havuzu</span>
            <p class="grey-text" data-name="count"></p>
        </div>
        <div class="collection mb-0 load"
             id="collections"
             data-href="{{ route('youtube.keyword.list') }}"
             data-callback="__collections"
             data-method="post"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a href="#" class="collection-item model hide waves-effect justify-content-between" data-trigger="delete">
                <span class="align-self-center">
                    <p data-name="title"></p>
                    <p data-name="reason"></p>
                </span>
                <time data-name="created-at" class="timeago grey-text right-align"></time>
            </a>
        </div>
        <div class="card-content">
            <form
                id="collection-form"
                method="put"
                action="{{ route('youtube.keyword.create') }}"
                data-callback="__create"
                class="json">
                <div class="input-field">
                    <input id="keyword" name="keyword" type="text" class="validate" />
                    <label for="keyword">Kelime Ekleyin</label>
                    <span class="helper-text">Örnek: "veri.zone, bankacalıkta kampanya"</span>
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
	@include('dataPool._menu', [ 'active' => 'youtube.keywords' ])
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
                        'data-href': '{{ route('youtube.keyword.delete') }}',
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
            __.find('#keyword').val('')

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

                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=title]').html(o.keyword)
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

            $('[data-name=count]').html(obj.hits.length + '/{{ auth()->user()->organisation->youtube_follow_limit_keyword }}')
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#collections'))
        }, 10000)
    }
@endpush
