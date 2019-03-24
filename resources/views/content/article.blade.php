@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'article',
            'period' => 'daily',
            'title' => 'Günlük Haber Paylaşımı',
            'id' => $data['crawler']->id,
            'unique_id' => 'tab_1',
            'es_index_key' => $data['crawler']->elasticsearch_index_name,
            'active' => true
        ],
        [
            'type' => 'article',
            'period' => 'hourly',
            'title' => 'Saatlik Haber Paylaşımı',
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
    <div class="row">
        <div class="col m12 xl12">
            <div class="card mb-1">
                <div class="card-content">
                    <span class="card-title d-flex">{{ $document['_source']['title'] }}</span>
                    <div class="markdown">{!! Term::markdown($document['_source']['description']) !!}</div>
                    <a class="green-text" href="{{ $document['_source']['url'] }}" target="_blank">{{ $document['_source']['url'] }}</a>
                </div>
                @include('content._inc.sentiment_bar', [
                    'pos' => $document['_source']['sentiment']['pos'],
                    'neg' => $document['_source']['sentiment']['neg'],
                    'neu' => $document['_source']['sentiment']['neu']
                ])
            </div>
        </div>
        <div class="col m12 xl6">
            <div class="card mb-1">
                <div class="card-content">
                    <span class="card-title">Sık Kullanılan Kelimeler</span>
                </div>
                <div class="card-content cyan darken-2">
                    <p class="white-text">Bu kelimeler ilgili siteye girilen haberlerden elde edilmiştir.</p>
                </div> 
                <div class="card-content"> 
                    @if (@$data['keywords'])
                        @foreach ($data['keywords'] as $key => $word)
                            <a href="{{ route('search.dashboard', [ 'q' => $key ]) }}" target="_blank" class="chip waves-effect">{{ $key }}</a>
                        @endforeach
                    @else
                        <span class="chip red white-text">Tespit Edilemedi</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col m12 xl6">
            @include('content._inc.sentiment', [
                'neu' => $data['total']->data['aggregations']['neutral']['value'],
                'pos' => $data['total']->data['aggregations']['positive']['value'],
                'neg' => $data['total']->data['aggregations']['negative']['value'],
                'alert' => 'İlgili siteden toplam '.$data['total']->data['hits']['total'].' içerik alındı. Sayfadaki istatistik verileri, alınan haberler üzerinden gerçekleştirilmiştir.'
            ])
        </div>
        <div class="col m12 xl12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Benzer Haberler</span>
                    <small class="grey-text text-darken-2">Diğer haber siteleri dahildir.</small>
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
                    @slot('color', 'cyan')
                    @slot('id', 'home-loader')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>

            <a href="#"
               class="btn-small white grey-text more hide json"
               id="smilars-more_button"
               data-json-target="#smilars">Daha Fazla</a>
        </div>
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
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('[data-name=title]')
                            .html('🔗 ' + o._source.title)
                            .attr('href', '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id)

                        item.find('[data-name=description]').html(o._source.description)

                        item.find('[data-name=url]').html(o._source.url).attr('href', o._source.url)
                        item.find('[data-name=created-at]').html(o._source.created_at)

                        item.appendTo(ul)
                })
            }
        }
    }
@endpush
