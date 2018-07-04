@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@push('local.scripts')
$('.tabs').tabs();
@endpush

@section('content')
    @if (auth()->user()->verified)
        @if (@auth()->user()->organisation_id)

        @else
            @push('local.scripts')

                @if (session('timeout'))
                    M.toast({ html: 'İşlem zaman aşımına uğradı! lütfen tekrar deneyin.', classes: 'red' })
                @endif

            @endpush

            @if (session('plan'))
                @php
                $plan = config('plans')[session('plan')];
                @endphp
            @endif

            <div class="step-title {{ $step == 1 ? 'active' : '' }}">
                <span class="step">1</span>
                <span class="text">Plan Seçimi</span>
            </div>

            @if ($step == 1)
            <div class="card card-unstyled">
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
                        <p class="center-align grey-text">Yıllık ödemelerde anında <span class="chip">{{ $plan['yearly_discount_rate'] }}%</span> indirim alın.</p>
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
                                            <span class="badge grey lighten-2">{{ $item['value'] }}</span>
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
                            <a href="{{ route('start', [ 'step' => 2, 'plan' => $key ]) }}" class="btn-flat btn-large waves-effect">Plan Seç</a>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @else
                <h3 class="center-align">₺ {{ $plan['price'] }}<sup>.00</sup> <sub>/ Ay</sub></h3>
                <p class="center-align grey-text">Yıllık ödemelerde anında <span class="chip">{{ $plan['yearly_discount_rate'] }}%</span> indirim alın.</p>
            @endif

            <div class="step-title {{ $step == 2 ? 'active' : '' }}">
                <span class="step">2</span>
                <span class="text">Ödeme İşlemi</span>
            </div>

            @if ($step == 2)
                {{ session('plan') }}
                @php
                print_r($plan);
                @endphp

                <div class="center-align">
                    <a href="{{ route('start', [ 'step' => 3, 'plan' => session('plan') ]) }}" class="btn-flat btn-large waves-effect">Ödemeyi Yap</a>
                </div>
            @endif

            <div class="step-title {{ $step == 3 ? 'active' : '' }}">
                <span class="step">3</span>
                <span class="text">Bitti</span>
            </div>

            @if ($step == 3)
                {{ session('plan') }}
                @php
                print_r($plan);
                @endphp
            @endif
        @endif
    @else
    <div class="rush-alert">
        <i class="material-icons">email</i>
        <h5>Onay Gerekiyor :(</h5>
        <p>Organizasyon oluşturabilmek için e-posta adresinizi doğrulamanız gerekiyor.</p>
    </div>
    @endif
@endsection
