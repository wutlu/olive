@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@push('local.styles')
    .title {
        font-size: 18px;
    }
@endpush

@include('content._inc.histogram', [
    'index' => $es->index,
    'type' => $es->type,
    'id' => $document['_source']['id'],
    'tab_title' => 'Kullanıcının Tweet Grafiği'
])

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('content')
    <div class="row">
        <div class="col m6 s12">
            <div class="card">
                <div class="card-content">
                    <a href="https://twitter.com/{{ $document['_source']['user']['screen_name'] }}" class="card-title d-table" target="_blank">{{ $document['_source']['user']['name'] }}</a>
                    <div class="markdown">{!! Term::tweet($document['_source']['text']) !!}</div>
                </div>
                @include('content._inc.sentiment_bar', [
                    'pos' => $document['_source']['sentiment']['pos'],
                    'neg' => $document['_source']['sentiment']['neg'],
                    'neu' => $document['_source']['sentiment']['neu']
                ])
            </div>
        </div>
        <div class="col m6 s12">
            <div class="card">
                @include('content._inc.sentiment', [
                    'total' => $data['total']->data['count'],
                    'pos' => $data['pos']->data['count'],
                    'neg' => $data['neg']->data['count'],
                    'alert' => 'İlgili kullanıcıdan toplam '.$data['total']->data['count'].' tweet alındı. Sayfadaki istatistik verileri, alınan tweetler üzerinden gerçekleştirilmiştir.'
                ])
            </div>
        </div>
    </div>
    <div class="row">
        @foreach (
            [
                'names' => 'Adlar',
                'screen_names' => 'Kullanıcı Adları',
                'platforms' => 'Platformlar',
                'langs' => 'Diller'
            ] as $key => $model
        )
        <div class="col l3 m6 s12">
            <div class="card">
                <div class="card-content cyan darken-2">
                    <span class="card-title card-title-small white-text">{{ $model }}</span>
                </div>
                <ul class="collection">
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
                            data-href="{{ route('tweet.aggregation', [ 'type' => $key, 'id' => $document['_source']['user']['id'] ]) }}">
                            <i class="material-icons grey-text">refresh</i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        @endforeach
    </div>
    <div class="card">
        <div class="card-content">
            <span class="card-title">Diğer Tweetler ({{ $data['total']->data['count'] }})</span>
        </div>
        <div class="collection load json-clear"
             id="smilars"
             data-href="{{ route('content.smilar', [ 'es_index' => $es->index, 'es_type' => $es->type, 'es_id' => $es->id ]) }}"
             data-method="post"
             data-skip="0"
             data-take="20"
             data-more-button="#smilars-more_button"
             data-callback="__smilars"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </div>
            <div class="collection-item z-depth-1 model hide">
                <a href="#" class="d-table red-text title" data-name="author"></a>
                <time class="d-table grey-text" data-name="created-at"></time>
                <span class="d-table grey-text text-darken-2" data-name="text"></span>
                <a href="#" class="d-table green-text" data-name="url" target="_blank"></a>
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
@endsection

@push('local.scripts')
    function __aggregation(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.closest('li.collection-item').addClass('hide')

            var collection = __.closest('ul.collection');
            var model = collection.children('li.collection-item[data-model]')

            $.each(obj.data, function(key, o) {
                var item = model.clone();
                    item.removeAttr('data-model').removeClass('hide')
                    item.find('[data-name=name]').html(o.key)
                    item.find('[data-name=count]').html(o.doc_count)

                item.appendTo(collection)
            })
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
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('[data-name=author]')
                            .html('🔗 ' + o._source.user.name)
                            .attr('href', '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id)

                        item.find('[data-name=text]').html(o._source.text)
                        item.find('[data-name=url]').html('https://twitter.com/' + o._source.user.screen_name + '/status/' + o._source.id).attr('href', 'https://twitter.com/' + o._source.user.screen_name + '/status/' + o._source.id)
                        item.find('[data-name=created-at]').html(o._source.created_at)

                        item.appendTo(ul)
                })
            }
        }
    }
@endpush
