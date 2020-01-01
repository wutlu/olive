@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'archive_dock' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ],
    'footer_hide' => true,
    'dock' => true,
    'report_menu' => true
])

@section('dock')
    @foreach (
        [
            'mention_out' => 'Andığı Kişiler',
            'mention_in' => 'Anıldığı Kişiler',
            'hashtags' => 'Hashtag Geçmişi',
            'places' => 'Konum Geçmişi',
            'category' => 'İlgi Alanları'
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
                    data-href="{{ route('media.aggregation', [ 'type' => $key, 'screen_name' => $data['user']['id'] ]) }}">
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

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'media',
            'period' => 'daily',
            'title' => 'Günlük Medya Paylaşımı',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'es_index_key' => date('Y.m', strtotime($document['_source']['created_at'])),
            'active' => true
        ],
        [
            'type' => 'media',
            'period' => 'hourly',
            'title' => 'Saatlik Medya Paylaşımı',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'es_index_key' => date('Y.m', strtotime($document['_source']['created_at']))
        ]
    ]
])

@push('wildcard-top')
    <div class="card">
        <div class="card-content d-flex justify-content-between">
            <span class="align-self-center">
                <span class="card-title d-flex">
                    <span class="mr-1">{{ $data['user']['name'] }}</span>
                    <a class="mr-1 green-text" href="https://www.instagram.com/{{ $data['user']['screen_name'] }}/" target="_blank">{{ '@'.$data['user']['screen_name'] }}</a>
                    @isset ($data['user']['verified'])
                        <i class="material-icons blue-text">check</i>
                    @endisset
                </span>
                <a href="https://www.instagram.com/p/{{ $document['_source']['shortcode'] }}/" target="_blank" class="grey-text">https://www.instagram.com/p/{{ $document['_source']['shortcode'] }}/</a>
            </span>
            <img alt="instagram" src="{{ asset('img/logos/instagram.svg') }}" class="align-self-center" style="width: 64px;" />
        </div>
    </div>
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <img alt="" width="240" class="materialboxed" src="{{ $document['_source']['display_url'] }}" />
        </div>

        @php
        $url = 'https://www.instagram.com/p/'.$document['_source']['shortcode'].'/';
        @endphp

        <div class="card-content pt-0">
            @isset ($document['_source']['text'])
                <div class="markdown">{!! Term::instagramMedia($document['_source']['text']) !!}</div>
            @endisset
            <a class="green-text" href="{{ $url }}" target="_blank">{{ $url }}</a>
        </div>

        @include('content._inc.archive_bar', [
            'document' => $document
        ])
    </div>
@endsection

@push('external.include.footer')
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
                            'href': '{{ route('search.dashboard') }}?q={{ 'user.id:'.$data['user']['id'] }} ' + o.key,
                            'target': '_blank'
                        }))
                    }
                    else if (__.attr('data-type') == 'mention_in')
                    {
                        name.html($('<a />', {
                            'html': '@' + o.key,
                            'href': '{{ route('search.dashboard') }}?q=user.id:' + o.key + ' {{ $data['user']['screen_name'] }}',
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
@endpush
