@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Canlı Trend'
        ]
    ],
    'dock' => true,
    'wide' => true
])

@section('dock')
    @include('trends._menu', [ 'active' => 'trends' ])
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.styles')
    .trend-collection > .collection-item { opacity: .2; }
    .trend-collection > .collection-item.on { opacity: 1; }
@endpush

@push('local.scripts')
    $('.owl-twitter').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide',
        autoplay: true,
        autoplayTimeout: 1000,
        autoplayHoverPause: true,
        loop: true
    })

    $('.owl-google').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide',
        autoplay: true,
        autoplayTimeout: 1000,
        autoplayHoverPause: true,
        loop: true
    })

    var options = {
        legend: { display: false },
        scales: {
            xAxes: [ { display: false } ],
            yAxes: [
                {
                    display: false,
                    ticks: {
                        min: 0,
                        max: this.max,
                        callback: function (value) { return (value / this.max * 100).toFixed(0) + '%'; },
                        reverse: true
                    }
                }
            ]
        },

        tooltips: { enabled: false },
        maintainAspectRatio: false
    };

    function __chart(selector, data)
    {
        return new Chart($(selector), {
            type: 'line',
            data: data,
            options: options
        })
    }

    var trendTimer;

    function __trends(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#' + __.data('module') + '-loader').addClass('hide')

            var collection = __,
                model = collection.children('[data-model]')

            __.children('.item').removeClass('on')

            $.each(obj.data, function(rank, o) {
                var hasItem = collection.children('#' + __.data('module') + '-item-' + rank).length;

                var item = hasItem ? $('#' + __.data('module') + '-item-' + rank) : model.clone().removeAttr('data-model').removeClass('hide').attr('id', __.data('module') + '-item-' + rank);

                    item.addClass('on')

                    item.find('[data-name=rank]').html(rank)
                    item.find('[data-name=title]')
                        .attr('href', 'https://www.youtube.com/watch?v=' + o.id)
                        .html(o.title)

                    if (__.data('module') == 'youtube')
                    {
                        item.find('[data-name=image]')
                            .attr('src', 'https://i.ytimg.com/vi/' + o.id + '/maxresdefault.jpg')
                            .attr('alt', o.title);
                    }

                var elements = {
                    'first': o.ranks[0],
                    'last': o.ranks[o.ranks.length-1]
                };

                var colors = {
                    'red': { 'dark': '#f44336', 'light': '#ffebee' },
                    'green': { 'dark': '#4caf50', 'light': '#e8f5e9' },
                    'grey': { 'dark': '#9e9e9e', 'light': '#fafafa' }
                };

                var icons = {
                    'stable': 'remove',
                    'up': 'arrow_drop_up',
                    'down': 'arrow_drop_down',
                    'new': 'star'
                };

                var color_dark = colors.grey.dark;
                var color_light = colors.grey.light;
                var icon = icons.stable;

                if (elements.last < elements.first)
                {
                    color_dark = colors.green.dark;
                    color_light = colors.green.light;
                    icon = icons.up;
                }
                else if (elements.last > elements.first)
                {
                    color_dark = colors.red.dark;
                    color_light = colors.red.light;
                    icon = icons.down;
                }

                if (o.ranks.length == 1)
                {
                    color_dark = colors.green.dark;
                    color_light = colors.green.light;
                    icon = icons.new;
                }

                    item.find('[data-name=arrow]')
                        .html(icon)
                        .css({
                            'color': color_dark
                        })

                    item.appendTo(collection)

                __chart(item.find('[data-name=chart]'), {
                    labels: o.ranks,
                    datasets: [{
                        backgroundColor: color_light,
                        borderColor: color_dark,
                        data: o.ranks,
                        tension: 0.1,
                        borderWidth: 1,
                        radius: 0,
                        fill: 'start'
                    }]
                })
            })

            __.children('.item:not(.on)').remove()

            window.clearTimeout(trendTimer)

            trendTimer = window.setTimeout(function() {
                vzAjax(__)
            }, 60000)
        }
    }
@endpush

@section('wildcard')
    <div class="cyan lighten-5 cyan-text pt-1 pb-1 center-align">
        <p class="mb-0">Trend verileri Olive trend algoritması tarafından oluşturulur. Kaynak sitelerdeki trend verileri ile uyuşması beklenemez.</p>
    </div>
    <div class="z-depth-2 pt-1 pb-1">
        <div class="container container-wide">
            <small class="grey-text d-block">Twitter</small>
            <div class="owl-carousel owl-twitter">
                @for ($i = 0; $i <= 50; $i++)
                    <a href="#" class="p-1 d-table">Örnek Trend {{ $i }}</a>
                @endfor
            </div>
            <small class="grey-text d-block">Google</small>
            <div class="owl-carousel owl-google">
                @for ($i = 0; $i <= 50; $i++)
                    <a href="#" class="p-1 d-table">Örnek Trend {{ $i }}</a>
                @endfor
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col s6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Sözlük</span>
                </div>
                <ul
                    class="collection trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="sozluk">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                                <canvas width="64" height="32" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
                @component('components.loader')
                    @slot('id', 'sozluk-loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>
        </div>
        <div class="col s6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Medya</span>
                </div>
                <ul
                    class="collection trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="news">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                                <canvas width="64" height="32" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
                @component('components.loader')
                    @slot('id', 'news-loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@section('subcard')
    <div class="container container-wide">
        <div class="card card-unstyled">
            <div class="card-content">
                <span class="card-title">YouTube</span>
            </div>
        </div>
        <div
            class="card-columns trend-collection load"
            data-method="post"
            data-href="{{ route('trend.live.redis') }}"
            data-callback="__trends"
            data-module="youtube">
            <div class="card item hide" data-model>
                <div class="card-image cyan">
                    <img data-name="image" style="opacity: .6;" />
                    <a target="_blank" href="#" class="card-title card-title-small" data-name="title"></a>
                </div>
                <div class="card-action d-flex">
                    <a href="#" class="btn-flat waves-effect grey-text text-darken-2">
                        <i class="material-icons">show_chart</i>
                    </a>
                    <a href="#" class="btn-flat waves-effect grey-text text-darken-2">
                        <i class="material-icons">link</i>
                    </a>
                    <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                        <canvas width="64" height="32" data-name="chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @component('components.loader')
            @slot('id', 'youtube-loader')
            @slot('color', 'cyan')
        @endcomponent
    </div>
@endsection
