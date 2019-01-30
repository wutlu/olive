<div class="card">
    <div class="card-content cyan darken-2 white-text">{{ $alert }}</div>
    <div class="card-content">
        <span class="card-title">Duygu Analizi</span>
        <canvas id="sentiment-chart"></canvas>

        @push('local.scripts')
            new Chart(document.getElementById('sentiment-chart'), {
                type: 'pie',
                data: {
                    labels: [
                       'Pozitif',
                       'Negatif',
                       'Nötr'
                    ],
                    datasets: [{
                        backgroundColor: [ '#0097a7', '#e53935', '#bdbdbd' ],
                        data: [
                            {{ $pos }},
                            {{ $neg }},
                            {{ $total - ($pos + $neg) }}
                        ]
                    }]
                },
                options: {
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
                }
            })
        @endpush
    </div>
</div>
