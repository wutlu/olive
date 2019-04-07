@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'dock' => true,
    'pin_group' => true,
    'wide' => true
])

@push('local.styles')
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
        max-height: 200px;
        overflow: auto;
    }
@endpush

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'tweet',
            'period' => 'daily',
            'title' => 'Günlük Tweet Paylaşımı',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'es_index_key' => date('Y.m', strtotime($document['_source']['created_at'])),
            'active' => true
        ],
        [
            'type' => 'tweet',
            'period' => 'hourly',
            'title' => 'Saatlik Tweet Paylaşımı',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'es_index_key' => date('Y.m', strtotime($document['_source']['created_at']))
        ]
    ]
])

@push('wildcard-top')
    <div class="card d-block">
        <div class="card-content d-flex justify-content-between">
            <span class="align-self-center">
                <span class="card-title">{{ $document['_source']['user']['name'] }}</span>
                <a href="https://twitter.com/intent/user?user_id={{ $document['_source']['user']['id'] }}" target="_blank" class="green-text">https://twitter.com/intent/user?user_id={{ $document['_source']['user']['id'] }}</a>
            </span>
            <img alt="Twitter" src="{{ asset('img/logos/twitter.svg') }}" class="align-self-center" style="width: 64px;" />
        </div>
    </div>
@endpush

@push('wildcard-bottom')
    <div class="card cyan darken-2">
        <div class="card-content">
            <p class="white-text">Tüm Tweetler ve ReTweetler kullanıcıya ait ve veritabanımıza alınmış periyodik verilerden oluşmaktadır.</p>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-transparent sub-tabs">
                <li class="tab">
                    <a href="#all_replies" class="active">Yanıtlar ({{ $data['reply']->data['count'] }})</a>
                </li>
                <li class="tab">
                    <a href="#all_quotes">Alıntılar ({{ $data['quote']->data['count'] }})</a>
                </li>
                <li class="tab">
                    <a href="#all_retweets">ReTweetler ({{ $data['retweet']->data['count'] }})</a>
                </li>
                <li class="tab">
                    <a href="#all_tweets">Tüm Tweetleri ({{ $data['total']->data['hits']['total'] }})</a>
                </li>
                <li class="tab">
                    <a href="#all_deleted_tweets">Silinen Tweetleri ({{ $data['deleted']->data['count'] }})</a>
                </li>
            </ul>
        </div>

        @foreach ([
            'all_tweets' => '',
            'all_retweets' => 'retweet',
            'all_quotes' => 'quote',
            'all_replies' => 'reply',
            'all_deleted_tweets' => 'deleted'
        ] as $key => $type)
            <div
                id="{{ $key }}"
                class="halfload white"
                @if ($key != 'all_replies')
                style="display: none;"
                @endif>
                <div class="collection json-clear loading"
                     id="loader-{{ $key }}"
                     data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => $type ]) }}"
                     data-method="post"
                     data-skip="0"
                     data-take="10"
                     data-more-button="#{{ $key }}-more_button"
                     data-callback="__all"
                     data-loader="#{{ $key }}-loader"
                     data-nothing>
                    <div class="collection-item nothing hide">
                        @component('components.nothing')
                            @slot('size', 'small')
                        @endcomponent
                    </div>
                    <div class="collection-item model hide"></div>
                </div>

                <div id="{{ $key }}-loader" class="p-1 center-align">
                    <a href="#" class="btn-flat waves-effect json" data-json-target="{{ '#loader-'.$key }}">Yükle</a>
                </div>

                <a href="#"
                   class="btn-small white grey-text more more-unstyled hide json"
                   id="{{ $key }}-more_button"
                   data-json-target="#loader-{{ $key }}">Daha Fazla</a>
            </div>
        @endforeach
    </div>
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <div class="d-flex">
                <img
                    alt="Avatar"
                    style="width: 48px; height: 48px;"
                    src="{{ $document['_source']['user']['image'] }}"
                    onerror="this.onerror=null;this.src='/img/no_image-twitter.svg';"
                    class="mr-1" />
                <div>
                    <span class="d-flex justify-content-between">
                        <span class="align-self-center">{{ $document['_source']['user']['name'] }}</span>
                        @isset ($document['_source']['user']['verified'])
                            <i class="material-icons cyan-text align-self-center ml-1">check</i>
                        @endisset
                    </span>
                    <a class="grey-text" href="https://twitter.com/{{ $document['_source']['user']['screen_name'] }}" target="_blank">{{ '@'.$document['_source']['user']['screen_name'] }}</a>
                </div>
                @isset ($document['_source']['user']['created_at'])
                    <time class="grey-text ml-auto" data-time="">{{ date('d.m.Y H:i', strtotime($document['_source']['user']['created_at'])) }}</time>
                @endisset
            </div>
        </div>

        @isset ($document['_source']['user']['description'])
            <div class="card-content markdown grey lighten-4 grey-text">{!! Term::tweet($document['_source']['user']['description']) !!}</div>
        @endisset

        @php
            $url = 'https://twitter.com/'.$document['_source']['user']['screen_name'].'/status/'.$document['_source']['id'];
        @endphp

        <div class="card-content">
            <div class="markdown">{!! Term::tweet($document['_source']['text']) !!}</div>
            <a class="green-text" href="{{ $url }}" target="_blank">{{ $url }}</a>
        </div>

        @isset ($document['_source']['entities']['medias'])
            <div class="d-flex flex-wrap mb-1">
                @foreach ($document['_source']['entities']['medias'] as $item)
                    @if ($item['media']['type'] == 'photo')
                        <img alt="" width="240" class="materialboxed" src="{{ $item['media']['media_url'] }}" />
                    @elseif ($item['media']['type'] == 'animated_gif' || $item['media']['type'] == 'video')
                        <video width="100%" height="240" controls>
                            <source src="{{ $item['media']['source_url'] }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video> 
                    @else
                        {{ $item['media']['type'] }}
                    @endif
                @endforeach
            </div>
        @endisset

        @isset ($data['external'])
            @php
                $external_url = 'https://twitter.com/'.$data['external']['_source']['user']['screen_name'].'/status/'.$data['external']['_source']['id'];
            @endphp
            <ul class="collapsible mb-1">
                <li>
                    <div class="collapsible-header orange lighten-5 d-block">
                        <div class="d-flex justify-content-between">
                            <span class="align-self-center">
                                <span class="red-text">{{ '@'.$data['external']['_source']['user']['screen_name'] }}</span>
                                <span class="grey-text">{{ $data['external']['_source']['user']['name'] }}</span>
                            </span>
                            <a href="{{ route('content', [
                                'es_index' => $data['external']['_index'],
                                'es_type' => $data['external']['_type'],
                                'es_id' => $data['external']['_id']
                            ]) }}" class="btn-flat waves-effect align-self-center center-align">Kaynak</a>
                        </div>
                    </div>
                    <div class="collapsible-body">
                        <div class="p-1">
                            <p class="grey-text mb-1">{{ date('d.m.Y H:i:s', strtotime($data['external']['_source']['created_at'])) }}</p>
                            <div class="markdown mb-1">{!! Term::tweet($data['external']['_source']['text']) !!}</div>
                            <a class="green-text" href="{{ $external_url }}" target="_blank">{{ $external_url }}</a>
                        </div>
                    </div>
                </li>
            </ul>
        @endisset

        @include('content._inc.sentiment_bar', [
            'pos' => $document['_source']['sentiment']['pos'],
            'neg' => $document['_source']['sentiment']['neg'],
            'neu' => $document['_source']['sentiment']['neu']
        ])
    </div>

    <div class="card">
        <div class="card-content">
            <span class="card-title">Profil Değerleri</span>
        </div>
        <div class="card-content"> 
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
            @endisset
        </div>
    </div>
@endsection

@section('dock')
    @include('content._inc.sentiment', [
        'neu' => $data['total']->data['aggregations']['neutral']['value'],
        'pos' => $data['total']->data['aggregations']['positive']['value'],
        'neg' => $data['total']->data['aggregations']['negative']['value'],
        'alert' => 'İlgili kullanıcıdan toplam '.$data['total']->data['hits']['total'].' tweet alındı. Sayfadaki istatistik verileri, alınan tweetler üzerinden gerçekleştirilmiştir.'
    ])

    @foreach (
        [
            'names' => 'Adları',
            'screen_names' => 'Kullanıcı Adları',
            'platforms' => 'Platform Geçmişi',
            'langs' => 'Dil Geçmişi',
            'mention_out' => 'Andığı Kişiler',
            'mention_in' => 'Anıldığı Kişiler',
            'hashtags' => 'Hashtag Geçmişi',
            'places' => 'Konum Geçmişi',
            'urls' => 'Bağlantı Geçmişi',
        ] as $key => $model
    )
        <div class="card mb-1">
            <div class="card-content d-flex justify-content-between cyan darken-2">
                <span class="card-title card-title-small white-text align-self-center">{{ $model }}</span>
                <a
                    href="#"
                    class="btn-floating waves-effect btn-flat json loading"
                    data-method="post"
                    data-callback="__aggregation"
                    data-type="{{ $key }}"
                    data-href="{{ route('tweet.aggregation', [ 'type' => $key, 'id' => $document['_source']['user']['id'] ]) }}">
                    <i class="material-icons white-text">keyboard_arrow_down</i>
                </a>
            </div>
            <ul class="collection aggregation-collection">
                <li class="collection-item hide" data-model>
                    <div class="d-flex justify-content-between">
                        <span class="align-self-center" data-name="name"></span>
                        <span class="grey align-self-center" data-name="count" style="padding: 0 .4rem;"></span>
                    </div>
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

@push('local.scripts')
    $('.materialboxed').materialbox()

    function __aggregation(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.addClass('hide')

            var collection = __.closest('.card').find('ul.collection');
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
                            'href': '{{ route('search.dashboard') }}?q={{ '@'.$document['_source']['user']['screen_name'] }} ' + o.key,
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'mention_in')
                    {
                        name.html($('<a />', {
                            'html': '@' + o.key,
                            'href': '{{ route('search.dashboard') }}?q=@' + o.key + ' {{ $document['_source']['user']['screen_name'] }}',
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'hashtags')
                    {
                        name.html($('<a />', {
                            'html': '#' + o.key,
                            'href': '{{ route('search.dashboard') }}?q={{ '@'.$document['_source']['user']['screen_name'] }} ' + encodeURIComponent(o.key),
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
                            'href': '{{ route('search.dashboard') }}?q=@' + o.key,
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
                    var item = item_model.clone().html(_tweet_(o));
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)
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
