@extends('layouts.app')

@section('content')
    <header id="main">
        <div class="parallax-container">
            <div class="parallax">
                <img src="{{ asset('img/bg.svg') }}" alt="background" />
            </div>

            <div class="container">
                <div class="row">
                    <div class="col">
                        <a href="#" id="logo">
                            <img src="{{ asset('img/olive-logo.svg') }}" alt="olive-logo" class="responsive-img" />
                        </a>

                        <p>@lang('global.header.lead-1')</p>
                        <p class="grey-text text-darken-2">@lang('global.header.lead-2')</p>
                        <p class="grey-text text-darken-2">@lang('global.header.lead-3')</p>

                        <a href="{{ route('user.login') }}" class="waves-effect btn-flat">@auth{{ 'Olive\'e Gidin'}}@else{{ 'Giriş Yapın' }}@endauth</a>

                        <div class="down-area center-align">
                            <a href="#" class="waves-effect btn-large btn-floating pulse grey darken-4">
                                <i class="material-icons white-text">arrow_downward</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/sepbg.svg') }}" alt="sep" />
        </div>

        <div class="container">
            <div class="d-table mx-auto">
                <span class="d-flex white-text" id="data-count">
                    <small class="align-self-center">Türkiye'nin en enlamlı büyük verisi.</small>
                </span>
            </div>
        </div>
    </div>

    <div class="parallax-container" id="more-step">
        <div class="parallax">
            <img src="{{ asset('img/bg-2.svg') }}" alt="grabg" />
        </div>

        <div class="container">
            <img id="vz-logo-top" src="{{ asset('img/veri.zone-logo-white.svg') }}" alt="veri.zone-logo" />
            <div class="item-group" id="features">
                <div class="item grey-text text-darken-2">
                    <i class="large material-icons analytics">poll</i>
                    <h5>Analiz</h5>
                    <ul>
                        <li>- Twitter Analizleri</li>
                        <li>- YouTube Analizleri</li>
                        <li>- Haber Siteleri Analizleri</li>
                        <li>- Sözlük Siteleri Analizleri</li>
                        <li>- E-ticaret Siteleri Analizleri</li>
                        <li>- ve dahası...</li>
                    </ul>
                </div>
                <div class="item grey-text text-darken-2">
                    <i class="large material-icons realtime">subject</i>
                    <h5>Gerçek Zamanlı Veri</h5>
                    <ul>
                        <li>- Anlık veri akışı.</li>
                        <li>- Duygusal akış.</li>
                        <li>- Tam veya kriterlere göre akış.</li>
                        <li>- Akış gruplandırma.</li>
                        <li>- Veri pinleme ve çıktı alma.</li>
                    </ul>
                </div>
                <div class="item grey-text text-darken-2">
                    <i class="large material-icons rotate">toys</i>
                    <h5>Araçlar</h5>
                    <ul>
                        <li>- İçerik takibi ve yorum raporları.</li>
                        <li>- Detaylama ile görünenden fazla bilgi.</li>
                        <li>- Anlamlı listeler elde etme.</li>
                        <li>- Duygu analizleri.</li>
                        <li>- Gerçek zamanlı veri apileri.</li>
                    </ul>
                </div>
                <div class="item grey-text text-darken-2">
                    <i class="large material-icons cloud">cloud</i>
                    <h5>Arşiv</h5>
                    <ul>
                        <li>- Kullanıcı tarafından veri elde edebilme.</li>
                        <li>- Elde edilen verilerin yasal süreçlerde barındırılması ve hızlı bir şekilde erişebilme.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/sepbg.svg') }}" alt="sep" />
        </div>

        @push('local.scripts')
            function __counter(__, obj)
            {
                if (obj.status == 'ok')
                {
                    __.html(obj.data.count)

                    setTimeout(function() {
                        vzAjax($('[data-id=loader]'))
                    }, 30000)
                }
            }
        @endpush

        <div class="container">
            <div class="d-table mx-auto">
                <span class="d-flex white-text" id="data-count">
                    <small class="align-self-center mr-1">Hızla büyüyen veritabanı ve</small>
                    <span class="align-self-center load" data-id="loader" data-href="{{ route('home.data.counter') }}" data-method="post" data-callback="__counter">0</span>
                    <i class="align-self-center material-icons">add</i>
                    <small class="align-self-center ml-1">veri.</small>
                </span>
            </div>
        </div>
    </div>

    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/obg.svg') }}" alt="obg" />
        </div>

        <div class="container">
            <h3 class="grey-text text-darken-2">planlar</h3>

            <div class="card with-bg">
                <div class="card-content center-align">
                    <p class="teal-text">Hemen ücretsiz bir şekilde üye olabilir ve hiçbir ücret ödemeden başlangıç paketinden faydalanabilirsiniz.</p>
                    <p class="grey-text text-darken-2">Tüm araçlardan faydalanabilmek için bir organizasyon satın almalı veya bir organizasyon'a dahil olmalısınız.</p>
                    <p class="grey-text text-darken-2">Yapacağınız yıllık ödemelerde anında {{ config('formal.discount_with_year') }}% indirim sağlıyoruz.</p>

                    @if (@$discountDay)
                        <p class="center-align grey-text text-darken-2">Hemen şimdi üye olun ve bugüne özel <span class="chip">{{ $discountDay->discount_rate }}%</span> indirim kuponuna anında sahip olun.</p>
                    @endif
                </div>
                <div class="card-tabs">
                    <ul class="tabs tabs-fixed-width">
                        @foreach (config('plans') as $key => $plan)
                        <li class="tab">
                            <a href="#tab-{{ $key }}">{{ $plan['name'] }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @foreach (config('plans') as $key => $plan)
                <div class="card-content" id="tab-{{ $key }}">
                    @if ($plan['price'])
                        <div class="center-align" style="text-decoration: line-through;">{{ config('formal.currency') }} {{ $plan['price_old'] }}</div>
                        <h3 class="center-align">
                            {{ config('formal.currency') }}
                            {{ $plan['price'] }}
                            <sup>.00</sup>
                            <sub>
                                <small>/ Ay</small>
                            </sub>
                        </h3>
                        @if (@$plan['description'])
                            <p class="grey-text text-darken-2 center-align">{{ $plan['description'] }}</p>
                        @endif

                        <p class="grey-text text-darken-2 center-align">Vergiler dahil değildir.</p>
                    @else
                        <h3 class="center-align">Ücretsiz!</h3>
                    @endif

                    <ul class="collection collection-unstyled collection-unstyled-hover">
                        @foreach ($plan['properties'] as $k => $item)
                        <li class="collection-item">
                            <div>
                                <span>
                                    <p>
                                        <span class="grey-text text-darken-4">{{ $item['text'] }}</span>

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
                                    <p class="grey-text text-darken-2">{{ $item['details'] }}</p>

                                    @if (is_string($item['value']))
                                        <p class="teal-text">{{ $item['value'] }}</p>
                                    @endif
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>

            <div class="center-align">
                <a href="{{ route('user.login') }}" class="waves-effect btn-flat btn-large">Hemen Başlayın!</a>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
        $('.parallax').parallax()
    })

    $('.down-area').on('click', 'a.btn-large', function(e) {
        scrollTo({
            'target': '#more-step'
        })
    })
@endpush
