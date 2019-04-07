<div class="card mb-1 pb-1">
    <div class="card-content">
        <span class="card-title">Duygu Grafiği</span>
    </div>
    <canvas id="sentiment-chart"></canvas>
</div>

@push('local.scripts')
    new Chart(document.getElementById('sentiment-chart'), {
        type: 'pie',
        data: {
            labels: [
               'Pozitif',
               'Negatif',
               'Nötr'
            ],
            datasets: [
                {
                    backgroundColor: [ '#0097a7', '#e53935', '#bdbdbd' ],
                    data: [
                        {{ intval($pos*100  ) }},
                        {{ intval($neg*100  ) }},
                        {{ intval($neu*100  ) }}
                    ]
                }
            ]
        },
        options: {
            title: { display: false },
            legend: { display: false },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                            return previousValue + currentValue;
                        });

                        var currentValue = dataset.data[tooltipItem.index];
                        var percentage = Math.floor(((currentValue/total) * 100));

                        return percentage + '%';
                    }
                }
            }
        }
    })
@endpush
