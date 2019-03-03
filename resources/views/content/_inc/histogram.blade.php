@php
 /**
  * Histogram için chart.js sayfaya import edilmeli.
  *
  * <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
  */
@endphp

@push('local.styles')
    #daily,
    #hourly {
        line-height: 1px;
        height: 100px;
    }

    #daily canvas,
    #hourly canvas {
        width: 100%;
    }
@endpush

@section('wildcard')
    <div class="z-depth-2">
        <ul class="tabs tabs-fixed-width tabs-transparent cyan darken-2 histogram-tabs">
            <li class="tab">
                <a class="active" href="#daily">{{ $tab_title }} (Günlük)</a>
            </li>
            <li class="tab">
                <a href="#hourly">{{ $tab_title }} (Saatlik)</a>
            </li>
        </ul>

        <div
            id="daily"
            class="load loaded"
            data-method= "post"
            data-href="{{ route('content.histogram', [
                'type' => 'daily',
                'es_index' => $index,
                'es_type' => $type,
                'es_id' => $id
            ]) }}"
            data-callback="__daily_chart">
            <canvas id="daily-chart"></canvas>
        </div>
        <div
            id="hourly"
            style="display: none;"
            data-method="post"
            data-href="{{ route('content.histogram', [
                'type' => 'hourly',
                'es_index' => $index,
                'es_type' => $type,
                'es_id' => $id
            ]) }}"
            data-callback="__hourly_chart">
            <canvas id="hourly-chart"></canvas>
        </div>
    </div>
@endsection

@push('local.scripts')
    $('.histogram-tabs').tabs({
        onShow: function(tab) {
            var loader = $('#' + tab.id);

            if (!loader.hasClass('loaded'))
            {
                loader.addClass('loaded')
                vzAjax(loader)
            }
        }
    })

    var options = {
        title: {
            display: false
        },
        legend: { display: false },
        scales: {
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
                top: 10,
                bottom: 0
            }
        },
        maintainAspectRatio: false
    };

    function __daily_chart(__, obj)
    {
        if (obj.status == 'ok')
        {
            var data = [];

            $.each(obj.data.aggregations.results.buckets, function(key, o) {
                data.push(o.doc_count);
            })

            var daily_chart = new Chart(document.getElementById('daily-chart'), {
                type: 'bar',
                data: {
                    labels: [
                        "Pazartesi",
                        "Salı",
                        "Çarşamba",
                        "Perşembe",
                        "Cuma",
                        "Cumartesi",
                        "Pazar"
                    ],
                    datasets: [
                        {
                            backgroundColor: '#0097a7',
                            data: data
                        }
                    ]
                },
                options: options
            })
        }
    }

    function __hourly_chart(__, obj)
    {
        if (obj.status == 'ok')
        {
            var data = [];

            $.each(obj.data.aggregations.results.buckets, function(key, o) {
                data.push(o.doc_count);
            })

            new Chart(document.getElementById('hourly-chart'), {
                type: 'bar',
                data: {
                    labels: [
                        "00:00",
                        "01:00",
                        "02:00",
                        "03:00",
                        "04:00",
                        "05:00",
                        "06:00",
                        "07:00",
                        "08:00",
                        "09:00",
                        "10:00",
                        "11:00",
                        "12:00",
                        "13:00",
                        "14:00",
                        "15:00",
                        "16:00",
                        "17:00",
                        "18:00",
                        "19:00",
                        "20:00",
                        "21:00",
                        "22:00",
                        "23:00"
                    ],
                    datasets: [
                        {
                            backgroundColor: '#0097a7',
                            data: data
                        }
                    ]
                },
                options: options
            })
        }
    }
@endpush
