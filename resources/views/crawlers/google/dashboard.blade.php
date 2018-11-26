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
            'text' => 'Google Ayarları'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Google Ayarları" />
            <span class="card-title">Google Ayarları</span>
        </div>
        <div class="card-content">
            <div class="item-group">
                <div class="item">
                    <small class="d-block grey-text">Arama Sayısı</small>
                    <p data-name="search-count">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Kapladığı Alan</small>
                    <p data-name="search-size">-</p>
                </div>
            </div>
        </div>
        <div class="card-content red d-none" data-name="alert"></div>
    </div>
    <div class="card">
        <div class="card-content">
            <span class="card-title mb-0">Hata Logları</span>
        </div>
        <div class="card-content red lighten-5">Log takibini log monitörü bölümünden de yapabilirsiniz. Bu alan sadece "Google" modülü ile ilgili logları gösterir.</div>
        <ul
            id="console"
            class="collection black load d-flex align-items-end flex-wrap no-select"
            data-href="{{ route('admin.google.monitoring.log') }}"
            data-callback="__log"
            data-method="post">
            <li class="collection-item d-none" style="width: 100%;">
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
            var model = collection.children('li.collection-item.d-none');

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var m = $('[data-id=' + o.uuid + ']');

                    var item = m.length ? m : model.clone();
                        item.removeClass('d-none')
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
@endpush

@push('local.styles')
    ul#console {
        height: 600px;
        overflow-y: scroll;
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center;
    }

    ul#console > li textarea {
        border-width: 0;
        background-color: rgba(255, 0, 0, .1);
        resize: none;
    }
@endpush

@section('dock')
    <div class="card">
        <div
            class="collection load"
            data-href="{{ route('admin.google.index.status') }}"
            data-callback="__status">
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
                    data-callback="__index_create">Indeksleri Oluştur</a>
            @endif
        </div>
    </div>
@endsection

@push('local.scripts')
    function __status_set(__, obj)
    {
        if (obj.status == 'err')
        {
            M.toast({ html: 'Önce indeksleri oluşturmanız gerekiyor.', classes: 'red' })

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
            var vid = obj.elasticsearch.data.indices['olive__google-search'].total;

            $('[data-name=search-count]').html(number_format(vid.docs.count))
            $('[data-name=search-size]').html(humanFileSize(vid.store.size_in_bytes))

            $('[data-name=alert]').addClass('d-none')

            $('[data-trigger=search-index]').remove()
        }
        else if (obj.status == 'err')
        {
            $('[data-name=alert]').html('Sistemin çalışması için tüm indekslerin oluşturulması gerekiyor.').removeClass('d-none')
        }

        window.clearTimeout(statusTimer)

        statusTİmer = window.setTimeout(function() {
            vzAjax($('[data-callback=__status]'))
        }, 5000)
    }
@endpush
