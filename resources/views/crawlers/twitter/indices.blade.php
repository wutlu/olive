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
        	'text' => 'Index Yönetimi'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    var collection_timer;

    function __indices(__, obj)
    {
        var ul = $('#indices');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.uuid + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.uuid)

                        item.find('[data-name=health]').html(o.health)
                        item.find('[data-name=count]').html(number_format(o['docs.count'] ? o['docs.count'] : 0))
                        item.find('[data-name=size]').html(o['store.size'])

                        if (!selector.length)
                        {
                            item.find('[data-name=name]').html(o.index)
                            item.appendTo(ul)
                        }
                })
            }

            $('#home-loader').hide()
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#indices'))
        }, 10000)
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Twitter Index Yönetimi" />
            <span class="card-title">Twitter Index Yönetimi</span>
        </div>
        <ul class="collection load" 
             id="indices"
             data-href="{{ route('admin.twitter.indices.json') }}"
             data-callback="__indices"
             data-nothing>
            <li class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Index Yok</p>
                </div>
            </li>
            <li class="collection-item model d-none">
                <span class="align-self-center">
                	<p data-name="name"></p>
                	<p data-name="health" class="green-text"></p>
                </span>
                <small class="badge ml-auto right-align">
                	<p data-name="count"></p>
                	<p data-name="size"></p>
                </small>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@section('dock')
    <div class="card">
        <div class="collection">
            <label class="collection-item waves-effect d-block">
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('admin.twitter.option.set') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="twitter.index.auto"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    @if ($options['twitter.index.auto'] == 'on'){{ 'checked' }}@endif  />
                <span>Otomatik Index</span>
            </label>
            <div class="collection-item blue-text">Otomatik Indexlemede;<br />Twitter için oluşturulan aylık<br /> indexler, veriler<br /> alınmadan önce oluşturulur.</div>
            @if ($options['twitter.index.trends'] == 'off')
                <a
                    href="#"
                    class="collection-item waves-effect d-block json"
                    data-href="{{ route('admin.twitter.index.create') }}"
                    data-method="post"
                    data-callback="__index_create">Trend indeksini Oluştur</a>
                <div
                    class="load"
                    data-method="get"
                    data-href="{{ route('admin.twitter.index.status') }}"
                    data-callback="__index_status">
                </div>

                @push('local.scripts')
                    var index_timer;

                    function __index_status(__, obj)
                    {
                        if (obj.trends.status == 'ok')
                        {
                            $('[data-callback=__index_create]').remove()

                            M.toast({ html: 'Trend indeksi oluşturuldu.', classes: 'green' })
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
	@include('crawlers.twitter._menu', [ 'active' => 'indices' ])
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
