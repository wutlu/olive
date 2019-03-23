@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'dock' => true
])

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'video-comments',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">people</i> Video Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'active' => true,
            'info' => 'İlgili videoya yapılan yorumların günlere dağılım grafiği.'
        ],
        [
            'type' => 'video-comments',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">people</i> Video Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'info' => 'İlgili videoya yapılan yorumların saatlere dağılım grafiği.'
        ],

        [
            'type' => 'video-by-video',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Kanal Yükleme Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_3',
            'info' => 'İlgili videoyu yükleyen kullanıcının yüklemelerinin günlere dağılımı.'
        ],
        [
            'type' => 'video-by-video',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Kanal Yükleme Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_4',
            'info' => 'İlgili videoyu yükleyen kullanıcının yüklemelerinin saatlere dağılımı.'
        ],

        [
            'type' => 'comment-by-video',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Kanal Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_5',
            'info' => 'İlgili videoyu yükleyen kullanıcının yaptığı yorumların günlere dağılımı.'
        ],
        [
            'type' => 'comment-by-video',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Kanal Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_6',
            'info' => 'İlgili videoyu yükleyen kullanıcının yaptığı yorumların saatlere dağılımı.'
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

@section('content')
    <div class="card mb-1">
        <!--<iframe
            id="ytplayer"
            type="text/html"
            width="100%"
            height="360"
            src="http://www.youtube.com/embed/{{ $document['_source']['id'] }}?origin={{ config('app.url') }}"
            frameborder="0">
        </iframe>-->
        @isset ($document['_source']['description'])
            <div class="card-content">
                <div class="markdown">
                    {!! Term::linked($document['_source']['description']) !!}
                </div>
            </div>
        @endisset
    </div>

    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Takip Edilen Kelimeler</span>
            <span data-name="count" class="grey-text text-darken-2">0</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#users"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="users"
             data-href="{{ route('admin.twitter.stream.keywords') }}"
             data-skip="0"
             data-take="10"
             data-include="string"
             data-more-button="#users-more_button"
             data-callback="__keywords"
             data-method="post"
             data-loader="#home-loader-2"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item model hide waves-effect justify-content-between"
                data-trigger="textarea">
                <span class="align-self-center">
                    <p data-name="keyword"></p>
                    <p data-name="reason"></p>
                </span>
                <span class="d-flex flex-column align-items-end">
                    <span data-name="follower" class="badge"></span>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
            </a>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'home-loader-2')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="users-more_button"
                type="button"
                data-json-target="#users">Daha Fazla</button>
    </div>
@endsection

@push('local.scripts')
    function __keywords(__, obj)
    {
        var ul = $('#users');
        var item_model = ul.children('.model');

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
                        item.find('[data-name=follower]').html(o.organisation.name)
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '-').removeClass('green-text red-text').addClass(o.reason ? 'red-text' : 'green-text')

                        item.appendTo(ul)
                })
            }

            $('[data-name=count]').html(obj.total)
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
            'titles' => 'Kanal Adları'
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

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush
