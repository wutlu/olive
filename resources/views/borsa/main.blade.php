@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'breadcrumb' => [
        [
            'text' => 'Kalabalığın Düşüncesi'
        ]
    ],
    'help' => 'help()'
])

@push('local.scripts')
    function help()
    {
        return modal({
            'id': 'info',
            'body': 'İnternet ortamından elde ettiğimiz verileri düzenli olarak sorgulayarak, hisselere yapılan yorumları olumlu/al ve olumsuz/sat bir şekilde inceliyoruz. Elde ettiğimiz sonuçları bu modül ile rahatlıkla inceleyebilirsiniz.',
            'title': 'Kalabalığın Sesi',
            'size': 'modal-medium',
            'options': {},
            'footer': [
               $('<a />', {
                   'href': '#',
                   'class': 'modal-close waves-effect btn-flat',
                   'html': buttons.ok
               })
            ]
        })
    }
@endpush

@section('content')
    <div class="d-flex justify-content-end mt-1 mb-1">
        <label class="mr-1 align-self-center">
            <input value="xu030-bist-30" name="group" type="radio" data-update checked />
            <span>BİST30</span>
        </label>
        <label class="mr-1 align-self-center">
            <input value="xu100-bist-100" name="group" type="radio" data-update />
            <span>BİST100</span>
        </label>
        <button
            type="button"
            class="btn-flat waves-effect json"
            data-href="{{ route('borsa.graph') }}"
            data-method="post"
            data-callback="__graph"
            data-include="lot,group">GRAFİK</button>
    </div>
    <div class="card card-unstyled">
        <div class="card-content hide" id="chart-element"></div>
        <div class="card-table">
            <input type="hidden" name="sk" value="name" />
            <input type="hidden" name="sv" value="asc" />
            <table
                class="highlight table-loading load"
                data-href="{{ route('borsa.data') }}"
                data-method="post"
                data-callback="__fill"
                data-callbefore="__before"
                data-include="sk,sv,group">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="d-flex" data-sort="name">
                                <span class="align-self-center">Hisse</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex" data-sort="hour">
                                <span class="align-self-center">Saat</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="d-flex justify-content-end" data-sort="value">
                                <span class="align-self-center">Değer</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="buy">
                                <span class="align-self-center">Alış</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="sell">
                                <span class="align-self-center">Satış</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="diff">
                                <span class="align-self-center">Fark</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="max">
                                <span class="align-self-center">Maks</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="min">
                                <span class="align-self-center">Min</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="lot">
                                <span class="align-self-center">Lot</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="tl">
                                <span class="align-self-center">TL</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="total_pos">
                                <span class="align-self-center">Al Dediler</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th class="hide-on-med-and-down">
                            <a href="#" class="d-flex justify-content-end" data-sort="total_neg">
                                <span class="align-self-center">Sat Dediler</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="d-flex justify-content-end" data-sort="pos_neg">
                                <span class="align-self-center">Al-Sat</span>
                                <i class="material-icons align-self-center">arrow_drop_down</i>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hide" data-model>
                        <td>
                            <label>
                                <input type="checkbox" data-name="lot" data-multiple="true" />
                                <span>
                                    <a href="#" class="blue-grey-text" data-name="name">-</a>
                                </span>
                            </label>
                        </td>
                        <td data-name="hour" class="hide-on-med-and-down">-</td>
                        <td data-name="value" class="right-align">-</td>
                        <td data-name="buy" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="sell" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="diff" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="max" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="min" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="lot" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="tl" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="total_pos" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="total_neg" class="hide-on-med-and-down right-align">-</td>
                        <td data-name="pos_neg" class="right-align">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/apex.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    function __graph(__, obj)
    {
        if (obj.status == 'ok')
        {
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
                series: obj.datas,
                grid: {
                    borderColor: '#f0f0f0',
                    row: { opacity: 0 }
                },
                markers: { size: 4 },
                yaxis: [
                    {
                        labels: {
                            style: { color: '#ef5350' }
                        },
                        title: {
                            text: 'Test',
                            style: { color: '#ef5350' }
                        },
                        max: 1
                    }
                ],
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

            if ($('input[name=lot]:checked').length == 1)
            {
                options['colors'] = [ '#ef5350', '#ccc' ];
                options['yaxis'].push(
                    {
                        opposite: true,
                        labels: {
                            style: { color: '#ccc' }
                        },
                        title: {
                            text: 'Deneme',
                            style: { color: '#ccc' }
                        },
                        max: obj.datas[obj.datas.length-1].max,
                        min: obj.datas[obj.datas.length-1].min,
                        offsetX: 10,
                        offsetY: 10
                    }
                )
            }

            $('#chart-element').children('#chart').remove()
            $('#chart-element').removeClass('hide').append($('<div />', { 'id': 'chart' }))

            var chart = new ApexCharts(document.querySelector('#chart'), options);
                chart.render()
        }
    }

    var sk = $('input[name=sk]');
    var sv = $('input[name=sv]');

    $(document).on('click', '[data-sort]', function() {
        var __ = $(this);

        sv.val(sv.val() == 'asc' ? 'desc' : 'asc')
        sk.val(__.data('sort'))

        vzAjax($('[data-callback=__fill]'))
    }).on('click', '[data-update]', function() {
        vzAjax($('[data-callback=__fill]'))
    })

    function __before(__)
    {
        __.addClass('table-loading')
    }

    function __fill(__, obj)
    {
        __.removeClass('table-loading')
        __.find('[data-model]:not(.hide)').remove()

        if (obj.status == 'ok')
        {
            $('[data-sort]').children('i.material-icons').html('arrow_drop_down')
            $('[data-sort=' + sk.val() + ']').children('i.material-icons').html(sv.val() == 'asc' ? 'arrow_drop_up' : 'arrow_drop_down')

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var item = __.find('[data-model].hide').clone();
                        item.removeClass('hide')

                        item.find('[data-name=lot]').val(o.name).attr('name', 'lot')
                        item.find('[data-name=name]').html(o.name).attr('href', '{{ route('search.dashboard') }}?q=' + o.name)
                        item.find('[data-name=hour]').html(o.hour)
                        item.find('[data-name=value]').html(o.value)
                        item.find('[data-name=buy]').html(o.buy)
                        item.find('[data-name=sell]').html(o.sell)
                        item.find('[data-name=diff]').html(o.diff).addClass(o.diff < 0 ? 'red-text' : 'green-text')
                        item.find('[data-name=max]').html(o.max)
                        item.find('[data-name=min]').html(o.min)
                        item.find('[data-name=lot]').html(number_format(o.lot))
                        item.find('[data-name=tl]').html(number_format(o.tl))
                        item.find('[data-name=total_pos]').html(o.total_pos ? number_format(o.total_pos) : '-')
                        item.find('[data-name=total_neg]').html(o.total_neg ? number_format(o.total_neg) : '-')
                        item.find('[data-name=pos_neg]').html(o.pos_neg ? number_format(o.pos_neg) : '-').addClass(o.diff < 0 ? 'red-text' : 'green-text')

                    __.children('tbody').append(item)
                })
            }
            else
            {
                M.toast({ 'html': 'Hiç borsa verisi bulunamadı!', 'classes': 'red' })
            }
        }
    }
@endpush
