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
    'id' => $document['_source']['site_id'],
])

@section('content')
<div class="row">
    <div class="col m12 xl6">
        <div class="card">
            <div class="card-content">
                <span class="card-title d-table">Sitede Sık Kullanılan Kelimeler</span>
                @forelse (@$data['keywords'] as $key => $word)
                    <span class="chip">{{ $key }}</span>
                @empty
                    <span class="chip red white-text">Tespit Edilemedi</span>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col m12 xl6">
        @include('content._inc.sentiment', [
            'total' => $data['total']->data['count'],
            'pos' => $data['pos']->data['count'],
            'neg' => $data['neg']->data['count']
        ])
    </div>
    <div class="col m12 xl12">
        <div class="card teal white-text">
            <div class="card-content">
                <a href="{{ $document['_source']['url'] }}" class="card-title white-text d-flex" target="_blank">
                    <i class="material-icons mr-1">insert_link</i>
                    <span>{{ $document['_source']['title'] }}</span>
                </a>
                <div class="markdown">{!! Term::markdown($document['_source']['description']) !!}</div>
            </div>
            <div class="card-content teal lighten-1 d-flex justify-content-between">
                <time data-position="right" data-tooltip="Oluşturuldu" data-time="{{ $document['_source']['created_at'] }}">{{ date('d.m.Y H:i', strtotime($document['_source']['created_at'])) }}</time>
                <time data-position="left" data-tooltip="Alındı" data-time="{{ $document['_source']['called_at'] }}">{{ date('d.m.Y H:i', strtotime($document['_source']['called_at'])) }}</time>
            </div>
        </div>

        <div class="card">
            <div class="card-content teal">
                <span class="card-title white-text mb-0">Diğer Sitelerdeki Benzer Haberler</span>
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
                    <span class="d-table grey-text" data-name="title"></span>
                    <a href="#" class="grey-text" data-name="url" target="_blank"></a>
                    <time class="d-table teal-text mb-0" data-name="created-at"></time>
                </div>
            </div>
        </div>

        @component('components.loader')
            @slot('color', 'teal')
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

                        item.find('[data-name=title]').html(o._source.title)
                        item.find('[data-name=url]').html(o._source.url).attr('href', o._source.url)
                        item.find('[data-name=created-at]').html(o._source.created_at)

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }
@endpush
