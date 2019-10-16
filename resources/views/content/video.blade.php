@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'dock' => true,
    'pin_group' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ],
    'footer_hide' => true
])

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'video-comments',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">people</i> Video: Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'active' => true,
            'info' => 'İlgili videoya yapılan yorumların günlere dağılım grafiği.'
        ],
        [
            'type' => 'video-comments',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">people</i> Video: Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'info' => 'İlgili videoya yapılan yorumların saatlere dağılım grafiği.'
        ],

        [
            'type' => 'video-by-video',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Kanal: Yükleme Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_3',
            'info' => 'İlgili videoyu yükleyen kanalın yüklemelerinin günlere dağılımı.'
        ],
        [
            'type' => 'video-by-video',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Kanal: Yükleme Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_4',
            'info' => 'İlgili videoyu yükleyen kanalın yüklemelerinin saatlere dağılımı.'
        ],

        [
            'type' => 'comment-by-video',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Kanal: Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_5',
            'info' => 'İlgili videoyu yükleyen kanalın yaptığı yorumların günlere dağılımı.'
        ],
        [
            'type' => 'comment-by-video',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Kanal: Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_6',
            'info' => 'İlgili videoyu yükleyen kanalın yaptığı yorumların saatlere dağılımı.'
        ],
    ]
])

@push('wildcard-top')
    <div class="card">
        <div class="card-content d-flex justify-content-between">
            <span class="align-self-center">
                <span class="card-title">{{ $document['_source']['title'] }}</span>
                <a href="https://www.youtube.com/watch?v={{ $document['_source']['id'] }}" target="_blank" class="green-text">https://www.youtube.com/watch?v={{ $document['_source']['id'] }}</a>
            </span>
            <img alt="YouTube" src="{{ asset('img/logos/youtube.svg') }}" class="align-self-center" style="width: 64px;" />
        </div>
    </div>
@endpush

@section('subcard')
    <div class="card">
        <div class="card-content">
            <nav class="nav-half mb-0">
                <div class="nav-wrapper">
                    <div class="input-field">
                        <input id="string"
                               name="string"
                               type="search"
                               class="validate json json-search"
                               data-json-target=".searcher.active->children(.collection)"
                               placeholder="Ara" />
                        <label class="label-icon" for="string">
                            <i class="material-icons">search</i>
                        </label>
                    </div>
                </div>
            </nav>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width sub-tabs">
                <li class="tab">
                    <a href="#commentsVideo" class="active">Videoya Yapılan Yorumlar</a>
                </li>
                <li class="tab">
                    <a href="#commentsUser">Kanalın Yaptığı Yorumlar</a>
                </li>
                <li class="tab">
                    <a href="#videosUser">Kanalın Diğer Videoları</a>
                </li>
                <li class="tab">
                    <a href="#videosSmilar">Benzer Videolar</a>
                </li>
            </ul>
        </div>

        <div class="card card-unstyled halfload searcher" id="commentsVideo">
            <div class="card-content grey-text">
                Video için <span data-name="count">0</span> yorum bulundu.
            </div>
            <ul class="collection json-clear" 
                 id="ajax-commentsVideo"
                 data-href="{{ route('youtube.comments', $document['_source']['id']) }}"
                 data-skip="0"
                 data-take="10"
                 data-include="string"
                 data-more-button="#ajax-commentsVideo-more_button"
                 data-callback="__commentsOrVideos"
                 data-method="post"
                 data-loader="#home-loader-2"
                 data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')@endcomponent
                </li>
                <li class="collection-item model hide">
                    <span data-name="title" class="red-text"></span>
                    <p data-name="text"></p>
                    <a href="#" data-name="created-at" class="timeago grey-text"></a>
                    <ul class="collection sub-collection hide" data-name="replies">
                        <li class="collection-item sub-model hide">
                            <span data-name="title" class="red-text"></span>
                            <p data-name="text"></p>
                            <a href="#" data-name="created-at" class="timeago grey-text"></a>
                        </li>
                    </ul>
                </li>
            </ul>

            <div id="home-loader-2" class="p-1 center-align">
                <a href="#" class="btn-flat waves-effect json" data-json-target="#ajax-commentsVideo">Yükle</a>
            </div>

            <a href="#"
               class="more hide json"
               id="ajax-commentsVideo-more_button"
               data-json-target="#ajax-commentsVideo">Daha Fazla</a>
        </div>
        <div class="card card-unstyled halfload searcher" id="commentsUser" style="display: none;">
            <div class="card-content grey-text">
                Kanal için <span data-name="count">0</span> yorum bulundu.
            </div>
            <ul class="collection json-clear" 
                 id="ajax-commentsUser"
                 data-href="{{ route('youtube.comments', $document['_source']['channel']['id']) }}"
                 data-skip="0"
                 data-take="10"
                 data-include="string"
                 data-more-button="#ajax-commentsUser-more_button"
                 data-callback="__commentsOrVideos"
                 data-method="post"
                 data-loader="#home-loader-1"
                 data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')@endcomponent
                </li>
                <li class="collection-item model hide">
                    <span data-name="title" class="red-text"></span>
                    <p data-name="text"></p>
                    <a href="#" data-name="created-at" class="timeago grey-text"></a>
                    <ul class="collection sub-collection hide" data-name="replies">
                        <li class="collection-item sub-model hide">
                            <span data-name="title" class="red-text"></span>
                            <p data-name="text"></p>
                            <a href="#" data-name="created-at" class="timeago grey-text"></a>
                        </li>
                    </ul>
                </li>
            </ul>

            @component('components.loader')
                @slot('color', 'teal')
                @slot('id', 'home-loader-1')
                @slot('class', 'card-loader-unstyled')
            @endcomponent

            <a href="#"
               class="more hide json"
               id="ajax-commentsUser-more_button"
               data-json-target="#ajax-commentsUser">Daha Fazla</a>
        </div>
        <div class="card card-unstyled halfload searcher" id="videosUser" style="display: none;">
            <div class="card-content grey-text">
                Kanal için <span data-name="count">0</span> video bulundu.
            </div>
            <ul class="collection json-clear" 
                 id="ajax-videosUser"
                 data-href="{{ route('youtube.videos', $document['_source']['channel']['id']) }}"
                 data-skip="0"
                 data-take="10"
                 data-include="string"
                 data-more-button="#ajax-videosUser-more_button"
                 data-callback="__commentsOrVideos"
                 data-method="post"
                 data-loader="#home-loader-3"
                 data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')@endcomponent
                </li>
                <li class="collection-item model model-video hide"></li>
            </ul>

            @component('components.loader')
                @slot('color', 'teal')
                @slot('id', 'home-loader-3')
                @slot('class', 'card-loader-unstyled')
            @endcomponent

            <a href="#"
               class="more hide json"
               id="ajax-videosUser-more_button"
               data-json-target="#ajax-videosUser">Daha Fazla</a>
        </div>
        <div class="card card-unstyled halfload searcher" id="videosSmilar" style="display: none;">
            <div class="card-content grey-text">
                Benzer <span data-name="count">0</span> video bulundu.
            </div>
            <ul class="collection json-clear" 
                 id="ajax-videosSmilar"
                 data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id ]) }}"
                 data-skip="0"
                 data-take="10"
                 data-include="string"
                 data-more-button="#ajax-videosSmilar-more_button"
                 data-callback="__commentsOrVideos"
                 data-method="post"
                 data-loader="#home-loader-4"
                 data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')@endcomponent
                </li>
                <li class="collection-item model model-video hide"></li>
            </ul>

            @component('components.loader')
                @slot('color', 'teal')
                @slot('id', 'home-loader-4')
                @slot('class', 'card-loader-unstyled')
            @endcomponent

            <a href="#"
               class="more hide json"
               id="ajax-videosSmilar-more_button"
               data-json-target="#ajax-videosSmilar">Daha Fazla</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title">{{ $document['_source']['title'] }}</span>
        </div>

        <iframe
            id="ytplayer"
            type="text/html"
            width="100%"
            height="360"
            src="http://www.youtube.com/embed/{{ $document['_source']['id'] }}?origin={{ url('/') }}"
            frameborder="0">
        </iframe>
        @isset ($document['_source']['description'])
            <div class="card-content">
                <div class="markdown">
                    {!! Term::linked($document['_source']['description']) !!}
                </div>
            </div>
        @endisset

        @include('content._inc.pin_bar', [
            'document' => $document
        ])
    </div>
@endsection

@push('local.scripts')
    function __commentsOrVideos(__, obj)
    {
        var ul = __;
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp')

                    if (item.hasClass('model-video'))
                    {
                        item.html(_video_(o))
                    }
                    else
                    {
                        item.find('[data-name=title]').html(o.channel.title)
                        item.find('[data-name=text]').html(o.text)
                        item.find('[data-name=created-at]')
                            .attr('data-time', o.created_at).html(o.created_at)
                            .attr('href', '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id)

                        if (o.replies.length)
                        {
                            var sub_collection = item.find('.sub-collection')
                                sub_collection.removeClass('hide')

                            $.each(o.replies, function(k, so) {
                                var sub_item = sub_collection.find('.sub-model').clone();
                                    sub_item.removeClass('sub-model hide').addClass('_tmp')

                                    sub_item.find('[data-name=title]').html(so.channel.title)
                                    sub_item.find('[data-name=text]').html(so.text)
                                    sub_item.find('[data-name=created-at]')
                                            .attr('data-time', so.created_at)
                                            .html(so.created_at)
                                            .attr('href', '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id)

                                    sub_item.appendTo(sub_collection)
                            })
                        }
                    }

                    item.mark(obj.words, {
                        'element': 'span',
                        'className': 'marked yellow black-text',
                        'accuracy': 'complementary'
                    }).appendTo(ul)
                })
            }

            __.closest('.card').find('[data-name=count]').html(obj.total)
        }
    }

    function __aggregation(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.closest('li.collection-item').addClass('hide')

            var collection = __.closest('ul.collection');
            var model = collection.children('li.collection-item[data-model]')

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var item = model.clone();
                        item.removeAttr('data-model').removeClass('hide')

                    var name = item.find('[data-name=name]').html(o.key);

                        item.find('[data-name=count]').html(o.doc_count)

                    item.appendTo(collection)
                })
            }
            else
            {
                collection.find('.nothing').removeClass('hide')
            }
        }
    }

    $('.sub-tabs').tabs({
        onShow: function(tab) {
            var loader = $('#ajax-' + tab.id);

            if ($('#' + tab.id).hasClass('halfload'))
            {
                if (!loader.hasClass('loaded'))
                {
                    loader.addClass('loaded')
                    vzAjax(loader)
                }
            }
        }
    })
@endpush

@section('dock')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title">{{ $document['_source']['channel']['title'] }}</span>
            <a href="https://www.youtube.com/channel/{{ $document['_source']['channel']['id'] }}" target="_blank" class="grey-text">{{ '@'.$document['_source']['channel']['id'] }}</a>
        </div>
    </div>

    @foreach (
        [
            'titles' => 'Kanal Adları',
            'category' => 'Kanalın İlgi Alanları'
        ] as $key => $model
    )
        <div class="card mb-1">
            <div class="card-content grey lighten-5">
                <span class="card-title card-title-small">{{ $model }}</span>
            </div>
            <ul class="collection aggregation-collection">
                <li class="collection-item hide" data-model>
                    <div class="d-flex justify-content-between">
                        <span class="align-self-center" data-name="name"></span>
                        <span class="grey align-self-center" data-name="count" style="padding: 0 .4rem;"></span>
                    </div>
                </li>
                <li class="collection-item center-align">
                    <a
                        href="#"
                        class="btn-floating waves-effect btn-flat json"
                        data-method="post"
                        data-callback="__aggregation"
                        data-type="{{ $key }}"
                        data-href="{{ route('video.aggregation', [ 'type' => $key, 'id' => $document['_source']['channel']['id'] ]) }}">
                        <i class="material-icons grey-text">refresh</i>
                    </a>
                </li>
                <li class="collection-item nothing hide">
                    @component('components.nothing')
                        @slot('size', 'small')
                    @endcomponent
                </li>
            </ul>
        </div>
    @endforeach
@endsection

@push('external.include.header')
    @if (config('app.env') == 'production')
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    @endif
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }
@endpush
