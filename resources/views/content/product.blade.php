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

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'product',
            'period' => 'daily',
            'title' => 'Benzer Ürün Grafiği (Günlük)',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'es_index_key' => $document['_source']['site_id'],
            'active' => true
        ],
        [
            'type' => 'product',
            'period' => 'hourly',
            'title' => 'Benzer Ürün Grafiği (Saatlik)',
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
                <a href="{{ $document['_source']['url'] }}" target="_blank" class="green-text">{{ $document['_source']['url'] }}</a>
            </span>
            <img alt="E-ticaret" src="{{ asset('img/logos/sahibinden.svg') }}" class="align-self-center" style="width: 64px;" />
        </div>
    </div>
@endpush

@push('local.styles')
    [data-name=title] {
        font-size: 18px;
    }
@endpush

@section('content')
    <div class="row">
        <div class="col m12 xl12">
            @isset ($document['_source']['description'])
                <div class="card mb-1">
                    <div class="card-content">
                        <div class="markdown">{!! Term::markdown($document['_source']['description']) !!}</div>
                    </div>
                </div>
            @endisset

            <div class="card">
                <div class="card-content">
                    <span class="card-title">Benzer Ürünler</span>
                    <small class="grey-text text-darken-2">Diğer e-ticaret siteleri dahildir.</small>
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
                        <ul class="d-flex" data-name="breadcrumb"></ul>
                        <span class="d-table red-text">
                            <span data-name="price-amount"></span>
                            <span data-name="price-currency"></span>
                        </span>
                        <a href="#" class="d-table green-text" data-name="url" target="_blank"></a>
                    </div>
                </div>
                @component('components.loader')
                    @slot('color', 'teal')
                    @slot('id', 'home-loader')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>

            <div class="center-align">
                <button class="btn-flat waves-effect hide json"
                        id="smilars-more_button"
                        type="button"
                        data-json-target="#smilars">Daha Fazla</button>
            </div>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card teal">
        <div class="card-content teal darken-2">
            <span class="white-text text-darken-4">{{ number_format($document['_source']['price']['amount']) }}</span>
            <span class="white-text">{{ $document['_source']['price']['currency'] }}</span>
        </div>
        <div class="card-content">
            <span class="teal-text text-lighten-4">{{ title_case($document['_source']['seller']['name']) }}</span>
            @isset ($document['_source']['seller']['phones'])
                @foreach ($document['_source']['seller']['phones'] as $key => $phone)
                    <p class="white-text">{{ $phone['phone'] }}</p>
                @endforeach
            @endisset
        </div>

        <div class="card-tabs">
            <ul class="tabs dock-tabs tabs-transparent tabs-fixed-width">
                <li class="tab">
                    <a href="#category" class="active">Kategori</a>
                </li>
                <li class="tab">
                    <a href="#address">Adres</a>
                </li>
            </ul>
        </div>

        @isset ($document['_source']['breadcrumb'])
            <ul class="collection white" id="category">
                @foreach ($document['_source']['breadcrumb'] as $key => $segment)
                    <li class="collection-item" data-icon="»">{{ $segment['segment'] }}</li>
                @endforeach
            </ul>
        @endisset

        @isset ($document['_source']['address'])
            <ul class="collection white" id="address" style="display: none;">
                @foreach ($document['_source']['address'] as $key => $segment)
                    <li class="collection-item" data-icon="»">{{ $segment['segment'] }}</li>
                @endforeach
            </ul>
        @endisset
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
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
                    var item = item_model.clone().html(_product_(o));
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)
                        item.appendTo(ul)
                })
            }
        }
    }

    $(document).ready(function() {
        $('.dock-tabs').tabs()
    })
@endpush
