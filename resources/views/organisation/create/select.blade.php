@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Organizasyon Oluştur'
        ],
        [
            'text' => 'Plan Seçimi'
        ]
    ]
])

@push('local.scripts')
$('.tabs').tabs();
@endpush

@if (session('timeout'))
    @push('local.scripts')
        M.toast({ html: 'İşlem zaman aşımına uğradı! Lütfen tekrar deneyin.', classes: 'red' })
    @endpush
@endif

@section('content')
    <div class="step-title">
        <span class="step">1</span>
        <span class="text">Plan Seçimi</span>
    </div>

    <div class="card">
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width">
                @foreach (config('plans') as $key => $plan)
                <li class="tab">
                    <a href="#tab-{{ $key }}">{{ $plan['name'] }}</a>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="card-content">
            @foreach (config('plans') as $key => $plan)
            <div id="tab-{{ $key }}">
                @if ($plan['price'])
                    <h3 class="center-align">₺ {{ $plan['price'] }}<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="grey-text center-align">Vergiler dahil değildir.</p>
                @else
                    <h3 class="center-align">Ücretsiz!</h3>
                    <p class="center-align green-text">Mevcut Planınız</p>
                @endif

                <ul class="collection collection-unstyled collection-unstyled-hover">
                    @foreach ($plan['properties'] as $k => $item)
                    <li class="collection-item">
                        <div>
                            <span>
                                <p>
                                    {{ $item['text'] }}

                                    @if ($item['value'] === true)
                                    <i class="material-icons secondary-content green-text">check</i>
                                    @elseif ($item['value'] === false)
                                    <i class="material-icons secondary-content red-text">close</i>
                                    @elseif (is_integer($item['value']))
                                    <span class="badge">{{ $item['value'] }}</span>
                                    @else
                                    <i class="material-icons secondary-content teal-text">streetview</i>
                                    @endif
                                </p>
                                <p class="grey-text">{{ $item['details'] }}</p>
                                @if (is_string($item['value']))
                                <p class="teal-text">{{ $item['value'] }}</p>
                                @endif
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @if ($plan['price'])
                <div class="center-align">
                    <a href="{{ route('organisation.create.details', [ $key ]) }}" class="btn-flat btn-large waves-effect">Planı Seç</a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

@endsection
