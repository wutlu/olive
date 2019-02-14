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

    for (var i = 1; i <= 4; i++) {
        __chart('#chart-' + i, {
            labels: [ '', '', '', '', '' ],
            datasets: [{
                backgroundColor: '#E8F5E9',
                borderColor: '#4CAF50',
                data: [ i, 2, 5, 4, 8-i ],
                tension: 0.1,
                borderWidth: 1,
                radius: 0
            }]
        })
    }

    for (var i = 5; i <= 8; i++) {
        __chart('#chart-' + i, {
            labels: [ '', '', '', '', '' ],
            datasets: [{
                backgroundColor: '#FFEBEE',
                borderColor: '#F44336',
                data: [ i, 2, 5, 4, 8-i ],
                tension: 0.1,
                borderWidth: 1,
                radius: 0
            }]
        })
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
        <ul class="collection">
            <li class="collection-item d-flex">
                <span class="rank align-self-center">1</span>
                <i class="material-icons green-text align-self-center">arrow_drop_up</i>
                <a href="#" class="align-self-center">KBÜ de İl protokolü ve öğretim üyelerinin ortak Kolaj Sergisi açıldı</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-1" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">2</span>
                <i class="material-icons green-text align-self-center">arrow_drop_up</i>
                <a href="#" class="align-self-center">Harran Üniversitesi ile TJK arasında işbirliği protokolü</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-2" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">3</span>
                <i class="material-icons green-text align-self-center">arrow_drop_up</i>
                <a href="#" class="align-self-center">Duvarı delip 120 bin liralık gümüş çaldılar</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-3" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">4</span>
                <i class="material-icons green-text align-self-center">arrow_drop_up</i>
                <a href="#" class="align-self-center">Milli Eğitim Bakanlığı Maarif Müfettişi Ali Yeni'den Ürkmezer'e Ziyaret</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-4" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">5</span>
                <i class="material-icons red-text align-self-center">arrow_drop_down</i>
                <a href="#" class="align-self-center">Ombudsmanlığın Dünü Bugünü Ve Yarını Çalıştayı</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-5" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">6</span>
                <i class="material-icons red-text align-self-center">arrow_drop_down</i>
                <a href="#" class="align-self-center">Hırsızların hedefi rögar kapakları</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-6" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">7</span>
                <i class="material-icons grey-text align-self-center">remove</i>
                <a href="#" class="align-self-center">Jet Fadıl 40 yıllık ünlü profesörü Peygamber torunuyum diye kandırmış!</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-7" width="64" height="32"></canvas>
                </div>
            </li>
            <li class="collection-item d-flex">
                <span class="rank align-self-center">8</span>
                <i class="material-icons grey-text align-self-center">remove</i>
                <a href="#" class="align-self-center">Billur Kalkavan kimdir? Kaç yaşında? Sevgilisi kim?</a>
                <div class="chart align-self-center ml-auto" style="width: 64px; height: 32px;">
                    <canvas id="chart-8" width="64" height="32"></canvas>
                </div>
            </li>
        </ul>
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
                        data: [ i, 2, 5, 4, 8-i ],
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
