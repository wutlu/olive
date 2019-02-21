@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Alarmlar'
        ]
    ]
])

@section('action-bar')
    <a href="#" class="btn-floating btn-large halfway-fab waves-effect white">
        <i class="material-icons grey-text text-darken-2">add</i>
    </a>
@endsection

@push('local.scripts')
    function __collections(__, obj)
    {
        var ul = $('#collections');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].collection-item'),

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)

                        item.find('[data-name=image]').attr('src', 'https://i.ytimg.com/vi/' + o.video_id + '/hqdefault.jpg')
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.find('[data-name=video-title]').html(o.video_title)
                        item.find('[data-name=reason]')
                            .html(o.reason ? o.reason : '-')
                            .removeClass('green-text red-text')
                            .addClass(o.reason ? 'red-text' : 'green-text')

                        if (!selector.length)
                        {
                            item.appendTo(ul)
                        }
                })

                $('[data-tooltip]').tooltip()
            }

            $('[data-name=count]').html(obj.hits.length + '/{{ auth()->user()->organisation->capacity*2 }}')
        }
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Alarmlar</span>
            <span class="d-block" data-name="count"></span>
        </div>
    </div>

    <div class="card-group mb-0 load"
             id="collections"
             data-href="{{ route('youtube.video.list') }}"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="nothing hide">
                @component('components.nothing')
                    @slot('cloud_class', 'white-text')
                @endcomponent
            </div>

        <div class="card card-alarm hoverable model hide">
            <div class="group d-flex">
                <div class="card-content grey lighten-5">
                    <small class="grey-text">Kalan Bildirim</small>
                    <span class="d-block" data-name="hit"></span>
                    <small class="grey-text">Bildirim Aralığı</small>
                    <span class="d-block">
                        <span data-name="interval"></span> dakika
                    </span>
                </div>
                <div class="card-content">
                    <span class="card-title card-title-small" data-name="title"></span>
                    <small class="grey-text">Alıcılar</small>
                    <span class="d-block" data-name="emails"></span>
                    <small class="grey-text">Kaynaklar</small>
                    <span class="d-block" data-name="sources"></span>
                </div>
            </div>

            <ul class="days d-flex">
                <li class="day red lighten-2 white-text" data-name="day-1">Pt</li>
                <li class="day red lighten-2 white-text" data-name="day-2">Sa</li>
                <li class="day red lighten-2 white-text" data-name="day-3">Ça</li>
                <li class="day red lighten-2 white-text" data-name="day-4">Pe</li>
                <li class="day red lighten-2 white-text" data-name="day-5">Cu</li>
                <li class="day red lighten-3 white-text" data-name="day-6">Ct</li>
                <li class="day red lighten-3 white-text" data-name="day-7">Pa</li>
                <li class="hour grey lighten-2 grey-text" data-name="start-time"></li>
                <li class="hour grey lighten-2 grey-text" data-name="end-time"></li>
            </ul>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@push('local.styles')
    .card-alarm > ul.days {
        margin: 0;
    }
    .card-alarm > ul.days > li {
        height: 24px;
        line-height: 24px;

        display: table-row;

        -ms-flex: 1 1 auto;
            flex: 1 1 auto;

        text-align: center;
    }
    .card-alarm > ul.days > li.day {
        width: 24px;
    }
    .card-alarm > .group > .card-content:first-child {
        width: 128px;
    }
    .card-alarm > .group > .card-content:last-child {
        width: calc(100% - 96px);
    }
@endpush
