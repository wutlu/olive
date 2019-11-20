@extends('layouts.app', [
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
        text-align: center;
        font-size: 24px;
    }
    .main-slider .item {
        display: block;
    }
    .main-slider .item > .canvas {
        margin: 0 16px;
        padding: 48px 0 16px;
    }

    section {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-attachment: fixed;
    }

    .demo-section {
        background-image: url({{ asset('img/photo/carbon.jpg?v4') }});
    }
    .demo-section > .section-overlay {
        padding: 100px 0;
    }

    .y-section {
        background-image: url({{ asset('img/obg.svg?v4') }});
        background-size: contain;
        background-attachment: scroll;
        background-position: center bottom;

        padding: 2rem 0 0;
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
        display: block;
    }
    section h2 {
        margin: 0 0 1rem;
        font-size: 32px;
    }

    @media (max-width: 1024px)
    {
        section h1 {
            font-size: 32px;
        }
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
    }
    .olive-logo {
        max-width: 128px;
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
        smartSpeed: 1000,
        dots: false,
        loop: true,
        responsive: {
            0: { items: 1 },
            800: { items: 2 },
            1400: { items: 3 }
        },
        autoHeight: true,
        nav: false,
        autoplay: true,
        autoplayTimeout: 2000,
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
        autoplayTimeout: 2000,
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
        autoplayTimeout: 4000,
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
        <div class="section-overlay d-flex">
            <div class="container align-self-center">
                <div class="center-align">
                    <img alt="Veri Zone" src="{{ asset('img/veri.zone_logo-white.svg') }}" class="logo mb-2" />
                </div>
                <h1 class="white-text mb-0 d-block center-align">Web ve Sosyal Medyayı Takip Edin</h1>
                <p class="white-text d-block center-align" style="font-size: 24px;">Olan biteni ölçümleyin, olacaklardan eş zamanlı haberdar olun!</p>
            </div>
        </div>
    </section>

    <div class="more-down">
        <span class="white-text d-table mx-auto mb-1">ÜCRETSİZ DENEYİN</span>
        <a href="#" class="btn-floating btn-large pulse white" data-scroll-to=".demo-section">
            <i class="material-icons grey-text text-darken-2">keyboard_arrow_down</i>
        </a>
    </div>

    <section class="y-section">
        <div class="container">
            <div class="owl-carousel main-slider">
                <div class="item">
                    <div class="canvas">SINIRSIZ SORGU!</div>
                </div>
                <div class="item">
                    <div class="canvas">SINIRSIZ SONUÇ!</div>
                </div>
                <div class="item">
                    <div class="canvas">EŞ ZAMANLI GRAFİKLER</div>
                </div>
                <div class="item">
                    <div class="canvas">BOOLE OPERATÖRLERİ</div>
                </div>
                <div class="item">
                    <div class="canvas">TROLLSÜZ SONUÇLAR</div>
                </div>
                <div class="item">
                    <div class="canvas">YERLİ TEKNOLOJİ</div>
                </div>
                <div class="item">
                    <div class="canvas">MİLLİ TEKNOLOJİ</div>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-2 mb-2 pt-2 pb-2 mx-auto" style="max-width: 1024px;">
                <div class="align-self-center right-align pr-2">
                    <img alt="Olive" src="{{ asset('img/olive_logo-grey.svg') }}" class="olive-logo" />
                </div>
                <div class="align-self-center grey-text text-darken-4" style="font-size: 18px;">Olive web ve sosyal medya platformu, açık kaynak konuşmaları gerçek zamanlı takip edebilmeniz için tasarlanmıştır. Web ve sosyal medyada; şirketiniz, organizasyonunuz veya yöneticileriniz için içgörüler elde edin. Müşteri ilişkilerinden kurumsal iletişime kadar her konudan haberdar olun.</div>
            </div>

            <div class="browser-mockup mockup-slider">
                <div class="owl-carousel">
                    <img src="{{ asset('img/mockup-slide-1.jpg?v7') }}" alt="Olive Mockup 1" />
                    <img src="{{ asset('img/mockup-slide-2.jpg?v7') }}" alt="Olive Mockup 2" />
                    <img src="{{ asset('img/mockup-slide-3.jpg?v7') }}" alt="Olive Mockup 3" />
                    <img src="{{ asset('img/mockup-slide-4.jpg?v7') }}" alt="Olive Mockup 4" />
                    <img src="{{ asset('img/mockup-slide-5.jpg?v7') }}" alt="Olive Mockup 4" />
                </div>
            </div>
        </div>
    </section>

    <section class="work-section">
        <div class="section-overlay">
            <div class="container">
                <h2 class="white-text center-align">EN İYİ ARAÇLAR</h2>
                <p class="grey-text text-lighten-4 d-table mx-auto mb-0" style="font-size: 16px;">Haftalık, Aylık ve Yıl Sonu rapor hizmetleri ve bir çok çevrimiçi araç.</p>
                <p class="grey-text text-lighten-4 d-table mx-auto mb-0" style="font-size: 16px;">Eş zamanlı web ve sosyal medya takip, ölçümleme, analiz ve içgörü için en iyi iş zekası platformu.</p>
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

    <section>
        <div class="container">
            <div class="pt-2 pb-2 mt-2 mb-2 center-align">
                <h2 class="grey-text text-darken-2 mb-2">VERİ ZONE TEKNOLOJİ</h2>
                <p style="font-size: 16px;" class="grey-text text-darken-2 mb-1"><span class="">Heyecanlıyız ve her şeyi merak ediyoruz!</span> Markaları, kurumları, şirketleri ve kişileri! <span class="">Web</span> ve <span class="">Sosyal Medya</span> hızla gelişip büyürken, merakımızı gidermek de bir hayli zorlaştı. Bol kahve eşliğinde yaptığımız uzun ve zorlu mesailerin sonunda <span class="">Olive</span>'i tasarladık.</p>
                <p style="font-size: 16px;" class="grey-text text-darken-2 mb-1">Artık <span class="">Olive</span>'in yardımıyla, büyük ve karmaşık <span class="">Web</span> ve <span class="">Sosyal Medya</span> verilerini; çok daha hızlı ve zengin içerikler halinde rahatca okuyup, hızlı bir şekilde raporlayabiliyoruz.</p>
                <p style="font-size: 16px;" class="grey-text text-darken-2">Siz de <span class="">Olive</span>'in eşsiz özelliklerini denemek için hemen bizimle iletişime geçin!</p>
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

                            <span class="chip pink darken-2 white-text">haber</span>
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

    <section>
        <div class="container">
            <div class="pt-2 pb-2 mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="flex-fill align-self-center">
                        <div class="p-1 grey-text text-darken-2">
                            <h2 class="grey-text text-darken-4">ÇEVRİMİÇİ İTİBARINIZI KORUYUN</h2>
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

    @if (config('system.user.registration'))
        <section class="demo-section white-text">
            <div class="section-overlay">
                <div class="container">
                    <div class="d-flex flex-wrap">
                        <div class="flex-fill">
                            <div class="card card-unstyled">
                                <div class="card-content">
                                    <h2>ÜCRETSİZ DENEYİN!</h2>
                                    <p style="font-size: 16px;">Deneme süreniz bittikten sonra çeşitli özelliklerden ücretsiz faydalanmaya devam edebilirsiniz.</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-fill align-self-center center-align">
                            <div class="p-2">
                                <a href="{{ route('user.login') }}" class="btn-flat btn-large heartbeat white grey-text text-darken-2 waves-effect">Deneyin!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
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
        @endpush
        <section class="demo-section white-text">
            <div class="section-overlay">
                <div class="container">
                    <div class="row">
                        <div class="col m12 l6">
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
            </div>
        </section>
    @endif
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
    <script src="{{ asset('js/jquery.typewrite.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush
