@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi'
        ],
        [
            'text' => 'Medya Botları'
        ]
    ]
])


@push('local.scripts')
    function __crawlers(__, obj)
    {
        var ul = $('#crawlers');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model d-none')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)

                        item.find('[data-name=report]').html('id: ' + o.id + ' err: '+ o.error_count + ' control: ' + o.control_date)
                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=site]').html(o.site)
                        item.find('[data-name=status]').addClass(o.status ? 'green-text' : 'red-text')
                        item.find('[data-name=index]').addClass(o.elasticsearch_index ? 'green-text' : 'red-text')
                        item.find('[data-name=test]').addClass(o.test ? 'green-text' : 'red-text')

                        item.appendTo(ul)
                })

                $('[data-tooltip]').tooltip()
            }

            $('#home-loader').hide()
        }
    }

    function __go_bot(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = obj.route;
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/36.jpg') }}" alt="Medya Botları" />
            <span class="card-title">Medya Botları</span>
            <a href="{{ route('crawlers.media.bot') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content grey lighten-2">
            <ul class="item-group">
                <li class="item">
                    <small class="grey-text">Aktif/Devre Dışı</small>
                    <p class="d-block">{{ $active_count.'/'.$disabled_count }}</p>
                </li>
                <li class="item">
                    @if (@$stat->data)
                        <small class="grey-text d-block">Toplam Döküman</small>
                        <p class="d-block">{{ number_format($stat->data['_all']['primaries']['docs']['count']) }}</p>
                        <small class="grey-text d-block">Kapladığı Alan</small>
                        <p class="d-block">{{ Term::humanFileSize($stat->data['_all']['primaries']['store']['size_in_bytes'])->readable }}</p>
                    @else
                        <p class="d-block red-text">Elasticsearch server bağlantısı kurulamadı.</p>
                    @endif
                </li>
            </ul>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#crawlers"
                           placeholder="Arayın" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear"
             id="crawlers"
             data-href="{{ route('crawlers.media.list.json') }}"
             data-skip="0"
             data-take="50"
             data-include="string"
             data-more-button="#crawlers-more_button"
             data-callback="__crawlers"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Bot Yok</p>
                </div>
            </div>
            <a
                href="#"
                data-href="{{ route('route.generate.id') }}"
                data-name="crawlers.media.bot"
                data-callback="__go_bot"
                class="collection-item model d-none waves-effect json">
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="site" class="grey-text"></p>
                    <span class="grey-text" data-name="report"></span>
                </span>
                <small class="badge ml-auto">
                    <i class="material-icons" data-name="test">sentiment_very_satisfied</i>
                    <i class="material-icons" data-name="index">storage</i>
                    <i class="material-icons" data-name="status">power</i>
                </small>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect d-none json"
                id="crawlers-more_button"
                type="button"
                data-json-target="#crawlers">Daha Fazla</button>
    </div>
@endsection
