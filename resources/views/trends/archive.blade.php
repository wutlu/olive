@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Trend Arşivi'
        ]
    ],
    'footer_hide' => true,
    'dock' => true,
    'wide' => true
])

@push('local.scripts')
    $('select').formSelect()

    $(document).on('change', 'select[name=day]', function() {
        var month = $('select[name=month]');
        var year = $('select[name=year]');

        if (!month.val())
        {
            month.val(1)
            month.formSelect()
        }

        if (!year.val())
        {
            year.val({{ date('Y') }})
            year.formSelect()
        }
    })

    $(document).on('change', 'select[name=month]', function() {
        var day = $('select[name=day]');
        var year = $('select[name=year]');

        if (!year.val())
        {
            year.val({{ date('Y') }})
            year.formSelect()
        }

        if (!$(this).val())
        {
            day.val('')
            day.formSelect()
        }
    })
@endpush

@section('wildcard')
    <div class="card wild-background">
        <div class="pl-1">
            <span class="wildcard-title">Trend Arşivi</span>
        </div>
    </div>
@endsection

@section('dock')
    @include('trends._menu', [ 'active' => 'archive' ])

    <form method="get" action="{{ route('trend.archive') }}">
        <div class="card card-unstyled">
            <div class="card-content">
                <div class="d-flex">
                    <div class="input-field">
                        <select name="day" id="day">
                            <option value="" selected>-</option>
                            @for ($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ $request->day == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <label for="day">Gün</label>
                    </div>
                    <div class="input-field">
                        <select name="month" id="month">
                            <option value="" selected>-</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $request->month == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <label for="month">Ay</label>
                    </div>
                    <div class="input-field">
                        <select name="year" id="year">
                            @for ($i = date('Y'); $i >= (date('Y')-2); $i--)
                                <option value="{{ $i }}" {{ $request->year == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <label for="year">Yıl</label>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="input-field">
                    <select name="type" id="type">
                        <option value="" {{ $request->type ? '' : 'selected' }}>Tümü</option>
                        <option value="monthly" {{ $request->type == 'monthly' ? 'selected' : '' }}>Aylık</option>
                        <option value="weekly" {{ $request->type == 'weekly' ? 'selected' : '' }}>Haftalık</option>
                        <option value="daily" {{ $request->type == 'daily' ? 'selected' : '' }}>Günlük</option>
                        <option value="hourly" {{ $request->type == 'hourly' ? 'selected' : '' }}>Saatlik</option>
                    </select>
                    <label for="type">Tür</label>
                </div>
            </div>
            <div class="card-content">
                <div class="input-field">
                    <select name="module" id="module">
                        <option value="" {{ $request->module ? '' : 'selected' }}>Tümü</option>
                        @foreach ($modules as $key => $module)
                            <option value="{{ $key }}" data-icon="{{ $module['icon'] }}" {{ $request->module == $key ? 'selected' : '' }}>{{ $module['name'] }}</option>
                        @endforeach
                    </select>
                    <label for="module">Modül</label>
                </div>
            </div>
            <div class="card-content">
                <button type="submit" class="btn blue-grey waves-effect">Süz</button>
            </div>
        </div>
    </form>

    <br />

    <div class="yellow-text text-darken-2">
        @component('components.alert')
            @slot('icon', 'info')
            @slot('text', 'Trend Arşivinde bulunan değerler, içeriklerin arşivlendiği andan itibaren kayıt altına alınır. Daha sonra gelecek değerler ilgili tarih için güncellenmez.')
        @endcomponent
    </div>
@endsection

@section('content')
    <div class="card card-unstyled">
        @if (count($data))
            <ul class="collection collection-unstyled collection-hoverable">
                @foreach ($data as $item)
                    <li class="collection-item d-flex">
                        <img align="{{ $item->module }}" src="{{ $modules[$item->module]['icon'] }}" style="width: 24px; height: 24px;" class="align-self-center mr-1" />
                        <span class="d-table">
                            <a href="{{ route('trend.archive.view', $item->id) }}" class="align-self-center d-table">{{ $modules[$item->module]['name'] }}</a>
                            <span class="grey-text">{{ $item->group }}</span>
                        </span>
                        <span class="ml-auto d-table">{{ date('d.m.Y', strtotime($item->created_at)) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="card-content">
                @component('components.nothing')@endcomponent
            </div>
        @endif

        @if ($data->total() > $pager)
            <span class="d-table mx-auto">{!! $data->appends([
                'day' => $request->day,
                'month' => $request->month,
                'year' => $request->year,
                'type' => $request->type,
                'module' => $request->module
            ])->links('vendor.pagination.materializecss') !!}</span>
        @endif
    </div>
@endsection
