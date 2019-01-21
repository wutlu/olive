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
            'text' => 'Medya Botları',
            'link' => route('crawlers.media.list')
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

            $('#home-loader').hide()
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#indices'))
        }, 10000)
    }

    function __timeout(__)
    {
        $('#home-loader').hide()

        __.find('.nothing').removeClass('hide')
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Medya Index Yönetimi" />
            <span class="card-title">Medya Index Yönetimi</span>
        </div>
        <ul class="collection load" 
             id="indices"
             data-href="{{ route('crawlers.media.indices.json') }}"
             data-callback="__indices"
             data-method="post"
             data-nothing
             data-timeout="4000"
             data-error-callback="__timeout">
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="collection-item model hide justify-content-between">
                <span class="align-self-center">
                    <p data-name="name" class="mb-0"></p>
                    <p data-name="health" class="mb-0"></p>
                </span>
                <small class="grey-text d-flex align-items-end flex-column">
                    <p data-name="count" class="mb-0"></p>
                    <p data-name="size" class="mb-0"></p>
                </small>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@section('dock')
    @if ($options['media.index.status'] != count($index_groups))
        <div class="card" data-name="index-trigger-menu">
            <div class="card-content red white-text">Uyarı! Medya tarafında eksik indexler mevcut!</div>
            <div class="collection">
                <a
                    href="#"
                    class="collection-item waves-effect d-block json"
                    data-href="{{ route('crawlers.media.index.create') }}"
                    data-method="post"
                    data-callback="__index_create"
                    data-callbefore="__index_create_before">Eksik Indexleri Oluştur</a>
            </div>
        </div>
        @component('components.loader')
            @slot('color', 'cyan')
            @slot('class', 'hide')
            @slot('id', 'index-trigger-loader')
        @endcomponent
    @endif
    @include('crawlers.media._menu', [ 'active' => 'indices' ])
@endsection

@push('local.scripts')
    function __index_create_before(__)
    {
        $('#index-trigger-loader').removeClass('hide')
        __.addClass('disabled')
    }

    function __index_create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Indexler oluşturuldu. Medya botlarını çalıştırabilirsiniz.', classes: 'green darken-2' })

            $('[data-name=index-trigger-menu]').addClass('hide')
        }
        else if (obj.status == 'err')
        {
            M.toast({ html: 'Indexler oluşturulamadı.', classes: 'red darken-2' })
        }

        $('#index-trigger-loader').addClass('hide')
        __.removeClass('disabled')
    }
@endpush
