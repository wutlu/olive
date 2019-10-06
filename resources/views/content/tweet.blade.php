@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'dock' => true,
    'pin_group' => true,
    'wide' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ],
    'footer_hide' => true
])

@push('local.styles')
    table > thead > tr > th { padding: .2rem .4rem; }
    table > tbody > tr > td { padding: .2rem .4rem; }

    .stat-chart {
        line-height: 1px;
        height: 64px;
        width: 100%;
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
            <span class="d-flex align-self-center">
                <img
                    alt="Avatar"
                    style="width: 72px; height: 72px;"
                    src="{{ $document['_source']['user']['image'] }}"
                    onerror="this.onerror=null;this.src='/img/no_image-twitter.svg';"
                    class="mr-1 align-self-center" />
                <div class="align-self-center">
                    <span class="card-title d-flex">
                        <span class="align-self-center">{{ $document['_source']['user']['name'] }}</span>

                        @isset ($document['_source']['user']['verified'])
                            <i class="material-icons teal-text align-self-center ml-1">check</i>
                        @endisset
                    </span>
                    <span class="grey-text d-table">{{ '@'.$document['_source']['user']['screen_name'] }}</span>
                    <a href="https://twitter.com/intent/user?user_id={{ $document['_source']['user']['id'] }}" target="_blank" class="green-text">https://twitter.com/intent/user?user_id={{ $document['_source']['user']['id'] }}</a>
                </div>
            </span>
            <div class="d-flex align-self-center flex-column">
                <img alt="Twitter" src="{{ asset('img/logos/twitter.svg') }}" class="ml-auto" style="width: 64px;" />

                @isset ($document['_source']['user']['created_at'])
                    <time class="grey-text" data-time="">Üyelik Tarihi: {{ date('d.m.Y H:i', strtotime($document['_source']['user']['created_at'])) }}</time>
                @endisset
            </div>
        </div>

        <div class="card-content d-flex flex-wrap grey lighten-4 mb-1">
            <div class="p-1">
                <small class="d-table grey-text">Tweet</small>
                <span class="d-table">{{ number_format($document['_source']['user']['counts']['statuses']) }}</span>
            </div>
            <div class="p-1">
                <small class="d-table grey-text">Takip</small>
                <span class="d-table">{{ number_format($document['_source']['user']['counts']['friends']) }}</span>
            </div>
            <div class="p-1">
                <small class="d-table grey-text">Takipçi</small>
                <span class="d-table">{{ number_format($document['_source']['user']['counts']['followers']) }}</span>
            </div>
            <div class="p-1">
                <small class="d-table grey-text">Liste</small>
                <span class="d-table">{{ number_format($document['_source']['user']['counts']['listed']) }}</span>
            </div>
            <div class="p-1">
                <small class="d-table grey-text">Favori</small>
                <span class="d-table">{{ number_format($document['_source']['user']['counts']['favourites']) }}</span>
            </div>
            @isset ($document['_source']['user']['description'])
                <div class="p-1 markdown">
                    <small class="d-table grey-text">Profil Açıklaması</small>
                    {!! Term::tweet($document['_source']['user']['description']) !!}
                </div>
            @endisset
        </div>

        @isset ($document['_source']['entities']['medias'])
            <div class="d-flex flex-wrap mx-auto" style="max-width: 600px;">
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

        @php
            $url = 'https://twitter.com/'.$document['_source']['user']['screen_name'].'/status/'.$document['_source']['id'];
        @endphp

        <div class="d-table p-1 mx-auto" style="max-width: 600px;">
            <small class="d-table grey-text">Tweet</small>
            <div class="markdown">
                {!! Term::tweet($document['_source']['text']) !!}
            </div>

            @isset ($document['_source']['counts']['favorite'])
                <div class="d-flex flex-wrap">
                    <div class="p-1">
                        <small class="d-table grey-text">ReTweet</small>
                        <span class="d-table">{{ number_format($document['_source']['counts']['retweet']) }}</span>
                    </div>
                    <div class="p-1">
                        <small class="d-table grey-text">Cevap</small>
                        <span class="d-table">{{ number_format($document['_source']['counts']['reply']) }}</span>
                    </div>
                    <div class="p-1">
                        <small class="d-table grey-text">Alıntı</small>
                        <span class="d-table">{{ number_format($document['_source']['counts']['quote']) }}</span>
                    </div>
                    <div class="p-1">
                        <small class="d-table grey-text">Favori</small>
                        <span class="d-table">{{ number_format($document['_source']['counts']['favorite']) }}</span>
                    </div>
                </div>
            @endisset

            <a class="green-text" href="{{ $url }}" target="_blank">{{ $url }}</a>
        </div>

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

        @include('content._inc.pin_bar', [
            'document' => $document
        ])
    </div>
@endpush

@section('panel-icon', 'format_quote')
@section('panel')
    <div class="collection collection-unstyled">
        <div class="collection-item pb-0">
            <small class="blue-grey-text">Tweet</small>
        </div>

        <a data-tweets="tweet_replies" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'tweet_replies' ]) }}" class="collection-item" href="#" data-alias="Tweet">Yanıtlar</a>
        <a data-tweets="tweet_quotes" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'tweet_quotes' ]) }}" class="collection-item" href="#" data-alias="Tweet">Alıntılar</a>
        <a data-tweets="tweet_retweets" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'tweet_retweets' ]) }}" class="collection-item" href="#" data-alias="Tweet">ReTweetler</a>
        <a data-tweets="tweet_favorites" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'tweet_favorites' ]) }}" class="collection-item" href="#" data-alias="Tweet">En Çok Favlanan</a>
        <a data-tweets="tweet_deleted" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'tweet_deleted' ]) }}" class="collection-item" href="#" data-alias="Tweet">Silinen Etkileşimler</a>

        <div class="collection-item pb-0">
            <small class="blue-grey-text">Kullanıcı</small>
        </div>

        <a data-tweets="user_tweets" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_tweets' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Tweetler</a>
        <a data-tweets="user_replies" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_replies' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Verdiği Yanıtlar</a>
        <a data-tweets="user_quotes" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_quotes' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Yaptığı Alıntılar</a>
        <a data-tweets="user_retweets" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_retweets' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Yaptığı ReTweetler</a>
        <a data-tweets="user_favorites" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_favorites' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Fav Sayısına Göre Tweetler</a>
        <a data-tweets="user_favorites" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_quotes_desc' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Alıntı Sayısına Göre Tweetler</a>
        <a data-tweets="user_favorites" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_replies_desc' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Yanıt Sayısına Tweetler</a>
        <a data-tweets="user_favorites" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_retweets_desc' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">ReTweet Sayısına Göre Tweetler</a>
        <a data-tweets="user_deleted" data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id, 'type' => 'user_deleted' ]) }}" class="collection-item" href="#" data-alias="Kullanıcı">Silinen Tweetler</a>
    </div>
@endsection

@section('content')
    @if (@$data['details'])
        @push('local.scripts')
            var options = {
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: true,
                        tools: {
                            download: '<i class="material-icons">save</i>'
                        }
                    }
                },
                colors: [ '#77B6EA', '#f44336', '#009688', '#cddc39', '#9c27b0' ],
                dataLabels: { enabled: true },
                series: [
                    {
                        name: 'Tweet',
                        data: {!! json_encode($data['details']['statuses']) !!}
                    },
                    {
                        name: 'Takipçi',
                        data: {!! json_encode($data['details']['friends']) !!}
                    },
                    {
                        name: 'Takip',
                        data: {!! json_encode($data['details']['followers']) !!}
                    },
                    {
                        name: 'Liste',
                        data: {!! json_encode($data['details']['lists']) !!}
                    },
                    {
                        name: 'Favori',
                        data: {!! json_encode($data['details']['favorites']) !!}
                    }
                ],
                grid: {
                    borderColor: '#f0f0f0',
                    row: { colors: [ '#f0f0f0' ], opacity: 0.2 }
                },
                markers: { size: 6 },
                yaxis: {
                    min: 0
                },
                xaxis: {
                    categories: {!! json_encode($data['details']['days']) !!}
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                }
            }

            var chart = new ApexCharts(document.querySelector('#chart'), options);
                chart.render()
        @endpush

        <div class="card mb-1">
            <div class="card-content">
                <span class="card-title">Profil Değerleri</span>
            </div>
            <div id="chart"></div>
        </div>
    @endif
    <div class="card">
        <div class="card-content">
            <span class="card-title">
                <span data-name="tweets-title">-</span>
                (<span data-name="tweets-total">0</span>)
            </span>
        </div>
        <div
            id="tweets"
            class="collection collection-unstyled loading json-clear"
            data-href="#"
            data-method="post"
            data-skip="0"
            data-take="10"
            data-more-button="#tweets-more_button"
            data-callback="__all"
            data-loader="#tweets-loader"
            data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </div>
            <div class="collection-item model hide"></div>
        </div>
        @component('components.loader')
            @slot('color', 'teal')
            @slot('id', 'tweets-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
   </div>
    <a href="#"
       class="more hide json"
       id="tweets-more_button"
       data-json-target="#tweets">Daha Fazla</a>
@endsection

@section('dock')
    @foreach (
        [
            'platforms' => 'Platform Geçmişi',
            'langs' => 'Dil Geçmişi',
            'mention_out' => 'Andığı Kişiler',
            //'mention_in' => 'Anıldığı Kişiler',
            'hashtags' => 'Hashtag Geçmişi',
            'places' => 'Konum Geçmişi'
        ] as $key => $model
    )
        <div class="card mb-1 p-0">
            <div class="card-content">
                <a
                    href="#"
                    class="card-title json loading"
                    data-method="post"
                    data-callback="__aggregation"
                    data-type="{{ $key }}"
                    data-href="{{ route('tweet.aggregation', [ 'type' => $key, 'id' => $document['_source']['user']['id'] ]) }}">
                    {{ $model }}
                </a>
            </div>
            <ul class="collection collection-unstyled aggregation-collection hide">
                <li class="collection-item hide" data-model>
                    <div class="d-flex justify-content-between">
                        <span class="align-self-center" data-name="name"></span>
                        <span class="grey align-self-center" data-name="count" style="padding: 0 .4rem;"></span>
                    </div>
                </li>
            </ul>
        </div>
    @endforeach
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/apex.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush
@push('local.scripts')
    $('.materialboxed').materialbox()

    function __aggregation(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.removeClass('json')

            var collection = __.closest('.card').find('ul.collection');
            var model = collection.children('li.collection-item[data-model]')

            if (obj.data.length)
            {
                collection.removeClass('hide')

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
                __.addClass('white-text').parent('.card-content').addClass('red')
            }
        }
    }

    $(document).on('click', '[data-tweets]', function() {
        var __ = $(this);

        $('[data-name=tweets-title]').html(__.data('alias') + ': ' + __.html())

        var search = $('#tweets');
            search.data('href', __.data('href'))
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })

    $(document).ready(function() {
        $('[data-tweets=tweet_replies]').click()
    })

    function __all(__, obj)
    {
        var ul = $('#tweets');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (__.data('skip') <= __.data('take'))
            {
                $('[data-name=tweets-total]').html(obj.total)
            }

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
@endpush
