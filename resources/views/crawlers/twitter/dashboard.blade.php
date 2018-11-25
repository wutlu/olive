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
            'text' => 'Twitter Ayarları'
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
            $('[data-name=tweet-count]').html(obj.data.twitter.tweets.status == 'ok' ? number_format(obj.data.twitter.tweets.data._all.total.docs.count) : '-')
            $('[data-name=trend-count]').html(obj.data.twitter.trends.status == 'ok' ? number_format(obj.data.twitter.trends.data._all.total.docs.count) : '-')
            $('[data-name=tweet-size]').html(obj.data.twitter.size.tweet.status == 'ok' ? humanFileSize(obj.data.twitter.size.tweet.data._all.total.store.size_in_bytes) : '-')
			$('[data-name=trend-size]').html(obj.data.twitter.size.trend.status == 'ok' ? humanFileSize(obj.data.twitter.size.trend.data._all.total.store.size_in_bytes) : '-')

			window.clearTimeout(statisticsTimer)

			statisticsTimer = window.setTimeout(function() {
				vzAjax($('[data-callback=__statistics]'))
			}, 10000)
		}
	}
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Twitter Ayarları" />
            <span class="card-title">Twitter Ayarları</span>
        </div>
        <div class="card-content">
            <div class="item-group load" data-href="{{ route('admin.twitter.statistics') }}" data-method="get" data-callback="__statistics">
                <div class="item">
                    <small class="d-block grey-text">Tweet Sayısı</small>
                    <p data-name="tweet-count">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Kullanılan Alan</small>
                    <p data-name="tweet-size">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Alınan Trend Başlık</small>
                    <p data-name="trend-count">-</p>
                </div>
                <div class="item">
                    <small class="d-block grey-text">Kullanılan Alan</small>
                    <p data-name="trend-size">-</p>
                </div>
            </div>
        </div>
        <div class="card-content red d-none" data-name="alert"></div>
    </div>
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Hata Logları" />
            <span class="card-title">Hata Logları</span>
        </div>
        <div class="card-content orange lighten-4">Log takibini log monitörü bölümünden de yapabilirsiniz. Bu alan sadece "Twitter" modülü ile ilgili logları gösterir.</div>
        <ul
            id="console"
            class="collection black load d-flex align-items-end flex-wrap no-select"
            data-href="{{ route('admin.twitter.monitoring.log') }}"
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
                        item.find('[data-name=message]').val(o.message)

                    if (m.length)
                    {
                        if (m.attr('data-repeat') != o.hit)
                        {
                            item.attr('data-repeat', o.hit)
                            item.appendTo(collection)
                        }
                    }
                    else
                    {
                        item.find('[data-name=updated-at]').html(o.updated_at)
                        item.find('[data-name=created-at]').html(o.created_at)
                        item.attr('data-repeat', o.hit)

                        item.appendTo(collection)
                    }
                })
            }

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax($('ul#console'))
            }, 1000)
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
        <div class="collection">
            @if ($options['twitter.index.trends'] == 'off')
            <div class="collection-item d-block orange-text">
                <i class="material-icons d-table">warning</i>
                Trend Indeksinin oluşturulması bekleniyor.
            </div>
            @else
            <label class="collection-item waves-effect d-block">
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('admin.twitter.option.set') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="twitter.trend.status"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    @if ($options['twitter.trend.status'] == 'on'){{ 'checked' }}@endif  />
                <span>Trend Botu</span>
            </label>
            @endif
            @if ($options['twitter.index.tweets'] == date('Y.m', strtotime('+ 1 month')))
            <label class="collection-item waves-effect d-block">
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('admin.twitter.option.set') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="twitter.status"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    @if ($options['twitter.status'] == 'on'){{ 'checked' }}@endif  />
                <span>Tweet Botu</span>
            </label>
            @else
            <div class="collection-item d-block orange-text">
                <i class="material-icons d-table">warning</i>
                Tweet indekslerinin oluşturulması bekleniyor.
            </div>
            @endif
        </div>
    </div>
	@include('crawlers.twitter._menu', [ 'active' => 'dashboard' ])
@endsection
