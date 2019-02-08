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

    @if (session('timeout'))
        M.toast({ html: 'İşlem zaman aşımına uğradı! Lütfen tekrar deneyin.', classes: 'red' })
    @endif
@endpush

@section('content')
    <div class="step-title">
        <span class="step">1</span>
        <span class="text">Plan Seçimi</span>
    </div>


            @if (@$discountDay)
                <p class="center-align grey-text text-darken-2">Hemen şimdi üye olun ve bugüne özel <span class="chip">{{ $discountDay->discount_rate }}%</span> indirim kuponuna anında sahip olun.</p>
            @endif

            <div class="plans">
                @foreach (config('plans') as $key => $plan)
                    <div class="plan {{ $plan['class'] }}" data-title="{{ $plan['name'] }}">
                        <ul>
                            @foreach ($plan['properties'] as $item)
                                <li class="d-flex justify-content-between">
                                    <span>{{ $item['text'] }}</span>

                                    @if ($item['value'] === true)
                                        <i class="material-icons green-text">check</i>
                                    @elseif ($item['value'] === false)
                                        <i class="material-icons red-text">close</i>
                                    @elseif (is_integer($item['value']))
                                        <span>{{ $item['value'] }}</span>
                                    @else
                                        <i class="material-icons teal-text">streetview</i>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="price teal white-text">
                            <div class="d-flex justify-content-between">
                                <span class="new">
                                    <small>1 Ay</small>
                                    {{ config('formal.currency') }} {{ $plan['price'] }}
                                </span>
                                <span class="new">
                                    <small>12+ Ay</small>
                                    {{ config('formal.currency') }} {{ $plan['price'] - ($plan['price'] / 100 * config('formal.discount_with_year')) }}
                                </span>
                            </div>
                            <small>Vergiler dahil değildir.</small>
                        </div>

                        @if ($plan['price'])
                            <div class="buy center-align">
                                @isset ($plan['buy'])
                                    <a href="{{ route('organisation.create.details', [ $key ]) }}" class="orange-text">Planı Seç</a>
                                @else
                                    <span class="grey-text">Stokta Yok</span>
                                @endisset
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
@endsection
