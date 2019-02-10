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
            'text' => '🐞 YouTube Ayarları'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    var statisticsTimer;

    function __statistics(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.youtube.comments.data._all.primaries.docs)
            {
                $('[data-name=comment-count]').html(number_format(obj.data.youtube.comments.data._all.primaries.docs.count))
                $('[data-name=comment-size]').html(humanFileSize(obj.data.youtube.comments.data._all.total.store.size_in_bytes))
            }
            else
            {
                $('[data-elasticsearch=comments]').html('Index Oluşturulmadı!')
            }

            if (obj.data.youtube.videos.message)
            {
                var message = $.parseJSON(obj.data.youtube.videos.message);
            }
            else
            {
                var message = { 'status': obj.data.youtube.videos.status == 'ok' ? 200 : 404 };
            }

            if (message.status == '404')
            {
                $('[data-elasticsearch=videos]').html('Index Oluşturulmadı!')
            }
            else
            {
                $('[data-name=video-count]').html(number_format(obj.data.youtube.videos.data._all.primaries.docs.count))
                $('[data-name=video-size]').html(humanFileSize(obj.data.youtube.videos.data._all.total.store.size_in_bytes))
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
            <span class="card-title">YouTube Ayarları</span>
        </div>
        <div class="card-content grey-text">
            <div
                class="item-group load"
                data-href="{{ route('admin.youtube.statistics') }}"
                data-timeout="4000"
                data-method="post"
                data-callback="__statistics"
                data-error-callback="__connection_failed">
                <div class="item">
                    <small class="d-block grey-text">Alınan Yorum</small>
                    <p data-elasticsearch="comments" data-name="comment-count">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Kullanılan Alan</small>
                    <p data-elasticsearch="comments" data-name="comment-size">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Alınan Video</small>
                    <p data-elasticsearch="videos" data-name="video-count">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Kullanılan Alan</small>
                    <p data-elasticsearch="videos" data-name="video-size">-</p>
                </div>
            </div>
        </div>

        <div class="card-content red hide" data-name="alert"></div>

        <ul
            id="console"
            class="collection load d-flex align-items-end flex-wrap no-select"
            data-href="{{ route('admin.youtube.monitoring.log') }}"
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
        <div class="collection">
            @if ($options['youtube.index.videos'] == 'off' || $options['youtube.index.comments'] != date('Y.m', strtotime('+ 1 month')))
                <div class="collection-item d-block">
                    <i class="material-icons d-table">warning</i>
                    YouTube indexlerinin oluşturulması bekleniyor.
                </div>
            @else
                <label class="collection-item waves-effect d-block">
                    <input
                        name="value"
                        id="value"
                        value="on"
                        class="json"
                        data-href="{{ route('admin.youtube.option.set') }}"
                        data-method="patch"
                        data-delay="1"
                        data-key="youtube.status"
                        data-checked-value="on"
                        data-unchecked-value="off"
                        type="checkbox"
                        @if ($options['youtube.status'] == 'on'){{ 'checked' }}@endif  />
                    <span>Video Botu</span>
                </label>
            @endif
        </div>
    </div>
    @include('crawlers.youtube._menu', [ 'active' => 'youtube.settings' ])
@endsection
