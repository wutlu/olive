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
    ]
])

@if (@$data['keywords'])
    @push('local.scripts')
        var words = [
            @foreach ($data['keywords'] as $key => $count)
                {
                    text: '{{ $key }}',
                    weight: {{ $count }},
                    link: '{{ route('search.dashboard', [ 'q' => $key ]) }}'
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
        <div class="card-content cyan darken-2 white-text">Haber sitesinin sık kullandığı kelimeler.</div>
        <div class="card-content"> 
            @if (@$data['keywords'])
                <div id="words"></div> 
            @else
                <span class="chip red white-text">Tespit Edilemedi</span>
            @endif
        </div>
    </div>
@endsection

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'entry',
            'period' => 'daily',
            'title' => 'Başlığa Günlük Cevap',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'es_index_key' => $document['_source']['site_id'],
            'active' => true
        ],
        [
            'type' => 'entry',
            'period' => 'hourly',
            'title' => 'Başlığa Saatlik Cevap',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'es_index_key' => $document['_source']['site_id']
        ]
    ]
])

@push('wildcard-top')
    <div class="card">
        <div class="card-content d-flex justify-content-between">
            <span class="align-self-center">
                <span class="card-title">{{ $document['_source']['title'] }}</span>
                <a href="{{ $document['_source']['url'] }}" target="_blank" class="grey-text">{{ $document['_source']['url'] }}</a>
            </span>
            <img alt="{{ $data['slug'] }}" src="{{ asset('img/logos/'.$data['slug'].'.svg') }}" class="align-self-center" style="width: 64px;" />
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
        <div class="card-content">
            <span class="red-text">{{ $document['_source']['author'] }}</span>
            <div class="markdown">{!! Term::markdown($document['_source']['entry']) !!}</div>
        </div>
        @include('content._inc.pin_bar', [
            'document' => $document
        ])
    </div>
    <div class="card">
        <div class="card-content">
            <span class="card-title">Benzer Entryler</span>
            <small class="grey-text text-darken-2">Diğer sözlükler dahildir.</small>
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
                <span class="d-table red-text" data-name="author"></span>
                <time class="d-table grey-text" data-name="created-at"></time>
                <span class="d-table grey-text text-darken-2" data-name="entry"></span>
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
                    var item = item_model.clone().html(_entry_(o));
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)
                        item.appendTo(ul)
                })
            }
        }
    }
@endpush
