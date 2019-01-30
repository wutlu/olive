@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi',
            'link' => route('crawlers')
        ],
        [
            'text' => '🐞 Alışveriş Botları'
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
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)

                        item.find('[data-name=id]').html(o.id)
                        item.find('[data-name=error]').html(o.error_count + ' hata').removeClass(o.error_count ? 'grey-text' : 'red-text').addClass(o.error_count ? 'red-text' : 'grey-text')
                        item.find('[data-name=control-time]').attr('data-time', o.control_date).html(o.control_date)
                        item.find('[data-name=control-interval]').html(o.control_interval + ' dakika')
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

    function __connection_failed(__)
    {
        $('[data-elasticsearch]').html('ES Bağlantı Hatası')
    }
@endpush

@section('wildcard')
    <div class="card grey darken-4">
        <div class="card-image">
            <a class="btn-floating btn-large halfway-fab waves-effect teal dropdown-trigger" data-target="more">
                <i class="material-icons">more_vert</i>
            </a>
        </div>

        <div class="container">
            <table
                id="stats"
                class="load"
                data-method="post"
                data-timeout="4000"
                data-href="{{ route('crawlers.shopping.bot.statistics.all') }}"
                data-callback="__stats"
                data-error-callback="__connection_failed">
                <tbody>
                    <tr>
                        <th class="right-align grey-text">BOYUT</th>
                        <th class="cyan-text" data-elasticsearch data-name="total-size">-</th>

                        <th class="right-align grey-text">KUYRUK</th>
                        <th class="cyan-text" data-elasticsearch data-name="total-docs-buffer">-</th>

                        <th class="right-align grey-text">BAŞARILI</th>
                        <th class="cyan-text" data-elasticsearch data-name="total-docs-success">-</th>

                        <th class="right-align grey-text">BAŞARISIZ</th>
                        <th class="cyan-text" data-elasticsearch data-name="total-docs-failed">-</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <ul id="more" class="dropdown-content">
        <li>
            <a class="waves-effect" href="{{ route('crawlers.shopping.bot') }}">Yeni Bot</a>
        </li>
        <li>
            <a
                href="#"
                class="waves-effect"
                data-message="Oluşturulmamış indexlerin oluşturulması için istek gönderilecek?"
                data-trigger="trigger"
                data-href="{{ route('crawlers.shopping.bot.index.all') }}"
                data-callback="__create_all_index">Eksik Indexleri Oluştur</a>
        </li>
        <li>
            <a
                href="#"
                class="waves-effect"
                data-message="Pasif fakat test edilmiş tüm botlar çalıştırılacak?"
                data-trigger="trigger"
                data-href="{{ route('crawlers.shopping.bot.start.all') }}"
                data-callback="__start_all">Pasif Botları Çalıştır</a>
        </li>
        <li>
            <a
                href="#"
                class="waves-effect"
                data-message="Aktif tüm botlar durdurulacak?"
                data-trigger="trigger"
                data-href="{{ route('crawlers.shopping.bot.stop.all') }}"
                data-callback="__stop_all">Aktif Botları Durdur</a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Alışveriş Botları</span>
            <span class="d-block" data-name="bots-count"></span>
        </div>

        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#crawlers"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear"
             id="crawlers"
             data-method="post"
             data-href="{{ route('crawlers.shopping.list.json') }}"
             data-skip="0"
             data-take="50"
             data-include="string"
             data-more-button="#crawlers-more_button"
             data-callback="__crawlers"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                data-href="{{ route('route.generate.id') }}"
                data-method="post"
                data-name="crawlers.shopping.bot"
                data-callback="__go"
                class="collection-item model hide json justify-content-between">
                <span class="align-self-center">
                    <p>
                        <span class="rank" data-name="id"></span> <span data-name="name"></span>
                    </p>
                    <p data-name="site" class="grey-text"></p>
                    <p class="grey-text" data-name="error"></p>
                    <p class="grey-text">
                        <time class="timeago" data-name="control-time"></time> / <span data-name="control-interval"></span>
                    </p>
                </span>
                <small class="align-self-center">
                    <p>
                        <i class="material-icons" data-name="test">sentiment_very_satisfied</i>
                    </p>
                    <p>
                        <i class="material-icons" data-name="index">storage</i>
                    </p>
                    <p>
                        <i class="material-icons" data-name="status">power</i>
                    </p>
                </small>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="crawlers-more_button"
                type="button"
                data-json-target="#crawlers">Daha Fazla</button>
    </div>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=trigger]', function() {
        var __ = $(this);

        var mdl = modal({
                'id': 'trigger',
                'body': __.data('message'),
                'size': 'modal-small',
                'title': 'Uyarı',
                'options': {},
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat grey-text',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat cyan-text json',
                        'html': buttons.ok,
                        'data-href': __.data('href'),
                        'data-method': 'post',
                        'data-callback': __.data('callback')
                    })
                ]
            });
    })

    function __create_all_index(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Tüm botlar için index oluşturma isteği gönderildi.', classes: 'orange' })

            $('#modal-trigger').modal('close')
        } 
    }

    function __stop_all(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Aktif tüm botlar durduruldu.', classes: 'green darken-2' })

            $('#modal-trigger').modal('close')
        } 
    }

    function __start_all(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Pasif ve test edilmiş tüm botlar çalıştırıldı.', classes: 'green darken-2' })

            $('#modal-trigger').modal('close')
        } 
    }

    var statTimer;

    function __stats(__, obj)
    {
        if (obj.status == 'ok')
        {
            var es_catch_val = 'ES {error}';

            $('[data-name=bots-count]').html(obj.data.count.active + ' / ' + (obj.data.count.active + obj.data.count.disabled))

            try { $('[data-name=total-docs-success]').html(number_format(obj.data.count.success.data.count)) }
            catch (err) { $('[data-name=total-docs-success]').html(es_catch_val) }
            try { $('[data-name=total-docs-failed]').html(number_format(obj.data.count.failed.data.count)) }
            catch (err) { $('[data-name=total-docs-failed]').html(es_catch_val) }
            try { $('[data-name=total-docs-buffer]').html(number_format(obj.data.count.buffer.data.count)) }
            catch (err) { $('[data-name=total-docs-buffer]').html(es_catch_val) }
            try { $('[data-name=total-size]').html(humanFileSize(obj.data.elasticsearch.data._all.primaries.store.size_in_bytes)) }
            catch (err) { $('[data-name=total-size]').html(es_catch_val) }

            window.clearTimeout(statTimer)

            statTimer = setTimeout(function() {
                vzAjax($('#stats'))
            }, 10000)
        }
    }
@endpush
