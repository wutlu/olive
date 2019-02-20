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
    'index' => $es->index,
    'type' => $es->type,
    'id' => $document['_source']['id'],
])

@section('content')
    <div class="row">
        <div class="col m12 xl12">
            <div class="card">
                <div class="card-content">
                    <a href="{{ $document['_source']['url'] }}" class="card-title d-flex" target="_blank">
                        <i class="material-icons mr-1">insert_link</i>
                        <span>{{ $document['_source']['title'] }}</span>
                    </a>

                    @isset ($document['_source']['description'])
                        <div class="markdown">{!! Term::markdown($document['_source']['description']) !!}</div>
                    @endisset
                </div>
            </div>

            <div class="card">
                <div class="card-content cyan darken-2">
                    <span class="card-title white-text mb-0">Benzer Ürünler</span>
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

                        <span class="price red white-text">
                            <span data-name="price-amount"></span>
                            <span data-name="price-currency"></span>
                        </span>

                        <ul class="d-flex" data-name="breadcrumb"></ul>

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

@section('dock')
    <div class="card">
        <div class="card-content yellow lighten-4">
            <span class="grey-text text-darken-4">{{ number_format($document['_source']['price']['amount']) }}</span>
            <span class="grey-text">{{ $document['_source']['price']['currency'] }}</span>
        </div>
        <div class="card-content">
            <span class="red-text">{{ title_case($document['_source']['seller']['name']) }}</span>
            @isset ($document['_source']['seller']['phones'])
                @foreach ($document['_source']['seller']['phones'] as $key => $phone)
                    <p class="grey-text">{{ $phone['phone'] }}</p>
                @endforeach
            @endisset
        </div>

        <div class="card-tabs">
            <ul class="tabs dock-tabs cyan tabs-transparent tabs-fixed-width">
                <li class="tab">
                    <a href="#category" class="active">Kategori</a>
                </li>
                <li class="tab">
                    <a href="#address">Adres</a>
                </li>
            </ul>
        </div>

        @isset ($document['_source']['breadcrumb'])
            <ul class="collection" id="category">
                @foreach ($document['_source']['breadcrumb'] as $key => $segment)
                    <li class="collection-item" data-icon="»">{{ $segment['segment'] }}</li>
                @endforeach
            </ul>
        @endisset

        @isset ($document['_source']['address'])
            <ul class="collection" id="address" style="display: none;">
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
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('[data-name=title]')
                            .html('🔗 ' + o._source.title)
                            .attr('href', '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id)

                        item.find('[data-name=price-amount]').html(o._source.price.amount)
                        item.find('[data-name=price-currency]').html(o._source.price.currency)

                        $.each(o._source.breadcrumb, function(key, o) {
                            item.find('[data-name=breadcrumb]').append($('<li />', {
                                'html': o.segment
                            }))
                        })

                        item.find('[data-name=url]').html(o._source.url).attr('href', o._source.url)
                        item.find('[data-name=created-at]').html(o._source.created_at)

                        item.appendTo(ul)
                })
            }
        }

        $('#home-loader').hide()
    }

    $(document).ready(function() {
        $('.dock-tabs').tabs()
    })
@endpush
