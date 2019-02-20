@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@include('content._inc.histogram', [
    'index' => $es->index,
    'type' => $es->type,
    'id' => $document['_source']['group_name'],
])

@section('content')
    <div class="row">
        <div class="col m12 xl6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Yanıtlarda Sık Kullanılan Kelimeler</span>
                    @if (@$data['keywords'])
                        @foreach ($data['keywords'] as $key => $word)
                            <span class="chip">{{ $key }}</span>
                        @endforeach
                    @else
                        <span class="chip red white-text">Tespit Edilemedi</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col m12 xl6">
            @include('content._inc.sentiment', [
                'total' => $data['total']->data['count'],
                'pos' => $data['pos']->data['count'],
                'neg' => $data['neg']->data['count'],
                'alert' => 'İlgili başlıktan toplam '.$data['total']->data['count'].' girdi alındı. Sayfadaki istatistik verileri alınan girdiler üzerinden gerçekleştirilmiştir.'
            ])
        </div>
        <div class="col m12 xl12">
            <div class="card">
                <div class="card-content">
                    <a href="{{ $document['_source']['url'] }}" class="card-title d-flex" target="_blank">
                        <i class="material-icons mr-1">insert_link</i>
                        <span>{{ $document['_source']['title'] }}</span>
                    </a>
                    <span class="orange-text">{{ $document['_source']['author'] }}</span>
                    <div class="markdown">{!! Term::markdown($document['_source']['entry']) !!}</div>
                </div>
                @include('content._inc.sentiment_bar', [
                    'pos' => $document['_source']['sentiment']['pos'],
                    'neg' => $document['_source']['sentiment']['neg'],
                    'neu' => $document['_source']['sentiment']['neu']
                ])
            </div>

            <div class="card">
                <div class="card-content cyan darken-2">
                    <span class="card-title white-text mb-0">Benzer Entryler</span>
                </div>
                <div class="collection load json-clear"
                     id="smilars"
                     data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id ]) }}"
                     data-method="post"
                     data-skip="0"
                     data-take="5"
                     data-more-button="#smilars-more_button"
                     data-callback="__smilars"
                     data-nothing>
                    <div class="collection-item nothing hide">
                        @component('components.nothing')@endcomponent
                    </div>
                    <div class="collection-item z-depth-1 model hide">
                        <a href="#" class="d-table" data-name="title"></a>
                        <span class="d-table orange-text" data-name="author"></span>
                        <span class="d-table grey-text" data-name="entry"></span>
                        <a href="#" class="orange-text" data-name="url" target="_blank"></a>
                        <time class="d-table grey-text mb-0" data-name="created-at"></time>
                    </div>
                </div>
            </div>

            @component('components.loader')
                @slot('color', 'cyan')
                @slot('id', 'home-loader')
            @endcomponent

            <div class="center-align">
                <button class="btn-flat waves-effect hide json"
                        id="smilars-more_button"
                        type="button"
                        data-json-target="#smilars">Daha Fazla</button>
            </div>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
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

                        item.find('[data-name=entry]').html(o._source.entry)
                        item.find('[data-name=author]').html(o._source.author)
                        item.find('[data-name=url]').html(o._source.url).attr('href', o._source.url)
                        item.find('[data-name=created-at]').html(o._source.created_at)

                        item.appendTo(ul)
                })
            }
        }

        $('#home-loader').hide()
    }
@endpush
