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
<div class="row">
    <div class="col m12 xl12">
        <div class="card">
            <div class="card-content">
                <a href="{{ $document['_source']['url'] }}" class="card-title d-flex" target="_blank">
                    <i class="material-icons mr-1">insert_link</i>
                    <span>{{ $document['_source']['title'] }}</span>
                </a>
                <div class="markdown">
                    {!! Term::markdown($document['_source']['description']) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col m12 xl6">
        <div class="card">
            <div class="card-content">
                <span class="card-title d-table">Sitede Sık Kullanılan Kelimeler</span>
                @forelse (@$data['keywords'] as $key => $word)
                    <span class="chip">{{ $key }}</span>
                @empty
                    <span class="chip">Tespit Edilemedi</span>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col m12 xl6">
        <div class="card">
            <div class="card-content teal white-text">İlgili siteden toplam {{ $data['total']->data['count'] }} içerik alındı. Sayfadaki istatistik verileri alınan veriler üzerinden gerçekleştirilmiştir.</div>
            <div class="card-content">
                <span class="card-title">Duygu Analizi</span>
                <canvas id="sentiment-chart"></canvas>
            </div>
        </div>
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
                labels: [
                   'Pozitif',
                   'Negatif',
                   'Nötr'
                ],
                datasets: [{
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
