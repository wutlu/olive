@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'pin_group' => true,
    'dock' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ],
    'footer_hide' => true
])

@if (@$data['keywords'])
    @push('local.scripts')
        var words = [
            @foreach ($data['keywords'] as $key => $count)
                {
                    text: '{{ $key }}',
                    weight: {{ $count }},
                    link: '{{ route('search.dashboard') }}?q="{{ $key }}"'
                },
            @endforeach
        ];

        $('#words').jQCloud(words)
    @endpush

    @push('local.styles')
        #words {
            height: 400px;
        }
    @endpush
@endif

@section('dock')
    <div class="card mb-1">
        <div class="card-content"> 
            @if (@$data['keywords'])
                <div id="words"></div> 
            @else
                @component('components.nothing')@endcomponent
            @endif
        </div>
    </div>
@endsection

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'document',
            'period' => 'daily',
            'title' => 'Günlük Makale Paylaşımı',
            'id' => $data['crawler']->id,
            'unique_id' => 'tab_1',
            'es_index_key' => $data['crawler']->elasticsearch_index_name,
            'active' => true
        ],
        [
            'type' => 'document',
            'period' => 'hourly',
            'title' => 'Saatlik Makale Paylaşımı',
            'id' => $data['crawler']->id,
            'unique_id' => 'tab_2',
            'es_index_key' => $data['crawler']->elasticsearch_index_name
        ]
    ]
])

@push('wildcard-top')
    <div class="card">
        <div class="card-content">
            <span class="card-title">{{ $data['crawler']->name }}</span>
            <a href="{{ $data['crawler']->site }}" target="_blank" class="green-text">{{ $data['crawler']->site }}</a>
        </div>
    </div>
@endpush

@push('local.styles')
    [data-name=title] {
        font-size: 18px;
    }
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-image">
            <img src="{{ @$document['_source']['image_url'] }}" onerror="this.onerror=null;this.src='/img/md-s/21.jpg';" alt="Image" />
            <span class="card-title">{{ $document['_source']['title'] }}</span>
        </div>
        <div class="card-content">
            <div class="markdown">{!! Term::markdown($document['_source']['description']) !!}</div>
            <a class="green-text" href="{{ $document['_source']['url'] }}" target="_blank">{{ $document['_source']['url'] }}</a>
        </div>
        @include('content._inc.pin_bar', [
            'document' => $document
        ])
    </div>
    <div class="card">
        <div class="card-content">
            <span class="card-title">Benzer Makaleler</span>
            <small class="grey-text text-darken-2">Diğer blog siteleri dahildir.</small>
        </div>
        <div class="collection load json-clear"
             id="smilars"
             data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id ]) }}"
             data-method="post"
             data-skip="0"
             data-take="5"
             data-more-button="#smilars-more_button"
             data-callback="__smilars"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'Üzgünüz, hiç benzer içerik yok.')
                @endcomponent
            </div>
            <div class="collection-item model hide">
                <a href="#" class="d-table blue-text" data-name="title"></a>
                <time class="d-table grey-text" data-name="created-at"></time>
                <span class="d-table grey-text text-darken-2" data-name="description"></span>
                <a href="#" class="d-table green-text" data-name="url" target="_blank"></a>
            </div>
        </div>

        @component('components.loader')
            @slot('color', 'teal')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="more hide json"
       id="smilars-more_button"
       data-json-target="#smilars">Daha Fazla</a>
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/jquery.cloud.min.css?v='.config('system.version')) }}" />
@endpush
@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.cloud.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    function __smilars(__, obj)
    {
        var ul = $('#smilars');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone().html(_document_(o));
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)
                        item.appendTo(ul)
                })
            }
        }
    }
@endpush
