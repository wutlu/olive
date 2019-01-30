@php
 /**
  * Histogram için chart.js sayfaya import edilmeli.
  *
  * <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
  */
@endphp

@push('local.styles')
    #weekly,
    #hourly {
        line-height: 1px;
    }

    #weekly canvas,
    #hourly canvas {
        height: 100px;
        width: 100%;
    }
@endpush

@section('wildcard')
	@php
        switch ($type)
        {
            case 'entry':
                $tab_title = 'Cevap Grafiği';
            break;

            case 'article':
                $tab_title = 'Haber Grafiği';
            break;

            case 'product':
                $tab_title = 'Benzer Ürün Grafiği';
            break;
        }
	@endphp
	<div class="z-depth-2">
        <ul class="tabs tabs-fixed-width tabs-transparent cyan darken-2 histogram-tabs">
            <li class="tab">
                <a class="active" href="#weekly">Haftalık {{ $tab_title }}</a>
            </li>
            <li class="tab">
                <a href="#hourly">Saatlik {{ $tab_title }}</a>
            </li>
        </ul>

        <div
            id="weekly"
            class="load loaded"
            data-method= "post"
            data-href="{{ route('content.histogram', [
                'type' => 'weekly',
                'es_index' => $index,
                'es_type' => $type,
                'es_id' => $id
            ]) }}"
            data-callback="__weekly_chart">
            <canvas id="weekly-chart"></canvas>
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

    function __weekly_chart(__, obj)
    {
        if (obj.status == 'ok')
        {
            var data = [];

            $.each(obj.data.aggregations.results.buckets, function(key, o) {
                data.push(o.doc_count);
            })

            var weekly_chart = new Chart(document.getElementById('weekly-chart'), {
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
