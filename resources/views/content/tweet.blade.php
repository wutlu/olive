@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@push('local.styles')
    [data-name=author] {
        font-size: 18px;
    }

    table > thead > tr > th { padding: .2rem .4rem; }
    table > tbody > tr > td { padding: .2rem .4rem; }

    .stat-chart {
        line-height: 1px;
        height: 64px;
    }

    .stat-chart canvas {
        width: 100%;
    }

    .aggregation-collection {
        height: 200px;
        overflow: auto;
    }
@endpush

@include('content._inc.histogram', [
    'index' => $es->index,
    'type' => $es->type,
    'id' => $document['_source']['id'],
    'tab_title' => 'Kullanıcının Tweet Grafiği'
])

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('content')
    <div class="row">
        <div class="col m6 s12">
            <div class="card">
                <div class="card-content">
                    <div class="d-flex">
                        <img class="mr-1" style="width: 64px; height: 64px;" src="{{ str_replace('_normal', '', $document['_source']['user']['image']) }}" alt="{{ $document['_source']['user']['name'] }}" />
                        <span>
                            <span class="d-flex justify-content-between">
                                <a class="card-title align-self-center" href="https://twitter.com/{{ $document['_source']['user']['screen_name'] }}" target="_blank">{{ $document['_source']['user']['name'] }}</a>
                                @isset ($document['_source']['user']['verified'])
                                    <i class="material-icons blue-text align-self-center">check</i>
                                @endisset
                            </span>
                            @isset ($document['_source']['user']['description'])
                                <div class="markdown">{{ $document['_source']['user']['description'] }}</div>
                            @endisset
                        </span>
                    </div>
                </div>
                @isset ($document['_source']['user']['created_at'])
                    <div class="card-action d-flex justify-content-end">
                        <span class="right-align">
                            <small class="d-block grey-text">OLUŞTURULDU</small>
                            <span>{{ date('d.m.Y H:i', strtotime($document['_source']['user']['created_at'])) }}</span>
                        </span>
                    </div>
                @endisset
            </div>
            <div class="card">
                <div class="card-content">
                    <div class="markdown">{!! Term::tweet($document['_source']['text']) !!}</div>
                </div>
                @include('content._inc.sentiment_bar', [
                    'pos' => $document['_source']['sentiment']['pos'],
                    'neg' => $document['_source']['sentiment']['neg'],
                    'neu' => $document['_source']['sentiment']['neu']
                ])
            </div>
        </div>
        <div class="col m6 s12">
            <div class="card">
                @include('content._inc.sentiment', [
                    'total' => $data['total']->data['count'],
                    'pos' => $data['pos']->data['count'],
                    'neg' => $data['neg']->data['count'],
                    'alert' => 'İlgili kullanıcıdan toplam '.$data['total']->data['count'].' tweet alındı. Sayfadaki istatistik verileri, alınan tweetler üzerinden gerçekleştirilmiştir.'
                ])
            </div>
        </div>
    </div>
    <div class="row">
        @foreach (
            [
                'names' => 'Adlar',
                'screen_names' => 'Kullanıcı Adları',
                'platforms' => 'Platformlar',
                'langs' => 'Sık Kullandığı Diller',
                'mention_out' => 'Sık Andığı Kişiler',
                'mention_in' => 'Sık Anıldığı Kişiler',
                'hashtags' => 'Sık Kullandığı Hashtagler',
                'places' => 'Sık Girdiği Konumlar',
                'urls' => 'Sık Kullandığı Bağlantılar',
            ] as $key => $model
        )
        <div class="col l4 m6 s12">
            <div class="card">
                <div class="card-content cyan darken-2">
                    <span class="card-title card-title-small white-text">{{ $model }}</span>
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
                            data-href="{{ route('tweet.aggregation', [ 'type' => $key, 'id' => $document['_source']['user']['id'] ]) }}">
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
        </div>
        @endforeach
    </div>

    <div class="card cyan darken-2">
        <div class="card-content">
            <p class="white-text">Tüm Tweetler ve ReTweetler kullanıcıya ait ve veritabanımıza alınmış periyodik verilerden oluşmaktadır.</p>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-transparent sub-tabs">
                <li class="tab">
                    <a href="#stats" class="active">Profil Değerleri</a>
                </li>
                <li class="tab">
                    <a href="#all_tweets">Tüm Tweetler ({{ $data['total']->data['count'] }})</a>
                </li>
                <li class="tab">
                    <a href="#all_retweets">Tweetin ReTweetleri ({{ $data['retweet']->data['count'] }})</a>
                </li>
            </ul>
        </div>
        <div class="card-content white" id="stats">
            @isset ($data['stats'])
                @foreach (
                    [
                        '_followers' => 'Takipçi Performans Grafiği',
                        '_friends' => 'Takip Performans Grafiği',
                        '_statuses' => 'Tweet Performans Grafiği',
                        '_listed' => 'Liste Performans Grafiği',
                        '_favourites' => 'Favori Performans Grafiği',
                    ] as $key => $name
                )
                    @if (count($data['statistics']['diff'][$key]) >= 2)
                        <div id="{{ $key }}" class="stat-charts hide">
                            @push('local.scripts')
                                new Chart($('#{{ $key }}-chart'), {
                                    type: 'line',
                                    data: {
                                        labels: {{ json_encode($data['statistics']['diff'][$key]) }},
                                        datasets: [{
                                            backgroundColor: 'transparent',
                                            borderColor: '#00796b',
                                            data: {{ json_encode($data['statistics']['diff'][$key]) }},
                                            tension: 0.1,
                                            borderWidth: 1,
                                            radius: 0
                                        }]
                                    },
                                    options: {
                                        legend: { display: false },
                                        scales: {
                                            xAxes: [{ display: false }],
                                            yAxes: [{ display: false }]
                                        },
                                        tooltips: {
                                             enabled: false
                                        },
                                        maintainAspectRatio: false
                                    }
                                })
                            @endpush
                            <span class="teal-text text-darken-2">{{ $name }}</span>
                            <div class="stat-chart">
                                <canvas id="{{ $key }}-chart" height="64"></canvas>
                            </div>
                        </div>
                    @endif
                @endforeach
                <table>
                    <thead>
                        <tr>
                            <th class="grey-text text-lighten-2">Tarih</th>
                            <th class="right-align">
                                <a href="#" class="d-flex justify-content-end" data-chart="_followers">
                                    <i class="material-icons">show_chart</i>
                                    Takipçi
                                </a>
                            </th>
                            <th class="right-align">
                                <a href="#" class="d-flex justify-content-end" data-chart="_friends">
                                    <i class="material-icons">show_chart</i>
                                    Takip
                                </a>
                            </th>
                            <th class="right-align">
                                <a href="#" class="d-flex justify-content-end" data-chart="_statuses">
                                    <i class="material-icons">show_chart</i>
                                    Tweet
                                </a>
                            </th>
                            <th class="right-align">
                                <a href="#" class="d-flex justify-content-end" data-chart="_listed">
                                    <i class="material-icons">show_chart</i>
                                    Liste
                                </a>
                            </th>
                            <th class="right-align">
                                <a href="#" class="d-flex justify-content-end" data-chart="_favourites">
                                    <i class="material-icons">show_chart</i>
                                    Favori
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['stats'] as $item)
                            <tr>
                                <td class="grey-text">{{ $item['created_at'] }}</td>
                                <td class="right-align {{ $item['diff']['followers'] }}-text">{{ number_format($item['followers']) }}</td>
                                <td class="right-align {{ $item['diff']['friends'] }}-text">{{ number_format($item['friends']) }}</td>
                                <td class="right-align {{ $item['diff']['statuses'] }}-text">{{ number_format($item['statuses']) }}</td>
                                <td class="right-align {{ $item['diff']['listed'] }}-text">{{ number_format($item['listed']) }}</td>
                                <td class="right-align {{ $item['diff']['favourites'] }}-text">{{ number_format($item['favourites']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                test
            @endisset
        </div>
        <div id="all_tweets" class="halfload white" style="display: none;">
            <div class="collection json-clear mb-0"
                 id="loader-all_tweets"
                 data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id ]) }}"
                 data-method="post"
                 data-skip="0"
                 data-take="20"
                 data-more-button="#all_tweets-more_button"
                 data-callback="__all"
                 data-loader="#home-loader"
                 data-nothing>
                <div class="collection-item nothing hide">
                    @component('components.nothing')
                        @slot('size', 'small')
                    @endcomponent
                </div>
                <div class="collection-item z-depth-1 model hide">
                    <span href="#" class="d-table red-text" data-name="author"></span>
                    <time class="d-table grey-text" data-name="created-at"></time>
                    <a href="#" class="d-table grey-text text-darken-2" data-name="text"></a>
                    <a href="#" class="d-table green-text" data-name="url" target="_blank"></a>
                </div>
            </div>

            @component('components.loader')
                @slot('color', 'cyan')
                @slot('class', 'card-loader-unstyled')
                @slot('id', 'home-loader')
            @endcomponent

            <div class="center-align mt-1">
                <button class="btn-flat waves-effect hide json"
                        id="all_tweets-more_button"
                        type="button"
                        data-json-target="#loader-all_tweets">Daha Fazla</button>
            </div>
        </div>
        <div id="all_retweets" class="halfload white" style="display: none;">
            <div class="collection json-clear mb-0"
                 id="loader-all_retweets"
                 data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'retweet' ]) }}"
                 data-method="post"
                 data-skip="0"
                 data-take="20"
                 data-more-button="#all_retweets-more_button"
                 data-callback="__all"
                 data-loader="#home-loader-rt"
                 data-nothing>
                <div class="collection-item nothing hide">
                    @component('components.nothing')
                        @slot('size', 'small')
                    @endcomponent
                </div>
                <div class="collection-item z-depth-1 model hide">
                    <span href="#" class="d-table red-text" data-name="author"></span>
                    <time class="d-table grey-text" data-name="created-at"></time>
                    <a href="#" class="d-table grey-text text-darken-2" data-name="text"></a>
                    <a href="#" class="d-table green-text" data-name="url" target="_blank"></a>
                </div>
            </div>

            @component('components.loader')
                @slot('color', 'cyan')
                @slot('class', 'card-loader-unstyled')
                @slot('id', 'home-loader-rt')
            @endcomponent

            <div class="center-align mt-1">
                <button class="btn-flat waves-effect hide json"
                        id="all_retweets-more_button"
                        type="button"
                        data-json-target="#loader-all_retweets">Daha Fazla</button>
            </div>
        </div>
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
                            'href': '{{ route('search.dashboard') }}?q=' + encodeURIComponent('user.screen_name:' + o.key),
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'mention_in')
                    {
                        name.html($('<a />', {
                            'html': '@' + o.key,
                            'href': '{{ route('search.dashboard') }}?q=' + encodeURIComponent('user.screen_name:' + o.key),
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
                        item.find('[data-name=text]').attr('href', '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id)
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

    $(document).on('click', '[data-chart]', function() {
        var __ = $(this);

        $('.stat-charts').addClass('hide')
        $('#' + __.data('chart')).removeClass('hide')
    })
@endpush
