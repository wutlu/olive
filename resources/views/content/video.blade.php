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
            'title' => '<i class="material-icons align-self-center mr-1">people</i> Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'active' => true,
            'info' => 'İlgili videoya yapılan yorumların günlere dağılım grafiği.'
        ],
        [
            'type' => 'video-comments',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">people</i> Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'info' => 'İlgili videoya yapılan yorumların saatlere dağılım grafiği.'
        ],

        [
            'type' => 'video-by-video',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Yükleme Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_3',
            'info' => 'İlgili videoyu yükleyen kullanıcının yüklemelerinin günlere dağılımı.'
        ],
        [
            'type' => 'video-by-video',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Yükleme Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_4',
            'info' => 'İlgili videoyu yükleyen kullanıcının yüklemelerinin saatlere dağılımı.'
        ],

        [
            'type' => 'comment-by-video',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_5',
            'info' => 'İlgili videoyu yükleyen kullanıcının yaptığı yorumların günlere dağılımı.'
        ],
        [
            'type' => 'comment-by-video',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Yorum Grafiği',
            'id' => $document['_id'],
            'unique_id' => 'tab_6',
            'info' => 'İlgili videoyu yükleyen kullanıcının yaptığı yorumların saatlere dağılımı.'
        ],
    ]
])

@section('content')
    <div class="card">
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
