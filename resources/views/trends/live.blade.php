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

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
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
                        callback: function (value) { return (value / this.max * 100).toFixed(0) + '%'; }
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

    var newsTimer;

    function __news(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#news-loader').addClass('hide')

            var collection = $('#news-collection');
            var model = collection.children('[data-model]')

            $.each(obj.data, function(rank, o) {
                var item = collection.children('#news-item-' + rank).length ? $('#news-item-' + rank) : model.clone()
                                .removeAttr('data-model')
                                .removeClass('hide')
                                .attr('id', 'news-item-' + rank);
                    item.find('[data-name=rank]').html(rank)
                    item.find('[data-name=title]').html(o.title)

                    item.appendTo(collection)

                __chart(item.find('[data-name=chart]'), {
                    labels: o.chart,
                    datasets: [{
                        backgroundColor: '#E8F5E9',
                        borderColor: '#4CAF50',
                        data: o.chart,
                        tension: 0.1,
                        borderWidth: 1,
                        radius: 0
                    }]
                })
            })

            window.clearTimeout(newsTimer)

            newsTimer = window.setTimeout(function() {
                vzAjax($('#news-collection'))
            }, 1000)
        }
    }
@endpush

@section('wildcard')
    <div class="z-depth-2 pt-1 pb-1">
        <div class="container">
            <small class="grey-text">Twitter</small>
            <div class="owl-carousel owl-twitter">
                @for ($i = 0; $i <= 50; $i++)
                    <a href="#" class="p-1 d-table">Örnek Trend {{ $i }}</a>
                @endfor
            </div>
            <small class="grey-text">Google</small>
            <div class="owl-carousel owl-google">
                @for ($i = 0; $i <= 50; $i++)
                    <a href="#" class="p-1 d-table">Örnek Trend {{ $i }}</a>
                @endfor
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-content">
            <span class="card-title">Sözlük</span>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <span class="card-title">Medya</span>
        </div>
        <ul
            id="news-collection"
            class="collection load"
            data-method="post"
            data-href="{{ route('trend.live.redis', 'news') }}"
            data-callback="__news">
            <li class="collection-item hide" data-model>
                <div class="d-flex">
                    <span class="rank align-self-center" data-name="rank"></span>
                    <i class="material-icons align-self-center" data-name="arrow"></i>
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
@endsection

@section('subcard')
<div class="container">
    <div class="card-columns">
        @for ($i = 1; $i <= 48; $i++)
            @push('local.scripts')
                __chart('#v-chart-{{ $i }}', {
                    labels: [ '', '', '', '', '' ],
                    datasets: [{
                        backgroundColor: '#FFEBEE',
                        borderColor: '#F44336',
                        data: [ 1, 2, 5, 4, 8 ],
                        tension: 0.1,
                        borderWidth: 1,
                        radius: 0
                    }]
                })
            @endpush

            <div class="card">
                <div class="card-image cyan">
                    <img src="https://i.ytimg.com/vi/bS3uSzk4VwY/maxresdefault.jpg" alt="..." style="opacity: .6;" />
                    <span class="card-title card-title-small">Oğuzhan Uğur'la P!NÇ: Selçuk Aydemir, Burak Aksak, Doğan Kabak, 14 Şubat, Doğum Günü, Homofobi</span>
                </div>
                <div class="card-action d-flex">
                    <a href="#" class="btn-flat waves-effect grey-text text-darken-2">
                        <i class="material-icons">show_chart</i>
                    </a>
                    <a href="#" class="btn-flat waves-effect grey-text text-darken-2">
                        <i class="material-icons">link</i>
                    </a>
                    <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                        <canvas id="v-chart-{{ $i }}" width="64" height="32"></canvas>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>
@endsection

@push('local.scripts')

@endpush
