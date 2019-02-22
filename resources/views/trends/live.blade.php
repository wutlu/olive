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
    'dock' => true
])

@section('dock')
    @include('trends._menu', [ 'active' => 'trends' ])
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.styles')
    .fullscreen {
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: 50%;
    }

    .trend-collection > .collection-item {
        opacity: .2;
        padding: 6px 24px;
    }

    .trend-collection > .collection-item.on {
        opacity: 1;
    }

    .trend-collection > .collection-item [data-name=chart] {
        width: 64px;
        height: 24px;
    }

    @media (max-width: 1200px) {
        .trend-collection > .collection-item [data-name=chart] {
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

    function __chart(parent, data)
    {
        var chart = $('<canvas />', {
            'width': '64',
            'height': '24',
            'data-name': 'chart'
        })

        parent.html(chart)

        setTimeout(function() {
            new Chart(chart, {
                type: 'line',
                data: data,
                options: options
            })
        }, 100)
    }

    var sozlukTrendTimer;
    var newsTrendTimer;
    var googleTrendTimer;
    var twitterTrendTimer;
    var youtubeTrendTimer;

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

                    item.find('[data-name=youtube-link]')
                        .attr('href', 'https://www.youtube.com/watch?v=' + o.id)
                }

                if (module == 'twitter')
                {
                    item.find('[data-name=twitter-link]')
                        .attr('href', 'https://twitter.com/search?q=' + encodeURIComponent(o.title))
                }

                var elements = {
                    'first': o.ranks[0],
                    'last': o.ranks[o.ranks.length-1]
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

                var id = Math.floor(Math.random() * 999999);

                    item.find('[data-name=dropdown-trigger]').attr('data-target', 'dropdown-item-' + id)
                    item.find('[data-name=dropdown]').attr('id', 'dropdown-item-' + id)

                    item.find('[data-name=arrow]')
                        .html(icon)
                        .css({
                            'color': color_dark
                        })

                    item.find('[data-name=olive]')
                        .attr('href', '{{ route('search.dashboard') }}?q=' + encodeURIComponent(o.title))

                    item.find('[data-name=google]')
                        .attr('href', 'https://www.google.com/search?q=%22' + encodeURIComponent(o.title) + '%22')

                    item.appendTo(collection)

                $('[data-target=dropdown-item-' + id + ']').dropdown({
                    'alignment': 'right'
                })

                if (o.ranks.length)
                {
                    __chart(item.find('[data-name=chart-parent]'), {
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

            if (obj.data != null)
            {
                collection.children('.item:not(.on)').remove()
                collection.removeClass('hide')

                $('[data-id=nothing-' + module + ']').addClass('hide')
            }
            else
            {
                $('[data-id=nothing-' + module + ']').removeClass('hide')
            }

            window.clearTimeout(window[module + 'TrendTimer'])
            window[module + 'TrendTimer'] = window.setTimeout(function() {
                vzAjax(collection)
            }, 60000)
        }
    }

    $(document).on('click', '[data-run]', function() {
        var __ = $(this);

        if (__.data('status') == 'on')
        {
            __.data('status', 'off')
            __.find('i.material-icons').html('play_arrow')
            __.removeClass('pulse red')
            __.addClass('cyan')

            M.toast({ html: 'Canlı Trend Durduruldu', 'classes': 'red' })

            setTimeout(function() {
                window.clearTimeout(window[__.data('name') + 'TrendTimer'])
            }, 1000)
        }
        else
        {
            __.data('status', 'on')
            __.find('i.material-icons').html('pause')
            __.addClass('pulse red')
            __.removeClass('cyan')

            M.toast({ html: 'Canlı Trend Başlatıldı', 'classes': 'green' })

            vzAjax($('[data-module=' + __.data('name') + ']'))
        }
    })

    function __archive(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-alert').modal('close')

            M.toast({ 'html': 'Anlık trend görüntüsü alındı. Trend Arşivi sayfasından tüm arşivlere erişebilirsiniz.', 'classes': 'green' })
        }
        else if (obj.status == 'err')
        {
            return modal({
                'id': 'alert',
                'body': 'Trend günlüğü boş olduğundan arşiv alınamadı.',
                'title': 'Hata',
                'size': 'modal-small',
                'options': {},
                'footer': [
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat',
                       'html': buttons.ok
                   })
                ]
            })
        }
    }

    $(document).on('click', '[data-trigger=archive]', function() {
        return modal({
            'id': 'alert',
            'body': 'Anlık trend görüntüsü arşivlenecek. Son 1 dakika içerisinde yapacağınız her istek bir önceki görüntü ile birleştirilecektir.',
            'size': 'modal-small',
            'title': 'Arşiv',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat json',
                    'html': buttons.ok,
                    'data-href': '{{ route('trend.archive.save') }}',
                    'data-method': 'post',
                    'data-key': $(this).data('name'),
                    'data-callback': '__archive'
                })
            ],
            'options': {}
        })
    })
@endpush

@section('wildcard')
    <div class="teal lighten-2 z-depth-1 pt-1 pb-1">
        <div class="container">
            <p class="d-flex mb-0">
                <i class="material-icons mr-1 white-text align-self-center">help_outline</i>
                <span class="white-text align-self-center">Web trendlerini, Olive trend algoritması oluşturur.<br />Kaynak sitelerin trend algoritmalarıyla eşleşmesi beklenmemelidir.</span>
            </p>
        </div>
    </div>
@endsection

@section('content')
    <div class="fullscreen nowrap">
        <div class="fs-element">
            <div class="d-flex justify-content-end">
                <a href="#" class="btn-floating white waves-effect" data-class="body" data-class-add="fs-active" data-tooltip="Tam Ekran" data-position="left">
                    <i class="material-icons grey-text text-darken-2">fullscreen</i>
                </a>
            </div>
        </div>
        <header class="fs-header">
            <div class="d-flex justify-content-between">
                <img alt="Olive" src="{{ asset('img/olive-logo-grey.svg') }}" class="logo" />
                <a href="#" class="btn-floating btn-flat waves-effect" data-class="body" data-class-remove="fs-active">
                    <i class="material-icons">fullscreen_exit</i>
                </a>
            </div>
        </header>
        <div class="fs-container sortable">
            @foreach (
                [
                    'sozluk' => 'Sözlük',
                    'news' => 'Haber',
                    'google' => 'Google',
                    'twitter' => 'Twitter',
                    'youtube' => 'YouTube'
                ]
                as $key => $name
            )
                <div class="card">
                    <div class="card-content d-flex">
                        <a href="#" class="handle align-self-center btn-floating btn-flat mr-1">
                            <i class="material-icons">drag_handle</i>
                        </a>
                        <span class="card-title align-self-center">{{ $name }}</span>
                        <span class="align-self-center ml-auto d-flex">
                            <a href="#" class="align-self-center btn-floating btn-flat waves-effect" data-trigger="archive" data-name="{{ $key }}">
                                <i class="material-icons">archive</i>
                            </a>
                            <span>&nbsp;</span>
                            <a href="#" class="align-self-center btn-floating cyan darken-2" data-run="off" data-name="{{ $key }}">
                                <i class="material-icons">play_arrow</i>
                            </a>
                        </span>
                    </div>
                    <ul
                        class="collection collection-hoverable trend-collection hide"
                        data-method="post"
                        data-href="{{ route('trend.live.redis') }}"
                        data-callback="__trends"
                        data-module="{{ $key }}">
                        <li class="collection-item item hide" data-model>
                            <div class="d-flex">
                                <i class="material-icons align-self-center" data-name="arrow"></i>
                                <span class="rank align-self-center center-align" data-name="rank" style="width: 48px;"></span>
                                @if ($key == 'youtube')
                                    <img data-name="image" style="height: 24px;" class="align-self-center mr-1" />
                                @endif
                                <span class="align-self-center" data-name="title"></span>
                                <span class="align-self-center d-flex ml-auto">
                                    <div class="align-self-center" data-name="chart-parent"></div>
                                    <span>&nbsp;</span>
                                    <a href="#" class="btn-floating btn-small btn-flat waves-effect align-self-center dropdown-trigger" data-name="dropdown-trigger" data-target="dropdown-{{ $key }}">
                                        <i class="material-icons">more_vert</i>
                                    </a>
                                </span>
                            </div>
                            <ul class="dropdown-content" data-name="dropdown" id="dropdown-{{ $key }}">
                                <li>
                                    <a href="#" data-name="olive" target="_blank">Olive Sonuçları</a>
                                </li>
                                <li>
                                    <a href="#" data-name="google" target="_blank">Google Sonuçları</a>
                                </li>
                                @if ($key == 'youtube')
                                    <li>
                                        <a href="#" data-name="youtube-link" target="_blank">YouTube ile Aç</a>
                                    </li>
                                @elseif ($key == 'twitter')
                                    <li>
                                        <a href="#" data-name="twitter-link" target="_blank">Twitter Sonuçları</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                    <div class="nothing hide pb-1" data-id="nothing-{{ $key }}">
                        @component('components.nothing')
                            @slot('size', 'small')
                        @endcomponent
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
