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
                    <li>Twitter kullanıcı analizi.</li>
                    <li>Yerli haber sitelerinden anlık veya tarih bazlı trend bilgisi.</li>
                    <li>Terli sosyal medya platformlarından anlık veya tarih bazlı trend bilgisi.</li>
                    <li>Twitter anlık trend 50 bilgisi.</li>
                    <li>Google anlık trend 50 bilgisi.</li>
                </ul>
            </div>
            <div class="item white-text">
                <i class="large material-icons realtime">subject</i>
                <h5>Gerçek Zamanlı Veri</h5>
                <ul>
                    <li>Belirlenen kelimelerden gerçek zamanlı veri akışı.</li>
                    <li>Yerli haber sitelerinden gerçek zamanlı tam veri akışı.</li>
                    <li>Yerli sosyal medya platformlarından gerçek zamanlı tam veri akışı.</li>
                    <li>Twitter, Instagram, Yerli Sosyal Medya ve Yerli Medya anahtar kelime alarmı.</li>
                </ul>
            </div>
            <div class="item white-text">
                <i class="large material-icons rotate">toys</i>
                <h5>Araçlar</h5>
                <ul>
                    <li>Twitter, detaylı Tweet bilgileri.</li>
                    <li>Twitter, kullanıcı adından ID, ID'den Kullanıcı adı bulucu.</li>
                    <li>Twitter, kullanıcı Tweetleri dışarı aktarma.</li>
                    <li>Twitter, kullanıcı takip/takipçi listesi dışarı aktarma.</li>
                </ul>
            </div>
            <div class="item white-text">
                <i class="large material-icons cloud">cloud</i>
                <h5>Arşiv</h5>
                <ul>
                    <li>Tam veri arşivi.</li>
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

        <div class="card ">
            <div class="card-content">
                <span class="card-title">Olive'e kaydolmak ücretsizdir.</span>
                <p class="grey-text">Tüm araçlardan faydalanabilmek için bir organizasyon satın almalı veya bir organizasyon'a dahil olmalısınız.</p>
                <p class="orange-text">Hemen üye olarak hiçbir ücret ödemeden başlangıç paketinden faydalanabilirsiniz.</p>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width">
                    <li class="tab">
                        <a class="active" href="#tab0">Başlangıç</a>
                    </li>
                    <li class="tab">
                        <a href="#tab1">Şahıs Planı</a>
                    </li>
                    <li class="tab">
                        <a href="#tab2">Ofis Organizasyonu</a>
                    </li>
                    <li class="tab">
                        <a href="#tab3">Kurum Organizasyonu</a>
                    </li>
                </ul>
            </div>
            <div class="card-content grey lighten-4">
                <div id="tab0">
                    <h3 class="center-align">Ücretsiz</h3>

                    <ul class="collection collection-unstyled collection-unstyled-hover">
                        <li class="collection-item">
                            <div>
                                Plan kapasitesi
                                <span class="badge grey lighten-2">1</span>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Google anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, detaylı Tweet bilgileri.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı adından ID, ID'den Kullanıcı adı bulucu.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Api kullanımı.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Yerli haber sitelerinden anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Yerli sosyal medya platformlarından anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Twitter kullanıcı analizi.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Belirlenen kelimelerden gerçek zamanlı veri akışı.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Twitter, Instagram, Yerli Sosyal Medya ve Yerli Medya anahtar kelime alarmı.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Twitter, kullanıcı Tweetleri dışarı aktarma.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Twitter, kullanıcı takip/takipçi listesi dışarı aktarma.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="grey-text">
                                Tam veri erişimi.
                                <i class="material-icons secondary-content red-text">close</i>
                            </div>
                        </li>
                    </ul>
                </div>
                <div id="tab1">
                    <h3 class="center-align">₺ 490<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="center-align grey-text">Yıllık ödeme seçeneğinde <span class="chip">10%</span> indirim.</p>

                    <ul class="collection collection-unstyled collection-unstyled-hover">
                        <li class="collection-item">
                            <div>
                                Plan kapasitesi
                                <span class="badge grey lighten-2">1</span>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Google anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, detaylı Tweet bilgileri.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı adından ID, ID'den Kullanıcı adı bulucu.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Api kullanımı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter kullanıcı analizi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Belirlenen kelimelerden gerçek zamanlı veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, Instagram, Yerli Sosyal Medya ve Yerli Medya anahtar kelime alarmı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı Tweetleri dışarı aktarma.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı takip/takipçi listesi dışarı aktarma.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Tam veri erişimi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                    </ul>
                </div>
                <div id="tab2">
                    <h3 class="center-align">₺ 1,290<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="center-align grey-text">Yıllık ödeme seçeneğinde <span class="chip">12%</span> indirim.</p>

                    <ul class="collection collection-unstyled collection-unstyled-hover">
                        <li class="collection-item">
                            <div>
                                Plan kapasitesi
                                <span class="badge blue lighten-2 white-text">3</span>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Google anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, detaylı Tweet bilgileri.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı adından ID, ID'den Kullanıcı adı bulucu.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Api kullanımı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter kullanıcı analizi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Belirlenen kelimelerden gerçek zamanlı veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, Instagram, Yerli Sosyal Medya ve Yerli Medya anahtar kelime alarmı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı Tweetleri dışarı aktarma.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı takip/takipçi listesi dışarı aktarma.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Tam veri erişimi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                    </ul>
                </div>
                <div id="tab3">
                    <h3 class="center-align">₺ 2,190<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="center-align grey-text">Yıllık ödeme seçeneğinde <span class="chip">14%</span> indirim.</p>

                    <ul class="collection collection-unstyled collection-unstyled-hover">
                        <li class="collection-item">
                            <div>
                                Plan kapasitesi
                                <span class="badge green lighten-2 white-text">8</span>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından gerçek zamanlı tam veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Google anlık trend 50 bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, detaylı Tweet bilgileri.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı adından ID, ID'den Kullanıcı adı bulucu.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Api kullanımı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli haber sitelerinden anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Yerli sosyal medya platformlarından anlık veya tarih bazlı trend bilgisi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter kullanıcı analizi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Belirlenen kelimelerden gerçek zamanlı veri akışı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, Instagram, Yerli Sosyal Medya ve Yerli Medya anahtar kelime alarmı.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı Tweetleri dışarı aktarma.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Twitter, kullanıcı takip/takipçi listesi dışarı aktarma.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div>
                                Tam veri erişimi.
                                <i class="material-icons secondary-content green-text">check</i>
                            </div>
                        </li>
                    </ul>
                </div>
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
