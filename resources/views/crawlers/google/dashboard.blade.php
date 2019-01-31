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
            'text' => '🐞 Google Ayarları'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Google Ayarları</span>
            <div class="item-group">
                <div class="item">
                    <small class="d-block grey-text">Arama Sayısı</small>
                    <p data-elasticsearch data-name="search-count">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Kapladığı Alan</small>
                    <p data-elasticsearch data-name="search-size">-</p>
                </div>
            </div>
        </div>
        <div class="card-content red hide" data-name="alert"></div>
        <ul
            id="console"
            class="collection load d-flex align-items-end flex-wrap no-select"
            data-href="{{ route('admin.google.monitoring.log') }}"
            data-callback="__log"
            data-method="post">
            <li class="collection-item hide" style="width: 100%;">
                <p>
                    <span data-name="level"></span>
                    <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                    <span data-name="repeat"></span>
                    <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                    <time data-name="updated-at" class="timeago grey-text text-darken-2"></time>
                </p>
                <p>
                    <time data-name="created-at" class="timeago grey-text text-darken-2"></time>
                    <span data-name="module" class="grey-text text-darken-2"></span>
                </p>
                <textarea data-name="message" class="green-text d-block"></textarea>
            </li>
        </ul>
    </div>
@endsection

@push('local.scripts')
    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('ul#console');
            var model = collection.children('li.collection-item.hide');

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var m = $('[data-id=' + o.uuid + ']');

                    var item = m.length ? m : model.clone();
                        item.removeClass('hide')
                            .attr('data-id', o.uuid)

                        item.find('[data-name=level]').html(o.level + '. seviye').addClass(o.level <= 4 ? 'green-text' : o.level <= 7 ? 'orange-text' : 'red-text')
                        item.find('[data-name=repeat]').html(o.hit + ' tekrar').addClass(o.hit <= 10 ? 'green-text' : o.hit <= 20 ? 'orange-text' : 'red-text')
                        item.find('[data-name=updated-at]').attr('data-time', o.updated_at)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)
                        item.find('[data-name=module]').html(o.module)
                        item.find('[data-name=message]').html(o.message)

                    if (m.length)
                    {
                        if (m.attr('data-repeat') != o.hit)
                        {
                            item.attr('data-repeat', o.hit)
                        }
                    }
                    else
                    {
                        item.find('[data-name=updated-at]').html(o.updated_at)
                        item.find('[data-name=created-at]').html(o.created_at)
                        item.attr('data-repeat', o.hit)

                    }

                    item.appendTo(collection)
                })
            }

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax($('ul#console'))
            }, 10000)
        }
    }

    function __connection_failed(__)
    {
        $('[data-elasticsearch]').html('ES Bağlantı Hatası')
    }
@endpush

@section('dock')
    <div class="card">
        <div
            class="collection load"
            data-method="post"
            data-href="{{ route('admin.google.index.status') }}"
            data-callback="__status"
            data-timeout="4000"
            data-error-callback="__connection_failed">
            <label class="collection-item waves-effect d-block">
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('admin.google.status.set') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="google.status"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    data-callback="__status_set"
                    @if ($options['google.status'] == 'on'){{ 'checked' }}@endif />
                <span>Çalışıyor</span>
            </label>
            @if ($options['google.index.search'] == 'off')
                <a
                    href="#"
                    class="collection-item waves-effect d-block json"
                    data-href="{{ route('admin.google.index.create') }}"
                    data-method="post"
                    data-trigger="search-index"
                    data-callback="__index_create">Indexleri Oluştur</a>
            @endif
        </div>
    </div>
@endsection

@push('local.scripts')
    function __status_set(__, obj)
    {
        if (obj.status == 'err')
        {
            M.toast({ html: 'Önce indexleri oluşturmanız gerekiyor.', classes: 'red' })

            __.prop('checked', false)
        }
    }

    function __index_create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Index oluşturma isteği gönderildi. Lütfen bekleyin...', classes: 'orange' })
        }
    }

    var statusTimer;

    function __status(__, obj)
    {
        if (obj.status == 'ok')
        {
            var indice = obj.elasticsearch.data.indices['olive__google-search'];

            if (indice)
            {
                $('[data-name=search-count]').html(number_format(indice.primaries.docs.count))
                $('[data-name=search-size]').html(humanFileSize(indice.total.store.size_in_bytes))

                $('[data-name=alert]').addClass('hide')
                $('[data-trigger=search-index]').remove()
            }
            else
            {
                $('[data-elasticsearch]').html('Indexe ulaşılamıyor!')
                $('[data-name=alert]').html('İlgili index daha önce oluşturulmuştu. Şu an bu indexlere ulaşılamıyor.').removeClass('hide')
            }
        }
        else if (obj.status == 'err')
        {
            $('[data-elasticsearch]').html('Index isteği hiç gönderilmedi.')
            $('[data-name=alert]').html('Tüm indexleri oluşturmadan sistemi çalıştıramazsınız. Lütfen sağ menüden "Indexleri Oluştur" butonuna basın ve bekleyin.').removeClass('hide')
        }

        window.clearTimeout(statusTimer)

        statusTİmer = window.setTimeout(function() {
            vzAjax($('[data-callback=__status]'))
        }, 5000)
    }
@endpush
