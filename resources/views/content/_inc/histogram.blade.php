@php
 /**
  * Histogram için chart.js sayfaya import edilmeli.
  *
  * <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
  */
@endphp

@push('local.styles')
    [data-type=canvas] {
        line-height: 1px;
        height: 200px;
    }

    canvas,
    canvas {
        width: 100%;
    }
@endpush

@section('wildcard')
    <div class="z-depth-1">
        <ul class="tabs tabs-fixed-width tabs-transparent cyan darken-2 histogram-tabs">
            @foreach ($charts as $chart)
                <li class="tab">
                    <a class="@isset($chart['active']){{ 'active' }}@endisset d-flex" href="#{{ $chart['unique_id'] }}">{!! $chart['title'] !!}</a>
                </li>
            @endforeach
        </ul>

        @foreach ($charts as $chart)
            <div
                @isset($chart['active'])
                    class="active loaded load"
                @else
                    style="display: none;"
                @endisset
                id="{{ $chart['unique_id'] }}"
                data-method= "post"
                data-href="{{ route('content.histogram', [
                    'type' => $chart['type'],
                    'period' => $chart['period'],
                    'id' => $chart['id'],
                    'es_index_key' => @$chart['es_index_key']
                ]) }}"
                data-callback="__{{ $chart['period'] }}_chart"
                data-canvas-id="{{ $chart['unique_id'] }}-chart">
            @isset ($chart['info'])
                <p class="p-1 grey-text text-darken-2 d-flex">
                    <i class="material-icons align-self-center mr-1">info</i>
                    {{ $chart['info'] }}
                </p>
            @endisset
                <div data-type="canvas">
                    <canvas id="{{ $chart['unique_id'] }}-chart"></canvas>
                </div>
            </div>
        @endforeach
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

    var sentimentOptions = {
        title: {
            display: false
        },
        legend: { display: false },
        layout: {
            padding: 40
        },
        maintainAspectRatio: false
    };

    function __daily_chart(__, obj)
    {
        if (obj.status == 'ok' && obj.data)
        {
            var data = [];

            $.each(obj.data.buckets, function(key, o) {
                data.push(o.doc_count);
            })

            var daily_chart = new Chart(document.getElementById(__.data('canvas-id')), {
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
                options: sentimentOptions
            })
        }
        else
        {
            M.toast({ html: 'Grafik yüklenemedi.', 'classes': 'red darken-2' })
        }
    }

    function __hourly_chart(__, obj)
    {
        if (obj.status == 'ok' && obj.data)
        {
            var data = [];

            $.each(obj.data.buckets, function(key, o) {
                data.push(o.doc_count);
            })

            new Chart(document.getElementById(__.data('canvas-id')), {
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
                options: sentimentOptions
            })
        }
        else
        {
            M.toast({ html: 'Grafik yüklenemedi.', 'classes': 'red darken-2' })
        }
    }
@endpush
