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
    'wide' => true,
    'dock' => true
])

@section('dock')
    @include('trends._menu', [ 'active' => 'trends' ])
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.styles')
    .trend-collection > .collection-item {
        opacity: .2;
        padding: 6px 24px;
    }

    .trend-collection > .collection-item.on {
        opacity: 1;
    }

    .trend-collection > .collection-item .chart {
        width: 64px;
        height: 24px;
    }

    @media (max-width: 1200px) {
        .trend-collection > .collection-item .chart {
            display: none;
        }
    }
@endpush

@push('local.scripts')
    $('.sortable').sortable({
        handle: '.handle'
    })

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

    $(document).on('click', '[data-run]', function() {
        var __ = $(this);

        if (__.data('status') == 'on')
        {
            console.log('stopped')

            __.data('status', 'off')
            __.find('i.material-icons').html('play_arrow')

            M.toast({ html: 'Canlı Trend Durduruldu', 'classes': 'red' })
        }
        else
        {
            __.data('status', 'on')
            __.find('i.material-icons').html('pause')

            M.toast({ html: 'Canlı Trend Başlatıldı', 'classes': 'green' })
        }
    })
@endpush

@section('content')
<div class="sortable">
            <div class="card">
                <div class="card-content d-flex">
                    <a href="#" class="handle align-self-center btn-floating btn-flat mr-1">
                        <i class="material-icons">drag_handle</i>
                    </a>
                    <span class="card-title align-self-center">Sözlük</span>
                    <a href="#" class="align-self-center btn-floating cyan darken-2 waves-effect ml-auto" data-run="off" data-module="sozluk">
                        <i class="material-icons">play_arrow</i>
                    </a>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load_"
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
                                <canvas width="64" height="24" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Medya</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load_"
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
                                <canvas width="64" height="24" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Google</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load_"
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
                                <canvas width="64" height="24" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Twitter</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load_"
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
                                <canvas width="64" height="24" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-content">
                    <span class="card-title">YouTube</span>
                </div>
                <ul
                    class="collection collection-hoverable trend-collection load_"
                    data-method="post"
                    data-href="{{ route('trend.live.redis') }}"
                    data-callback="__trends"
                    data-module="youtube">
                    <li class="collection-item item hide" data-model>
                        <div class="d-flex">
                            <i class="material-icons align-self-center center-align" data-name="arrow"></i>
                            <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                            <img data-name="image" style="height: 24px;" class="align-self-center mr-1" />
                            <a href="#" class="align-self-center" data-name="title"></a>
                            <div class="chart align-self-center ml-auto">
                                <canvas width="64" height="24" data-name="chart"></canvas>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
</div>
@endsection
