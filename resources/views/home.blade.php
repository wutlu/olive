@extends('layouts.app')

@section('content')
<header id="main">
    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/bg-2.svg') }}" alt="bg-1" />
            <video autoplay muted loop id="background-video">
                <source src="{{ asset('video/face.mp4') }}" type="video/mp4">
            </video>
        </div>

        <div class="container">
            <div class="row">
                <div class="col">
                    <a href="#" id="logo">
                        <img src="{{ asset('img/olive-logo.svg') }}" alt="olive-logo" class="responsive-img" />
                    </a>

                    <p class="white-text">@lang('global.header.lead-1')</p>
                    <p class="grey-text">@lang('global.header.lead-2')</p>
                    <p class="grey-text">@lang('global.header.lead-3')</p>

                    <a href="{{ route('user.login') }}" class="waves-effect btn black-text white">@auth{{ 'Panele Gidin'}}@else{{ 'Giriş Yapın' }}@endauth</a>

                    <div class="down-area center-align">
                        <a href="#" class="waves-effect btn-large btn-floating pulse white">
                            <i class="material-icons black-text">arrow_downward</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="parallax-container" id="more-step">
    <div class="parallax">
        <img src="{{ asset('img/grabg.jpg') }}" alt="grabg-2" />
    </div>

    <div class="container">
        <img id="vz-logo-top" src="{{ asset('img/veri.zone-logo-white.svg') }}" alt="veri.zone-logo" />
        <div class="item-group" id="features">
            <div class="item white-text">
                <i class="large material-icons analytics">poll</i>
                <h5>Analiz</h5>
                <ul>
                    <li>-</li>
                </ul>
            </div>
            <div class="item white-text">
                <i class="large material-icons realtime">subject</i>
                <h5>Gerçek Zamanlı Veri</h5>
                <ul>
                    <li>-</li>
                </ul>
            </div>
            <div class="item white-text">
                <i class="large material-icons rotate">toys</i>
                <h5>Araçlar</h5>
                <ul>
                    <li>-</li>
                </ul>
            </div>
            <div class="item white-text">
                <i class="large material-icons cloud">cloud</i>
                <h5>Arşiv</h5>
                <ul>
                    <li>-</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="parallax-container">
    <div class="parallax">
        <img src="{{ asset('img/bg-2.svg') }}" alt="bg-2" />
    </div>

    <div class="container">
        <h2 class="center-align">Ekip</h2>
        <div class="item-group item-group-center" id="team">
            <div class="item">
                <img alt="mutlu" src="{{ asset('img/team-mutlu.jpg') }}" />
                <h6>Alper Mutlu Toksöz</h6>
                <p class="grey-text">Kurucu</p>
                <p class="grey-text">Yazılım Geliştirici</p>
            </div>
            <div class="item">
                <img alt="gül" src="{{ asset('img/team-gul.jpg') }}" />
                <h6>Gül Toksöz</h6>
                <p class="grey-text">Ürün Yöneticisi</p>
            </div>
            <div class="item">
                <img alt="canberk" src="{{ asset('img/team-canberk.jpg') }}" />
                <h6>Canberk Eraslan</h6>
                <p class="grey-text">Genel Müdür</p>
                <p class="grey-text">Operasyon Direktörü</p>
            </div>
            <div class="item">
                <img alt="sena" src="{{ asset('img/team-sena.jpg') }}" />
                <h6>Sena Demir</h6>
                <p class="grey-text">Veri Analisti</p>
            </div>
        </div>
    </div>
</div>

<div class="parallax-container">
    <div class="parallax">
        <img src="{{ asset('img/obg.jpg') }}" alt="obg" />
    </div>

    <div class="container">
        <h2 class="center-align white-text">Planlar</h2>

        <div class="card">
            <div class="card-content">
                <span class="card-title">Olive'e kaydolmak ücretsizdir.</span>
                <p class="grey-text">Tüm araçlardan faydalanabilmek için bir organizasyon satın almalı veya bir organizasyon'a dahil olmalısınız.</p>
                <p class="orange-text">Hemen üye olarak hiçbir ücret ödemeden başlangıç paketinden faydalanabilirsiniz.</p>
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
            <div class="card-content grey lighten-4">
                @foreach (config('plans') as $key => $plan)
                <div id="tab-{{ $key }}">
                    @if ($plan['price'])
                    <h3 class="center-align">₺ {{ $plan['price'] }}<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="center-align grey-text">Yıllık ödemelerde anında <span class="chip">{{ $plan['yearly_discount_rate'] }}%</span> indirim alın.</p>
                    @else
                    <h3 class="center-align">Ücretsiz!</h3>
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
                </div>
                @endforeach
            </div>
        </div>

        <div class="center-align">
            <a href="{{ route('user.login') }}" class="waves-effect btn white black-text btn-large">Hemen Başlayın!</a>
        </div>
    </div>
</div>
@endsection

@push('local.scripts')

    $(document).ready(function() {
        $('.tabs').tabs();
        $('.parallax').parallax();
    });

    $('.down-area').on('click', 'a.btn-large', function(e) {
        scrollTo({
            'target': '#more-step'
        })
    })

@endpush
