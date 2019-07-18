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
            'text' => '🐞 Instagram Ayarları'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@push('local.scripts')
    var statisticsTimer;

    function __statistics(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.instagram.medias.data._all.primaries.docs)
            {
                $('[data-name=media-count]').html(number_format(obj.data.instagram.medias.data._all.primaries.docs.count))
                $('[data-name=media-size]').html(humanFileSize(obj.data.instagram.medias.data._all.total.store.size_in_bytes))
            }
            else
            {
                $('[data-elasticsearch=medias]').html('Index Oluşturulmadı!')
            }

            if (obj.data.instagram.users.message)
            {
                var message = $.parseJSON(obj.data.instagram.users.message);
            }
            else
            {
                var message = { 'status': obj.data.instagram.users.status == 'ok' ? 200 : 404 };
            }

            if (message.status == '404')
            {
                $('[data-elasticsearch=users]').html('Index Oluşturulmadı!')
            }
            else
            {
                $('[data-name=user-count]').html(number_format(obj.data.instagram.users.data._all.primaries.docs.count))
                $('[data-name=user-size]').html(humanFileSize(obj.data.instagram.users.data._all.total.store.size_in_bytes))
            }

            window.clearTimeout(statisticsTimer)

            statisticsTimer = window.setTimeout(function() {
                vzAjax($('[data-callback=__statistics]'))
            }, 10000)
        }
    }

    function __connection_failed(__)
    {
        $('[data-elasticsearch]').html('ES Bağlantı Hatası')
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">settings</i>
                Instagram Ayarları
            </span>
        </div>
        <div id="stats">
            <div class="card-content grey-text">
                <div
                    class="item-group load"
                    data-href="{{ route('admin.instagram.statistics') }}"
                    data-timeout="4000"
                    data-method="post"
                    data-callback="__statistics"
                    data-error-callback="__connection_failed">
                    <div class="item">
                        <small class="d-block grey-text">Alınan Medya</small>
                        <p data-elasticsearch="medias" data-name="media-count">-</p>
                    </div>
                    <div class="item">
                        <small class="d-block grey-text">Kullanılan Alan</small>
                        <p data-elasticsearch="medias" data-name="media-size">-</p>
                    </div>
                    <div class="item">
                        <small class="d-block grey-text">Alınan Kullanıcı</small>
                        <p data-elasticsearch="users" data-name="user-count">-</p>
                    </div>
                    <div class="item">
                        <small class="d-block grey-text">Kullanılan Alan</small>
                        <p data-elasticsearch="users" data-name="user-size">-</p>
                    </div>
                </div>
            </div>
            <div class="card-content red hide" data-name="alert"></div>
        </div>
        <div class="card-content orange lighten-4">Bu alan sadece "Instagram" modülü ile ilgili logları gösterir.</div>
        <ul
            id="console"
            class="collection load no-select"
            data-href="{{ route('admin.instagram.monitoring.log') }}"
            data-callback="__log"
            data-method="post">
            <li class="collection-item hide">
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
                        item.find('[data-name=message]').val(o.message)

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
    #collections {
        overflow: hidden;
    }
    #collections > a.collection-item > input {
        border-width: 0;
        margin: 0;
        padding: 0;
        height: 24px;
        cursor: pointer;
    }
    #collections > a.collection-item > input:nth-of-type(1),
    #collections > a.collection-item > input:nth-of-type(2) {
        width: 50%;
    }
@endpush

@section('dock')
    <div class="collection">
        @if ($options['instagram.index.medias'] == date('Y.m', strtotime('+ 1 month')))
            <label class="collection-item waves-effect d-block">
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('admin.instagram.option.set') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="instagram.status"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    @if ($options['instagram.status'] == 'on'){{ 'checked' }}@endif  />
                <span>Medya Botu</span>
            </label>
        @else
            <div class="collection-item d-block">
                <i class="material-icons d-table">warning</i>
                Medya indexlerinin oluşturulması bekleniyor.
            </div>
        @endif
    </div>
    @include('crawlers.instagram._menu', [ 'active' => 'dashboard' ])
@endsection
