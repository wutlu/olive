@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Kıyasla (beta)'
        ]
    ],
    'dock' => true,
    'wide' => true
])

@section('dock')
    <div class="switch mb-2">
        <label class="d-table pt-1 pb-1 ">
            <input type="checkbox" name="metric" value="on" />
            <span class="lever"></span>
            Saatlik
        </label>
    </div>

    <div class="input-field">
        <input type="date" name="start_date" id="start_date" value="{{ date('Y-m-d', strtotime('-2 day')) }}" class="validate" />
        <label for="start_date">1. Tarih</label>
    </div>
    <div class="input-field">
        <input type="date" name="end_date" id="end_date" value="{{ date('Y-m-d') }}" class="validate" />
        <label for="end_date">2. Tarih</label>
    </div>

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">save</i>
                Kayıtlı Aramalar
            </span>
        </div>
        <ul class="collection collection-unstyled load hide"
            id="savedSearches"
            data-href="{{ route('search.list') }}"
            data-callback="__saved_searches"
            data-method="post"
            data-loader="#ss-loader"
            data-nothing>
            <li class="collection-item model hide">
                <div class="d-flex">
                    <input type="color" data-name="color" class="align-self-center mr-1" />
                    <label class="module-label align-self-center">
                        <input name="searches" data-multiple="true" type="checkbox" />
                        <span data-name="name"></span>
                    </label>
                </div>
            </li>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'Veri karşılaştırmak için 2 kayıtlı aramanızın olması gerekiyor.<br />Lütfen öncelikle <a class="blue-grey-text" href="'.route('search.dashboard').'">Arama Motoru</a> ile 2 arama kaydedin.')
                @endcomponent
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'ss-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <div class="input-field">
        <select name="currency" id="currency">
            <option value="">Birim Ekle</option>
            <option value="USD">Dolar</option>
            <option value="EUR">Euro</option>
            <option value="BTC">Bitcoin</option>
        </select>
    </div>

    <div class="d-flex">
        <div class="input-field flex-fill">
            <select data-name="normalize" name="normalize_1" id="normalize_1">
                <option value="">Yok</option>
            </select>
            <label>Normalize</label>
        </div>
        <span class="align-self-center p-1">-</span>
        <div class="input-field flex-fill">
            <select data-name="normalize" name="normalize_2" id="normalize_2">
                <option value="">Yok</option>
            </select>
        </div>
    </div>

    <br />

    <button
        type="button"
        class="btn blue-grey darken-2 btn-large waves-effect hide json"
        data-name="run"
        data-include="searches,start_date,end_date,metric,currency,normalize_1,normalize_2"
        data-href="{{ route('compare.process') }}"
        data-method="post"
        data-callback="__compare">Kıyasla</button>
@endsection

@section('content')
    <div class="olive-alert info">
        <div class="anim"></div>
        <h4 class="mb-2">Veri Kıyasla</h4>
        <p>Sağ menüden en az 2 arama seçin ve kıyaslamayı başlatın.</p>
    </div>
    <div class="card hide mb-1" id="chart-card">
        <div class="card-content"></div>
    </div>
    <div class="card hide mb-1" id="normalize-card">
        <div class="card-content"></div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/apex.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    $('select').formSelect()

    function hourly()
    {
        var metric = $('input[name=metric]:checked');
        var end_date = $('input[name=end_date]');

        if (metric.length)
        {
            end_date.parent('.input-field').addClass('hide')
        }
        else
        {
            end_date.parent('.input-field').removeClass('hide')
        }
    }

    $(document).on('change', 'input[name=metric]', function() {
        hourly()
    })

    hourly()

    function __compare(__, obj)
    {
        var alert = $('.olive-alert');

        var chart_card = $('#chart-card');
            chart_card.addClass('hide')
            chart_card.find('#chart').remove()

        var normalize_card = $('#normalize-card');
            normalize_card.addClass('hide')
            normalize_card.find('#normalize-chart').remove()

        if (obj.status == 'ok')
        {
            alert.addClass('hide')

            var options = {
                chart: {
                    height: 440,
                    type: 'line',
                    toolbar: {
                        show: true,
                        tools: {
                            download: '<i class="material-icons">save</i>'
                        }
                    }
                },
                dataLabels: { enabled: true },
                colors: [],
                series: obj.datas,
                grid: {
                    borderColor: '#f0f0f0',
                    row: { opacity: 0 }
                },
                markers: { size: 6 },
                xaxis: { categories: obj.categories },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                }
            }

            $('input[data-name=color].active').each(function(key, item) {
                var item = $(item);

                if (item.closest('.collection-item').find('input[type=checkbox]:checked').length)
                {
                    options.colors.push(item.val())
                }
            })

            var currency = $('select[name=currency]');

            if (currency.val())
            {
                options['yaxis'] = [];

                $.each($('input[name=searches]:checked'), function(key, item) {
                    options['yaxis'].push(
                        {
                            labels: {
                                style: { color: '#333' }
                            },
                            title: {
                                text: $(this).next('[data-name=name]').html()
                            }
                        }
                    )
                })

                options['yaxis'].push(
                    {
                        opposite: true,
                        labels: {
                            style: { color: '#ccc' }
                        },
                        title: {
                            text: currency.val(),
                            style: { color: '#ccc' }
                        },
                        max: obj.datas[obj.datas.length-1].max,
                        min: obj.datas[obj.datas.length-1].min
                    }
                )
            }

            $.each(obj.datas, function(key, o) {
                if (o.color)
                {
                    options.colors.push(o.color)
                }
            })

            $('#chart-card').removeClass('hide').children('.card-content').append($('<div />', { 'id': 'chart' }))

            var chart = new ApexCharts(document.querySelector('#chart'), options);
                chart.render()

            if (obj.normalized)
            {
                var normalize_options = {
                    chart: {
                        height: 440,
                        type: 'line',
                        toolbar: {
                            show: true,
                            tools: {
                                download: '<i class="material-icons">save</i>'
                            }
                        }
                    },
                    dataLabels: { enabled: true },
                    series: [ obj.normalized ],
                    grid: {
                        borderColor: '#f0f0f0',
                        row: { opacity: 0 }
                    },
                    markers: { size: 6 },
                    yaxis: [
                        {
                            labels: {
                                style: { color: '#333' }
                            },
                            title: {
                                text: 'Normalize Fark (' + $('select[name=normalize_1] > option:selected').html() + ' - ' + $('select[name=normalize_2] > option:selected').html() + ')'
                            },
                            max: 1
                        }
                    ],
                    xaxis: { categories: obj.categories },
                    legend: { show: false },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    }
                }

                var currency = $('select[name=currency]');

                if (currency.val())
                {
                    normalize_options['series'].push(obj.datas[obj.datas.length-1])

                    normalize_options['yaxis'].push(
                        {
                            opposite: true,
                            labels: {
                                style: { color: '#ccc' }
                            },
                            title: {
                                text: currency.val(),
                                style: { color: '#ccc' }
                            },
                            max: obj.datas[obj.datas.length-1].max,
                            min: obj.datas[obj.datas.length-1].min
                        }
                    )
                }

                $('#normalize-card').removeClass('hide').children('.card-content').append($('<div />', { 'id': 'normalize-chart' }))

                var normalize_chart = new ApexCharts(document.querySelector('#normalize-chart'), normalize_options);
                    normalize_chart.render()
            }
        }
        else if (obj.status == 'failed')
        {
            alert.removeClass('info').addClass('warning')
            alert.children('h4').html(obj.reason.title)
            alert.children('p').html(obj.reason.text)
            alert.removeClass('hide')
        }
    }

    function __saved_searches(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            __.removeClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = __.children('[data-id=' + o.id + ']');
                    var option = $('<option />', {
                        'value': o.id,
                        'html': o.name
                    })

                    option.appendTo($('[data-name=normalize]'))

                    $('[data-name=normalize]').formSelect()

                    item = selector.length ? selector : item_model.clone();
                    item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                    item.find('input[name=searches]').val(o.id)
                    item.find('[data-name=name]').html(o.name)
                    item.find('[data-name=color]').addClass('active').val('#'+Math.floor(Math.random()*16777215).toString(16))

                    item.appendTo(__)
                })

                $('[data-name=run]').removeClass('hide')
            }
        }
    }
@endpush
