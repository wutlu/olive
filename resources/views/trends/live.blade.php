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

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.styles')
    .trend-collection > .collection-item {
        opacity: .2;
        padding: 6px 24px;
    }
    .trend-collection > .collection-item.on { opacity: 1; }

    .chart {
        width: 64px;
        height: 24px;
    }
@endpush

@push('local.scripts')
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

    function __trends(__, obj)
    {
        if (obj.status == 'ok')
        {
            var module = __.data('module');

            $('#' + module + '-loader').addClass('hide')

            var collection = $('[data-module=' + module + ']'),
                model = collection.children('[data-model]')

            collection.children('.item').removeClass('on')

            $.each(obj.data, function(rank, o) {
                var hasItem = collection.children('#' + module + '-item-' + rank).length;

                var item = hasItem ? $('#' + module + '-item-' + rank) : model.clone().removeAttr('data-model').removeClass('hide').attr('id', module + '-item-' + rank);

                    item.addClass('on')

                    item.find('[data-name=rank]').html(rank)
                    item.find('[data-name=title]').html(o.title)

                if (module == 'youtube')
                {
                    item.find('[data-name=title]')
                        .attr('href', 'https://www.youtube.com/watch?v=' + o.id)

                    item.find('[data-name=image]')
                        .attr('src', 'https://i.ytimg.com/vi/' + o.id + '/default.jpg')
                        .attr('alt', o.title);
                }

                var elements = {
                    'first': o.ranks[0],
                    'last': o.ranks[o.ranks.length-1]
                };

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

                if (o.ranks.length && item.find('[data-name=chart]').length)
                {
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
                }
            })

            collection.children('.item:not(.on)').remove()

            window.setTimeout(function() {
                vzAjax(collection)
            }, 60000)
        }
    }
@endpush

@section('content')
    <div class="row">
        <div class="col m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Sözlük</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="sozluk">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto">
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
        <div class="col m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Medya</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="news">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto">
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
        <div class="col m12 l12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">YouTube</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="youtube">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <img data-name="image" style="height: 32px;" class="mr-1" />
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" target="_blank" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto">
                                <canvas width="64" height="32" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
                @component('components.loader')
                    @slot('id', 'youtube-loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>
        </div>
        <div class="col m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Google</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="google">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto">
                                <canvas width="64" height="32" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
                @component('components.loader')
                    @slot('id', 'google-loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>
        </div>
        <div class="col m12 l6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Twitter</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="twitter">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto">
                                <canvas width="64" height="32" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
                @component('components.loader')
                    @slot('id', 'twitter-loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                @endcomponent
            </div>
        </div>
    </div>
@endsection
