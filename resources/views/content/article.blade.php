@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@include('content._inc.histogram', [
    'index' => $es->index,
    'type' => $es->type,
    'id' => $document['_source']['site_id'],
])

@section('content')
    <div class="card">
        <div class="card-content">
            <ul class="item-group m-0">
                <li class="item">
                    <small class="grey-text">Alınan Haber</small>
                    <p class="mb-0">{{ $data['total']->data['count'] }}</p>
                </li>
                <li class="item">
                    <small class="grey-text">En Çok Tekrar Eden Kelimeler</small>
                </li>
                <li class="item">
                    <small class="grey-text">Pozitif İçerik</small>
                    <p class="mb-0">{{ $data['pos']->data['count'] }}</p>
                </li>
                <li class="item">
                    <small class="grey-text">Duygu Analizi</small>
                    <canvas id="sentiment-chart"></canvas>
                </li>
            </ul>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    var diskOptions = {
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
        }
    };

    var sentiment_chart = $("#sentiment-chart");

    $(document).ready(function() {
        sentiment_chart = new Chart(sentiment_chart, {
            type: 'pie',
            data: {
                datasets: [{
                    labels: [
                       'Pozitif',
                       'Negatif',
                       'Nötr'
                    ],
                    backgroundColor: [ '#aeea00', '#f44336', '#bdbdbd' ],
                    data: [
                        {{ $data['pos']->data['count'] }},
                        {{ $data['neg']->data['count'] }},
                        {{ $data['total']->data['count'] - ($data['pos']->data['count']+$data['neg']->data['count']) }}
                    ]
                }],
                legend: {
                    position: "right",
                    labels: {
                        usePointStyle: true
                    }
                }
            },
            options: diskOptions
        })
    })
@endpush
