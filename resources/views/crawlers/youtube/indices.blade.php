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
        	'text' => '🐞 Index Yönetimi'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@push('local.scripts')
    var collection_timer;

    function __indices(__, obj)
    {
        var ul = $('#indices');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.uuid + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.uuid)

                        item.find('[data-name=health]').html(o.health)
                                                       .removeClass('green-text red-text yellow-text')
                                                       .addClass(o.health + '-text')
                        item.find('[data-name=count]').html(number_format(o['docs.count'] ? o['docs.count'] : 0))
                        item.find('[data-name=size]').html(o['store.size'])

                        if (!selector.length)
                        {
                            item.find('[data-name=name]').html(o.index)
                            item.appendTo(ul)
                        }
                })
            }
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#indices'))
        }, 10000)
    }

    function __timeout(__)
    {
        __.find('.nothing').removeClass('hide')
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">YouTube Index Yönetimi</span>
        </div>
        <ul class="collection load" 
             id="indices"
             data-href="{{ route('admin.youtube.indices.json') }}"
             data-callback="__indices"
             data-method="post"
             data-nothing
             data-timeout="4000"
             data-loader="#home-loader"
             data-error-callback="__timeout">
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="collection-item model hide justify-content-between">
                <span class="align-self-center">
                	<p data-name="name" class="mb-0"></p>
                	<p data-name="health" class="mb-0"></p>
                </span>
                <small class="grey-text d-flex flex-column align-items-end">
                	<p data-name="count" class="mb-0"></p>
                	<p data-name="size" class="mb-0"></p>
                </small>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
@endsection

@section('dock')
    <div class="card mb-1">
        <div class="collection">
            <label class="collection-item waves-effect d-block">
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('admin.youtube.option.set') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="youtube.index.auto"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    @if ($options['youtube.index.auto'] == 'on'){{ 'checked' }}@endif  />
                <span>Otomatik Index</span>
            </label>
            <div class="collection-item grey lighten-4">Otomatik Indexlemede;<br />YouTube için oluşturulan aylık<br /> indexler, veriler<br /> alınmadan önce oluşturulur.</div>
            @if ($options['youtube.index.videos'] == 'off')
                <a
                    href="#"
                    class="collection-item waves-effect d-block json"
                    data-href="{{ route('admin.youtube.index.create') }}"
                    data-method="post"
                    data-callback="__index_create">Video Indexini Oluştur</a>
                <div
                    class="load"
                    data-method="post"
                    data-href="{{ route('admin.youtube.index.status') }}"
                    data-callback="__index_status">
                </div>

                @push('local.scripts')
                    var index_timer;

                    function __index_status(__, obj)
                    {
                        if (obj.videos.status == 'ok')
                        {
                            $('[data-callback=__index_create]').remove()

                            M.toast({ html: 'Video indexi oluşturuldu.', classes: 'green darken-2' })
                        }
                        else
                        {
                            window.clearTimeout(index_timer)

                            index_timer = window.setTimeout(function() {
                                vzAjax($('[data-callback=__index_status]'))
                            }, 5000)
                        }
                    }
                @endpush
            @endif
        </div>
    </div>
	@include('crawlers.youtube._menu', [ 'active' => 'indices' ])
@endsection

@push('local.scripts')
    function __index_create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Index oluşturma isteği gönderildi. Lütfen bekleyin...', classes: 'orange' })
        }
    }
@endpush
