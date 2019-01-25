@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sistem İzleme'
        ],
        [
            'text' => '🐞 Sunucu Bilgisi'
        ]
    ]
])

@push('local.styles')
    .card-metric {
        padding: 2rem 2rem 0;
    }
    .card-metric > .card-metric-title {
        font-size: 14px;
    }
    .card-metric > .card-metric-value {
        font-size: 24px;
    }
    .card-metric > .card-metric-percent {
        font-size: 14px;
        color: #999;
    }
@endpush

@push('local.scripts')
    var callbackTimer;

    function __callback(__, obj)
    {
        if (obj.status == 'ok')
        {
            var ramPer = 100-(100/obj.data.ram.total.size*obj.data.ram.free.size);
            var cpuPer = obj.data.cpu.usage;

            addData(ramChart, '', ramPer)
            addData(cpuChart, '', cpuPer)

            $('[data-id=ram-per]').html(ramPer.toFixed(2) + ' %')
            $('[data-id=ram-total]').html(obj.data.ram.total.readable)

            $('[data-id=cpu-per]').html(cpuPer.toFixed(2) + ' %')
            $('[data-id=cpu-core]').html(obj.data.cpu.core + ' CORE')

            window.clearTimeout(callbackTimer)

            callbackTimer = setTimeout(function() {
                vzAjax($('[data-callback=__callback]'))
            }, 1000)
        }
    }
@endpush

@section('content')
    <div
        class="load"
        data-callback="__callback"
        data-href="{{ route('admin.monitoring.server') }}"
        data-method="post"></div>
    <div class="row">
        <div class="col s12 xl6">
            <div class="card">
                <div class="card-metric">
                    <div class="card-metric-title">RAM</div>
                    <div class="card-metric-value">
                        <span data-id="ram-total">-</span>
                    </div>
                    <div class="card-metric-percent" data-id="ram-per">-</div>
                </div>
                <div class="card-chart">
                    <canvas id="ram-chart" height="128"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-metric">
                    <div class="card-metric-title">CPU</div>
                    <div class="card-metric-value">
                        <span data-id="cpu-core">-</span>
                    </div>
                    <div class="card-metric-percent" data-id="cpu-per">-</div>
                </div>
                <div class="card-chart">
                    <canvas id="cpu-chart" height="128"></canvas>
                </div>
            </div>
        </div>
        <div class="col s12 xl6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title mb-0">Disk Kullanımı</span>
                </div>
                @if (count($disks))
                    <div class="card-tabs">
                        <ul class="tabs tabs-fixed-width">
                            @foreach ($disks as $key => $disk)
                                <li class="tab">
                                    <a class="active" href="#disk-{{ $key }}">
                                        <i class="material-icons">storage</i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-content grey lighten-4">
                        @foreach ($disks as $key => $disk)
                            <div id="disk-{{ $key }}">
                                <canvas id="hdd-chart-{{ $key }}"></canvas>
                            </div>

                            @push('local.scripts')
                                var hddChart_{{ $key }} = $("#hdd-chart-{{ $key }}");

                                $(document).ready(function() {
                                    hddChart_{{ $key }} = new Chart(hddChart_{{ $key }}, {
                                        type: 'doughnut',
                                        data: {
                                            labels: [
                                                'Kullanılan {{ Term::humanFileSize($disk['used']->size)->readable }}',
                                                'Boş {{ Term::humanFileSize($disk['free']->size)->readable }}'
                                            ],
                                            datasets: [{
                                                backgroundColor: [ '#c00', '#8bc34a' ],
                                                data: [ {{ $disk['used']->size }}, {{ $disk['free']->size }} ]
                                            }]
                                        },
                                        options: diskOptions
                                    })
                                })
                            @endpush
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
    });

    var options = {
        legend: { display: false },
        scales: {
            xAxes: [
                { display: false }
            ],
            yAxes: [{
                display: false,
                ticks: {
                    min: 0,
                    max: this.max,
                    callback: function (value) {
                        return (value / this.max * 100).toFixed(0) + '%';
                    }
                }
            }]
        },
        layout: {
            padding: {
                top: 20,
                bottom: 0
            }
        },
        tooltips: {
             enabled: false
        },
        maintainAspectRatio: false
    };

    var diskOptions = {
        layout: {
            padding: {
                top: 10,
                bottom: 10
            }
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                        return previousValue + currentValue;
                    });

                    var currentValue = dataset.data[tooltipItem.index];
                    var percentage = Math.floor(((currentValue/total) * 100)+0.5);

                    return percentage + '%';
                }
            }
        },
        cutoutPercentage: 80
    };

    var ramChart = $('#ram-chart');
    var cpuChart = $('#cpu-chart');

    $(document).ready(function() {
        ramChart = new Chart(ramChart, {
            type: 'line',
            data: {
                labels: [ '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '' ],
                datasets: [{
                    backgroundColor: '#1976d2',
                    borderColor: '#1976d2',
                    data: [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 ],
                    tension: 0.1,
                    borderWidth: 1,
                    radius: 0
                }]
            },
            options: options
        })
        cpuChart = new Chart(cpuChart, {
            type: 'line',
            data: {
                labels: [ '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '' ],
                datasets: [{
                    backgroundColor: '#616161',
                    borderColor: '#616161',
                    data: [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 ],
                    tension: 0.1,
                    borderWidth: 1,
                    radius: 0
                }]
            },
            options: options
        })
    })

    function addData(chart, label, data) {
        chart.data.labels.splice(0, 1)
        chart.data.labels.push(label)
        chart.data.datasets.forEach((dataset) => {
            dataset.data.splice(0, 1)
            dataset.data.push(data)
        })

        chart.update()
    }
@endpush
