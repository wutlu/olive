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
    <div class="input-field">
        <input type="date" name="start_date" id="start_date" value="{{ date('Y-m-d', strtotime('-2 day')) }}" class="validate" />
        <label for="start_date">Başlangıç Tarihi</label>
    </div>
    <div class="input-field">
        <input type="date" name="end_date" id="end_date" value="{{ date('Y-m-d') }}" class="validate" />
        <label for="end_date">Bitiş Tarihi</label>
    </div>

    <ul id="date-menu" class="dropdown-content">
        <li>
            <a
                href="#"
                class="collection-item waves-effect"
                data-update-click
                data-input="input[name=end_date]"
                data-focus="input[name=start_date]"
                data-input-value="{{ date('Y-m-d') }}"
                data-value="{{ date('Y-m-d') }}">Bugün (Grafik Alınabilir)</a>
        </li>
        @if ($organisation->historical_days >= 1)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d', strtotime('-1 day')) }}"
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Dün (Grafik Alınabilir)</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 2)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Son 2 Gün (Grafik Alınabilir)</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 7)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-7 day')) }}">Son 7 Gün (Grafik Alınabilir)</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 14)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-14 day')) }}">Son 14 Gün (Grafik Alınabilir)</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 30)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-30 day')) }}">Son 30 Gün</a>
            </li>
        @endif
    </ul>

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
                <label class="module-label">
                    <input name="searches" data-multiple="true" type="checkbox" />
                    <span data-name="name"></span>
                </label>
            </li>
            <li class="nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'Veri karşılaştırmak için 2 kayıtlı aramanızın olması gerekiyor.<br />Lütfen öncelikle <a class="blue-grey-text" href="'.route('search.dashboard').'">Arama Motoru</a> ile 2 kayıtlı arama oluşturun.')
                @endcomponent
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'ss-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
    <button
        type="button"
        class="btn blue-grey darken-2 btn-large waves-effect hide json"
        data-name="run"
        data-include="searches,start_date,end_date"
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
    <div id="chart"></div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/apex.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    function __compare(__, obj)
    {
        var alert = $('.olive-alert');

        if (obj.status == 'ok')
        {
            alert.addClass('hide')

            var options = {
                chart: {
                    height: 350,
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
                    row: { colors: [ '#f0f0f0' ], opacity: 0.2 }
                },
                markers: { size: 6 },
                yaxis: { min: 0 },
                xaxis: { categories: obj.categories },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                stroke: {
                    width: 1     
                }
            }

            var chart = new ApexCharts(document.querySelector('#chart'), options);
                chart.render()
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
                    var selector = __.children('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('input[name=searches]').val(o.id)
                        item.find('[data-name=name]').html(o.name)

                        item.appendTo(__)
                })

                $('[data-name=run]').removeClass('hide')
            }
        }
    }
@endpush
