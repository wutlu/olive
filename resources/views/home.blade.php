@extends('layouts.app', [
    'footer_extend' => true,
    'title' => [
        'text' => 'Veri Zone Teknoloji'
    ],
    'description' => 'Veri Zone Teknoloji, web ve sosyal medya verilerine anlam kazandıran yazılımlar ve raporlar üretir.'
])

@push('local.styles')
    .mockup-slider {
        margin: 0;
        padding: 0;

        max-width: 1024px;
    }
    .mockup-slider .owl-stage-outer {
        margin: 0;
        padding: 0;

        width: 100%;
        background-color: #fff;
    }

    .main-slider .owl-stage-outer {
        margin: 0;
        padding: 0;
        padding: 64px 0 48px;
        text-align: center;
        font-size: 24px;
    }
    .main-slider .item {
        display: block;
    }
    .main-slider .item > .canvas {
        border: 2px solid #666;
        margin: 0 16px;
        padding: 32px 16px;
    }

    section {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-attachment: fixed;
    }

    .demo-section {
        padding: 100px 0;
    }

    .y-section {
        background-image: url({{ asset('img/obg.svg?v4') }});
        background-size: contain;
        background-attachment: scroll;
        background-position: center bottom;
    }

    .x-section {
        background-image: url({{ asset('img/photo/xolive.jpg?v5') }});
        background-position: bottom;
    }

    .x-section > .section-overlay {
        min-height: calc(70vh);
    }

    .rt-section {
        background-image: url({{ asset('img/photo/live.jpg?v6') }});
        background-position: center bottom;
    }

    .work-section {
        background-image: url({{ asset('img/photo/analysis.jpg?v7') }});
        background-position: center bottom;
    }

    section h1 {
        margin: 0 0 1rem;
        font-size: 48px;
    }

    section h2 {
        margin: 0 0 1rem;
        font-size: 32px;
    }

    .section-overlay {
        background-color: rgba(0, 0, 0, .4);
        padding: 4rem 0;
    }

    .browser-mockup {
        margin: 2rem auto 0;
    }

    .browser-mockup.mockup-news {
        margin: 0;
    }

    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    .time-line > .collection {
        height: 200px;
        overflow: hidden;
    }
    .time-line > .collection.active {
        background-color: #f0f0f0;
    }

    .logo {
        max-width: 256px;
        margin: 0 0 2rem;
    }

    .more-down {
        text-align: center;
        position: absolute;
        right: 0;
        left: 0;

        -webkit-transform: translateY(calc(-100% + -2rem));
                transform: translateY(calc(-100% + -2rem));
    }

    .cookie-alert {
        position: fixed;

        top: auto;
        right: auto;
        bottom: 1rem;
        left: 1rem;

        z-index: 1000;

        background-color: #fff;

        border-radius: 3px;

        padding: 1rem 1.5rem;

        max-width: 300px;
    }

    @media (max-width: 576px) {
        .cookie-alert {
            max-width: 100%;
            right: 1rem;
        }
    }

    .cookie-alert > .close {
        position: absolute;

        top: .5rem;
        right: .5rem;
    }
@endpush

@push('local.scripts')
    $('.main-slider').owlCarousel({
        responsiveClass: true,
        smartSpeed: 100,
        dots: false,
        loop: true,
        responsive: {
            0: { items: 1 },
            500: { items: 2 },
            768: { items: 3 }
        },
        autoHeight: true,
        nav: false,
        autoplay: true,
        autoplayTimeout: 1500,
        autoplayHoverPause: true
    })

    $('.media-cards').owlCarousel({
        responsive:{
            0: { items: 1 },
            720: { items: 2 },
            1024: { items: 3 },
            1366: { items: 4 },
        },
        lazyLoad: true,
        center: true,
        loop: true,

        responsiveClass: true,
        smartSpeed: 100,
        dots: false,
        nav: false,

        autoplay: true,
        autoplayTimeout: 1000,
        autoplayHoverPause: true
    })

    $('.mockup-slider > .owl-carousel').owlCarousel({
        items: 1,
        lazyLoad: true,
        center: true,
        loop: true,

        responsiveClass: true,
        smartSpeed: 100,
        dots: false,
        nav: false,

        autoplay: true,
        autoplayTimeout: 1000,
        autoplayHoverPause: true
    })

    var buffer = [];
    var words = [];

    var speed = 600; // default
    var time = speed;
    var liveTimer;

    $(window).on('load', function() {
        livePush()
    })

    var bucket = $('.time-line > .collection');
    var model = bucket.children('.model');

    function livePush()
    {
        if (buffer.length)
        {
            var obj = buffer[0];

            if (!$('#' + obj.uuid).length)
            {
                var item = model.clone().html($('<div />', {
                    'html': [
                        $('<span />', {
                            'html': obj.called_at,
                            'class': 'grey-text align-self-center d-table',
                            'css': {
                                'width': '48px'
                            }
                        }),
                        $('<span />', {
                            'class': 'align-self-center',
                            'html': obj.title
                        })
                    ],
                    'class': 'd-flex grey-text text-darken-2'
                }));

                item.mark(words, {
                    'element': 'span',
                    'className': 'marked yellow black-text',
                    'accuracy': 'complementary'
                })

                item.attr('id', obj.uuid)
                    .hide()
                    .removeClass('model hide')
                    .show( 'highlight', {
                        'color': '#ffe0b2'
                    }, 1000 );

                item.prependTo(bucket)
            }

            buffer.shift()

            if (bucket.children('.collection-item').length > 200)
            {
                bucket.children('.collection-item:last-child').remove()
            }
        }

        window.clearTimeout(liveTimer);

        liveTimer = window.setTimeout(function() {
            livePush()
        }, time)
    }

    $(document).on('mouseenter', '.time-line > .collection', function() {
        time = 60000;

        $('.time-line > .collection').addClass('active')
    }).on('mouseleave', '.time-line', speed_change)

    function speed_change()
    {
        time = speed;

        window.clearTimeout(liveTimer);

        liveTimer = window.setTimeout(function() {
            livePush()
        }, time)

        $('.time-line > .collection').removeClass('active')
    }

    var streamTimer;

    function __realtime(__, obj)
    {
        if (obj.status == 'ok')
        {
            words = obj.words;

            $.each(obj.data, function(key, o) {
                if ($('#' + o.uuid).length)
                {
                    //
                }
                else
                {
                    var item = buffer.filter(function (x) {
                         return x.uuid === o.uuid
                    })[0];
    
                    if (!item)
                    {
                        buffer.push(o)
                    }
                }
            })

            window.clearTimeout(streamTimer)

            streamTimer = window.setTimeout(function() {
                vzAjax($('.time-line'))
            }, 10000)
        }
    }
@endpush

@section('content')
    <div class="cookie-alert z-depth-1 grey lighten-4 hide">
        <a href="{{ route('page.view', 'cerez-politikasi') }}" class="blue-grey-text" style="font-weight: bold;">Çerezler</a> özel bir deneyim sunarak ziyaretçilerimize daha iyi hizmet vermemizi ve daha faydalı bilgiler sunmak üzere kendi dahili amaçlarımız için kullanılacaktır.
        <a href="#" class="close">
            <i class="material-icons">close</i>
        </a>
    </div>

    <section class="x-section">
        <div class="section-overlay">
            <div class="container">
                <div style="max-width: 720px;">
                    <img alt="Veri Zone" src="{{ asset('img/veri.zone_logo-white.svg') }}" class="logo" />
                    <br />
                    <br />
                    <h1 class="white-text mb-0">Web ve Sosyal Medyayı Takip Edin</h1>
                    <p class="mb-2">
                        <span class="white black-text" style="font-size: 26px;">Olan biteni ölçümleyin, olacaklardan eş zamanlı haberdar olun!</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="more-down">
        <a href="#" class="btn-floating btn-large pulse white" data-scroll-to=".work-section">
            <i class="material-icons grey-text text-darken-2">keyboard_arrow_down</i>
        </a>
    </div>

    <section class="y-section grey lighten-4">
        <div class="container">
            <div class="owl-carousel main-slider">
                <div class="item">
                    <div class="canvas">Sınırsız Sorgu!</div>
                </div>
                <div class="item">
                    <div class="canvas">Sınırsız Sonuç!</div>
                </div>
                <div class="item">
                    <div class="canvas">Eş Zamanlı Grafikler</div>
                </div>
                <div class="item">
                    <div class="canvas">Boole Operatörleri</div>
                </div>
                <div class="item">
                    <div class="canvas">Kelime ve Cümleler</div>
                </div>
                <div class="item">
                    <div class="canvas">Trollsüz Sonuçlar</div>
                </div>
            </div>

            <div style="max-width: 720px; font-size: 16px;" class="d-table mx-auto center-align">Olive web ve sosyal medya platformu, çevrimiçi konuşmaları gerçek zamanlı takip edebilmeniz için tasarlanmıştır. Web ve sosyal medyada; şirketiniz, organizasyonunuz veya yöneticileriniz için içgörüler elde edin. Müşteri ilişkilerinden kurumsal iletişime kadar her konudan haberdar olun.</div>

            <div class="browser-mockup mockup-slider">
                <div class="owl-carousel">
                    <img src="{{ asset('img/mockup-slide-1.jpg?v5') }}" alt="Olive Mockup 1" />
                    <img src="{{ asset('img/mockup-slide-2.jpg?v5') }}" alt="Olive Mockup 2" />
                    <img src="{{ asset('img/mockup-slide-3.jpg?v5') }}" alt="Olive Mockup 3" />
                    <img src="{{ asset('img/mockup-slide-4.jpg?v5') }}" alt="Olive Mockup 4" />
                    <img src="{{ asset('img/mockup-slide-5.jpg?v5') }}" alt="Olive Mockup 4" />
                </div>
            </div>
        </div>
    </section>

    <section class="work-section">
        <div class="section-overlay">
            <div class="container">
                <h2 class="white-text center-align">EN İYİ ARAÇLAR</h2>
                <p class="white-text d-table mx-auto mb-0" style="font-size: 16px;">Haftalık, Aylık ve Yıl Sonu rapor hizmetleri ve bir çok çevrimiçi araç.</p>
                <p class="white-text d-table mx-auto mb-0" style="font-size: 16px;">Eş zamanlı web ve sosyal medya takip, ölçümleme, analiz ve içgörü için en iyi iş zekası platformu.</p>
            </div>
            <br />
            <br />
            <br />
            <div class="media-cards owl-carousel">
                @foreach ($array as $key => $item)
                    <div>
                        <div class="d-flex">
                            <span class="circle-rank z-depth-1 white align-self-center mr-1 d-flex justify-content-end" style="background-image: url('{{ $item['icon'] }}');">
                                <span class="align-self-end">{{ $key+1 }}</span>
                            </span>
                            <h5 class="white-text align-self-center m-0">{{ $item['title'] }}</h5>
                        </div>
                        <div class="card-panel left-align" style="width: 340px; font-size: 16px;">{{ $item['text'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="y-section">
        <div class="container">
            <div class="pt-2 pb-2 mt-2 mb-2">
                <h2 style="font-size: 24px;">VERİ ZONE TEKNOLOJİ</h2>
                <p style="font-size: 16px;"><span class="">Heyecanlıyız ve her şeyi merak ediyoruz!</span> Markaları, kurumları, şirketleri ve kişileri! <span class="">Web</span> ve <span class="">Sosyal Medya</span> hızla gelişip büyürken, merakımızı gidermek de bir hayli zorlaştı. Bol kahve eşliğinde yaptığımız uzun ve zorlu mesailerin sonunda <span class="">Olive</span>'i tasarladık.</p>
                <br />
                <p style="font-size: 16px;">Artık <span class="">Olive</span>'in yardımıyla, büyük ve karmaşık <span class="">Web</span> ve <span class="">Sosyal Medya</span> verilerini; çok daha hızlı ve zengin içerikler halinde rahatca okuyup, hızlı bir şekilde raporlayabiliyoruz.</p>
                <br />
                <p style="font-size: 16px;">Siz de <span class="">Olive</span>'in eşsiz özelliklerini denemek için hemen bizimle iletişime geçin!</p>
            </div>
        </div>
    </section>

    <section class="rt-section">
        <div class="section-overlay">
            <div class="container">
                <div class="row">
                    <div class="col s12 m6">
                        <h2 class="white-text">GERÇEK ZAMANLI AKIŞ</h2>
                        <div class="browser-mockup mockup-news">
                            <div
                                class="card card-nb time-line load"
                                data-href="{{ route('realtime.query.sample') }}"
                                data-callback="__realtime"
                                data-method="post">
                                <ul class="collection">
                                    <li class="collection-item model hide"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m6">
                        <div class="p-1">
                            <p class="white-text" style="font-size: 16px; text-decoration: line-through;">Sayfayı yenile, yeni verileri yükle vb. ilkel yöntemlerden kurtulun!</p>
                            <p class="white-text" style="font-size: 16px;">Belirlediğiniz kriterlere göre, veriler anında ekranınıza düşsün!</p>
                            <p class="white-text" style="font-size: 16px;">Ayrıca filtrelediğiniz verileri veya gündemi kaçırmadan görmenizi sağlayan eşsiz kullanıcı deneyimini Olive ile yaşayın!</p>

                            <br />

                            <span class="chip pink darken-4 white-text">haber</span>
                            <span class="chip white">+bilgi</span>
                            <span class="chip white">+teknoloji</span>
                            <span class="chip white">+internet</span>
                            <br />
                            <span class="chip white">+türkiye</span>
                            <span class="chip white">+spor</span>
                            <span class="chip white">+futbol</span>
                            <span class="chip white">+basketbol</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!--
    @push('local.styles')
        #videos .indicators {
            bottom: 48px;
            height: 24px;
        }
        #videos .indicators > .indicator-item {
            background-color: #000;
            margin-top: 0;
            margin-bottom: 0;
            width: 16px;
            height: 16px;
        }
        #videos .indicators > .indicator-item.active {
            background-color: #1ab7ea;
        }
    @endpush
    <section>
        <div class="container">
            <div class="mt-2 mb-2 pt-2 pb-2 center-align">
                <h3 class="m-0">Kısa bir bakış?</h3>
                <p class="grey-text mb-1">Hazırladığımız bu kısa videolar ile Olive'e kısaca bir göz atın!</p>
                <div class="carousel carousel-slider" id="videos">
                    <div class="carousel-item" style="height: 240px;">
                        <iframe src="https://player.vimeo.com/video/359099769" width="100%" height="100%" frameborder="0" allow="fullscreen" allowfullscreen></iframe>
                    </div>
                    <div class="carousel-item" style="height: 240px;">
                        <iframe src="https://player.vimeo.com/video/359099664" width="100%" height="100%" frameborder="0" allow="fullscreen" allowfullscreen></iframe>
                    </div>
                    <div class="carousel-item" style="height: 240px;">
                        <iframe src="https://player.vimeo.com/video/359099748" width="100%" height="100%" frameborder="0" allow="fullscreen" allowfullscreen></iframe>
                    </div>
                    <div class="carousel-item" style="height: 240px;">
                        <iframe src="https://player.vimeo.com/video/359099638" width="100%" height="100%" frameborder="0" allow="fullscreen" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
-->
    <section>
        <div class="container">
            <div class="pt-2 pb-2 mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="flex-fill align-self-center">
                        <div class="p-1">
                            <h2>ÇEVRİMİÇİ İTİBARINIZI KORUYUN</h2>
                            <p class="mb-0" style="font-size: 16px;">Çevrimiçi itibarınıza yönelik olası tehditleri herkesten önce fark edin, stratejinizi geliştirin ve harekete geçin.</p>

                            <div class="d-flex">
                                <ul class="flex-fill">
                                    <li class="d-flex">
                                        <i class="align-self-start material-icons mr-1">arrow_forward</i>
                                        <span class="align-self-start">Troll hesapları analizlerinizden çıkartın</span>
                                    </li>
                                    <li class="d-flex">
                                        <i class="align-self-start material-icons mr-1">arrow_forward</i>
                                        <span class="align-self-start">Influencer kriterlerinizi siz belirleyin</span>
                                    </li>
                                </ul>
                                <ul class="flex-fill">
                                    <li class="d-flex">
                                        <i class="align-self-start material-icons mr-1">arrow_forward</i>
                                        <span class="align-self-start">Markanızla ilgilenenlerin listesini çıkartın</span>
                                    </li>
                                    <li class="d-flex">
                                        <i class="align-self-start material-icons mr-1">arrow_forward</i>
                                        <span class="align-self-start">Olası krizleri önleyin</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex-fill align-self-center hide-on-med-and-down">
                        <img alt="Reputation" src="{{ asset('img/photo/businessman.jpg?v4') }}" class="responsive-img" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('local.scripts')
        function __demo_request(__, obj)
        {
            if (obj.status == 'ok')
            {
                M.toast({ html: 'Talebinizi Aldık!', classes: 'green darken-2' })
                M.toast({ html: 'Ekibimiz en kısa sürede sizinle iletişime geçecektir.', classes: 'blue-grey' })

                __.find('input[type=text]').html('')
            }
        }

        $('.carousel.carousel-slider').carousel({
            fullWidth: true,
            indicators: true
        })
    @endpush
    <section class="demo-section pink darken-4 white-text">
        <div class="container">
            <div class="row">
                <div class="col m12 l6">
                    <div class="card card-unstyled">
                        <div class="card-content">
                            <form id="demo-form" method="post" action="{{ route('demo.request') }}" class="json" data-callback="__demo_request">
                                <h2>ŞİMDİ ÜCRETSİZ DENEYİN!</h2>
                                <div class="input-field white-text">
                                    <i class="material-icons prefix">account_circle</i>
                                    <input id="icon_prefix" name="name" type="text" class="validate" />
                                    <label for="icon_prefix">Firma / Kurum</label>
                                </div>
                                <div class="input-field white-text">
                                    <i class="material-icons prefix">phone</i>
                                    <input id="icon_telephone" name="phone" type="text" class="validate" />
                                    <label for="icon_telephone">Telefon</label>
                                </div>
                                <div class="input-field white-text">
                                    <div class="captcha" data-id="demo-captcha"></div>
                                </div>
                                <button type="submit" class="btn-flat white-text waves-effect">Gönder</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col m12 l6">
                    <div class="card card-unstyled">
                        <div class="card-content">
                            <h2>En Uygun Çözümü Oluşturalım!</h2>
                            <p style="font-size: 16px;">Hemen bilgilerinizi bırakın, size en kısa sürede tam özellikli bir Olive ile dönüş sağlayalım.</p>
                            <p style="font-size: 16px;">Olive'i denerken, kullanmadığınız özellikleri belirleyin, paketinizden çıkartalım!</p>
                            <p style="font-size: 16px;">Gereksiz maliyetlerin altında kalmayın!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
    <script src="{{ asset('js/jquery.typewrite.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush
