@extends('layouts.app')

@push('local.scripts')
    $(document).on('mouseover', '#main', function() {
        $(this).find('.face').attr('src', '{{ asset('img/f2.jpg') }}')
    }).on('mouseleave', '#main', function() {
        $(this).find('.face').attr('src', '{{ asset('img/f1.jpg') }}')
    })
@endpush

@section('content')
    <header id="main">
        <div class="parallax-container">
            <div class="parallax">
                <img class="face" src="{{ asset('img/f1.jpg') }}" alt="Face" />
            </div>

            <div class="parallax">
                <img src="{{ asset('img/bg-2.svg') }}" alt="bg-2" />
            </div>

            <div class="container">
                <div class="row">
                    <div class="col">
                        <a href="#" id="logo">
                            <img src="{{ asset('img/olive-logo.svg') }}" alt="olive-logo" class="responsive-img" />
                        </a>

                        <p class="white-text">@lang('global.header.lead-1')</p>
                        <p class="grey-text">@lang('global.header.lead-2')</p>
                        <p class="red-text">@lang('global.header.lead-3')</p>

                        <a href="{{ route('user.login') }}" class="waves-effect btn black-text white">@auth{{ 'Olive\'e Gidin'}}@else{{ 'Giriş Yapın' }}@endauth</a>

                        <div class="down-area center-align">
                            <a href="#" class="waves-effect btn-large btn-floating pulse white">
                                <i class="material-icons grey-text text-darken-2">arrow_downward</i>
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
                        <li>- Twitter Analizleri</li>
                        <li>- YouTube Analizleri</li>
                        <li>- Haber Siteleri Analizleri</li>
                        <li>- Sözlük Siteleri Analizleri</li>
                        <li>- Alışveriş Siteleri Analizleri</li>
                        <li>- ve dahası...</li>
                    </ul>
                </div>
                <div class="item white-text">
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
                <div class="item white-text">
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
                <div class="item white-text">
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
            <img src="{{ asset('img/bg-2.svg') }}" alt="bg-2" />
        </div>

        <div class="container">
            <h2 class="center-align" itemprop="name">Olive</h2>
            <p class="center-align" itemprop="description">Olive, bir grup genç girişimci tarafından üretilen gerçek zamanlı veri analiz katmanıdır.<br />Tamamen yerli kaynaklarla üretilip, sürekli bir geliştirilme sürecindedir.</p>
            @php
            /*
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
            */
            @endphp
        </div>
    </div>

    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/obg.jpg') }}" alt="obg" />
        </div>

        <div class="container">
            <h2 class="center-align white-text">Planlar</h2>

            <div class="card">
                <div class="card-content center-align">
                    <p class="teal-text">Hemen ücretsiz bir şekilde üye olabilir ve hiçbir ücret ödemeden başlangıç paketinden faydalanabilirsiniz.</p>
                    <p class="grey-text">Tüm araçlardan faydalanabilmek için bir organizasyon satın almalı veya bir organizasyon'a dahil olmalısınız.</p>
                    <hr />
                    @if (@$discountDay)
                        <p class="center-align grey-text">Hemen şimdi üye olun ve bugüne özel <span class="chip">{{ $discountDay->discount_rate }}%</span> indirim kuponuna anında sahip olun.</p>
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
                <div class="card-content grey lighten-4" id="tab-{{ $key }}">
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
                            <p class="grey-text center-align">{{ $plan['description'] }}</p>
                        @endif

                        <p class="grey-text center-align">Vergiler dahil değildir.</p>
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

            <div class="center-align">
                <a href="{{ route('user.login') }}" class="waves-effect btn white black-text btn-large">Hemen Başlayın!</a>
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
