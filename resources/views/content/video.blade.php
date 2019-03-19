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
    <div class="card red">
        <div class="card-content d-flex justify-content-between">
            <span class="align-self-center">
                <span class="card-title white-text">{{ $document['_source']['title'] }}</span>
                <a href="https://www.youtube.com/watch?v={{ $document['_source']['id'] }}" target="_blank" class="red-text text-darken-4">https://www.youtube.com/watch?v={{ $document['_source']['id'] }}</a>
            </span>
            <img alt="YouTube" src="{{ asset('img/logos/youtube.svg') }}" class="white align-self-center z-depth-1" style="width: 64px;" />
        </div>
    </div>
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title">{{ $document['_source']['channel']['title'] }}</span>
            <a href="https://www.youtube.com/channel/{{ $document['_source']['channel']['id'] }}" target="_blank" class="grey-text">{{ '@'.$document['_source']['channel']['id'] }}</a>
        </div>
        <iframe
            id="ytplayer"
            type="text/html"
            width="100%"
            height="360"
            src="http://www.youtube.com/embed/{{ $document['_source']['id'] }}?origin={{ config('app.url') }}"
            frameborder="0">
        </iframe>
        @isset ($document['_source']['description'])
            <div class="card-content">
                {!! Term::linked($document['_source']['description']) !!}
            </div>
        @endisset
    </div>

    <div class="card cyan darken-2">
        <div class="card-content">
            <p class="white-text">Tüm Tweetler ve ReTweetler kullanıcıya ait ve veritabanımıza alınmış periyodik verilerden oluşmaktadır.</p>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-transparent sub-tabs">
                <li class="tab">
                    <a href="#comments" class="active">Videoya Yapılan Yorumlar</a>
                </li>
                <li class="tab">
                    <a href="#comments_channel">Kanalın Tüm Yorumları</a>
                </li>
            </ul>
        </div>
        @foreach ([
            'comments' => 'comment-video',
            'comments_channel' => 'comment-channel'
        ] as $key => $type)
            <div id="{{ $key }}" class="halfload white" style="@if ($key != 'comments'){{ 'display: none;' }}@endif">
                <div class="collection json-clear @if ($key != 'comments'){{ 'load' }}@endif"
                     id="loader-{{ $key }}"
                     data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => $type ]) }}"
                     data-method="post"
                     data-skip="0"
                     data-take="20"
                     data-more-button="#{{ $key }}-more_button"
                     data-callback="__all"
                     data-loader="#{{ $key }}-loader"
                     data-nothing>
                    <div class="collection-item nothing hide">
                        @component('components.nothing')
                            @slot('size', 'small')
                        @endcomponent
                    </div>
                    <div class="collection-item z-depth-1 model hide">
                        <span class="d-table grey-text text-darken-2" data-name="author"></span>
                        <a href="#" target="_blank" class="d-table grey-text text-darken-2" data-name="screen-name"></a>
                        <time data-time="" class="d-table grey-text" data-name="created-at"></time>
                        <span class="d-block" data-name="text"></span>
                        <a href="#" class="d-table green-text" data-name="url" target="_blank"></a>
                    </div>
                </div>

                @component('components.loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                    @slot('id', $key.'-loader')
                @endcomponent

                <div class="center-align mt-1">
                    <button class="btn-flat waves-effect hide json"
                            id="{{ $key }}-more_button"
                            type="button"
                            data-json-target="#loader-{{ $key }}">Daha Fazla</button>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('local.scripts')
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

                    var name = item.find('[data-name=name]');

                    if (__.attr('data-type') == 'mention_out')
                    {
                        name.html($('<a />', {
                            'html': '@' + o.key,
                            'href': 'https://twitter.com/' + o.key,
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'mention_in')
                    {
                        name.html($('<a />', {
                            'html': '@' + o.key,
                            'href': 'https://twitter.com/' + o.key,
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'hashtags')
                    {
                        name.html($('<a />', {
                            'html': '#' + o.key,
                            'href': '{{ route('search.dashboard') }}?q=' + encodeURIComponent(o.key),
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'urls')
                    {
                        name.html($('<a />', {
                            'html': o.key,
                            'href': o.key,
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'screen_names')
                    {
                        name.html($('<a />', {
                            'html': '@' + o.key,
                            'href': 'https://twitter.com/' + o.key,
                            'target': '_blank'
                        }))
                    }
                    else
                    {
                        name.html(o.key)
                    }

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

    function __all(__, obj)
    {
        var ul = $('#' + __.attr('id'));
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('[data-name=author]').html(o._source.user.name)
                        item.find('[data-name=screen-name]').html('@' + o._source.user.screen_name).attr('href', 'https://twitter.com/' + o._source.user.screen_name)
                        item.find('[data-name=text]').html(o._source.text)
                        item.find('[data-name=url]').html('https://twitter.com/' + o._source.user.screen_name + '/status/' + o._source.id).attr('href', 'https://twitter.com/' + o._source.user.screen_name + '/status/' + o._source.id)
                        item.find('[data-name=created-at]').html(o._source.created_at)

                        item.appendTo(ul)
                })
            }
        }
    }

    $('.sub-tabs').tabs({
        onShow: function(tab) {
            var loader = $('#loader-' + tab.id);

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
    @foreach (
        [
            'titles' => 'Kanal Adları'
        ] as $key => $model
    )
        <div class="card">
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
