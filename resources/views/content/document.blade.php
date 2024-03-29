@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'archive_dock' => true,
    'dock' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ],
    'footer_hide' => true,
    'report_menu' => true
])

@section('dock')
    @if (@$data['category'])
        <div class="card mb-1 p-0">
            <div class="card-content blue-grey white-text">
                <span class="card-title">Blog'un İlgi Alanları</span>
            </div>
            <ul class="collection collection-unstyled aggregation-collection">
                @foreach ($data['category'] as $category => $count)
                    <li class="collection-item">
                        <div class="d-flex justify-content-between">
                            <span class="align-self-center" data-name="name">{{ $category }}</span>
                            <span class="grey align-self-center" data-name="count" style="padding: 0 .4rem;">{{ $count }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
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
            <span class="card-title blue-grey white-text">{{ $document['_source']['title'] }}</span>
        </div>
        <div class="card-content">
            <div class="markdown">{!! Term::markdown($document['_source']['description']) !!}</div>
            <a class="green-text" href="{{ $document['_source']['url'] }}" target="_blank">{{ $document['_source']['url'] }}</a>
        </div>
        @include('content._inc.archive_bar', [
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

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
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

                    if (__.attr('data-type') == 'category')
                    {
                        name.html(o.key)
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
