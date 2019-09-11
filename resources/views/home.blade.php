@extends('layouts.app', [
    'footer_extend' => true,
    'title' => [
        'text' => 'Veri Zone Teknoloji'
    ],
    'description' => 'Veri Zone Teknoloji, sosyal medya ve haber verilerine; üstün filtreleme ve çeşitli analiz özellikleriyle, daha hızlı ve daha anlamlı bir şekilde ulaşmanızı sağlayan yazılımlar geliştirir.'
])

@push('local.styles')
    .main-slider .item {
        display: block;
        width: 100%;
    }
    .main-slider .owl-stage-outer {
        padding: 2rem 0;
        text-align: center;
        font-size: 32px;
    }

    section {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-attachment: fixed;
    }

    .demo-section {
        padding: 100px 0;
        background-color: #42a5f5;
        color: #fff;
    }

    .y-section {
        background-image: url({{ asset('img/obg.svg') }});
        background-size: contain;
        background-attachment: scroll;
        background-position: center bottom;
    }

    .x-section {
        background-image: url({{ asset('img/photo/xolive.jpg?v3') }});
    }

    .x-section > .section-overlay {
        min-height: 100vh;
    }

    .rt-section {
        background-image: url({{ asset('img/photo/live.jpg') }});
        background-position: center bottom;
    }

    .section-overlay {
        background-color: rgba(26, 28, 32, .4);
        padding: 4rem 0;
    }

    section h1 {
        margin: 0 0 1rem;
        font-size: 48px;
    }

    section h2 {
        margin: 0 0 1rem;
        font-size: 32px;
    }

    .browser-mockup {
        margin: 2rem auto 0;
        max-width: 1024px;
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
        width: 192px;
        margin: 0 0 2rem;
    }

    .more-down {
        text-align: center;
        position: absolute;
        right: 0;
        left: 0;

                transform: translateY(calc(-100% + -2rem));
        -webkit-transform: translateY(calc(-100% + -2rem));
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
        margin: 0,
        responsiveClass: true,
        smartSpeed: 500,
        dots: false,
        loop: true,
        responsive: {
            0: { items: 1 },
            500: { items: 1 },
            768: { items: 1 }
        },
        autoHeight: true,
        navText: [
            '<div class="nav-btn prev-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_left</i></div>',
            '<div class="nav-btn next-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_right</i></div>'
        ],
        nav: true,
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
        <div class="section-overlay">
            <div class="container">
                <div style="max-width: 720px;">
                    <img align="Veri Zone" src="{{ asset('img/veri.zone_logo-white.svg') }}" class="logo" />
                    <br />
                    <br />
                    <br />
                    <h1 class="white-text">Sosyal Medya'ya değer katıyoruz!</h1>
                    <p class="mb-2">
                        <span class="white-text" style="font-size: 22px;">Veri Zone Teknoloji, sosyal medya ve haber verilerine; üstün filtreleme ve çeşitli analiz özellikleriyle, daha hızlı ve daha anlamlı bir şekilde ulaşmanızı sağlayan yazılımlar geliştirir.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="more-down">
        <a href="#" class="btn-floating btn-large pulse white" data-scroll-to=".main-slider">
            <i class="material-icons grey-text text-darken-2">keyboard_arrow_down</i>
        </a>
    </div>

    <section class="y-section">
        <div class="container">
            <div class="p-2 m-2">
                <div class="card card-unstyled">
                    <div class="card-content">
                        <span class="card-title">Veri Zone Teknoloji</span>
                        <br />
                        <p>Veri Zone Teknoloji ekibi olarak; Ankara, CoZone'da bol kahve eşliğinde, heyecanlı ve hızlı bir şekilde çalışmalarımızı sürdürmekteyiz.</p>
                        <br />
                        <p>Genç ve dinamik bir ekiple girmiş olduğumuz bu yolda, teknolojiyi en güncel noktalarından yakalayarak, siz değerli kullanıcılarımıza en iyi deneyimi yaşatmak yegâne hedefimizdir.</p>
                        <br />
                        <p>Yenilikçi yazılımların neredeyse tamamı yabancı şirketler tarafından üretilmektedir. Bu nedenle büyük teknolojiler üreten yerli şirket sayısı yok denecek kadar az.</p>
                        <p>Veri Zone Teknoloji olarak ülkemizi, dünya çapında temsil etmek üzere bu yolda büyük teknolojiler üreterek yürümeye devam ediyoruz.</p>
                    </div>
                </div>
            </div>
            <div class="item-group p-2">
                <div class="item p-2">
                    <i class="large material-icons analytics">poll</i>
                    <h5>Analiz</h5>
                    <ul class="p-1">
                        <li>- Türkçe için geliştirilmiş duygu analizleri.</li>
                        <li>- Cinsiyet ve yaş tespitleri.</li>
                        <li>- Nefret söylemi, şikayet, istek ve soru içeren verilere en hızlı şekilde ulaşın.</li>
                        <li>- Ürünlerinizi, markanızı veya rakiplerinizi detaylı bir şekilde inceleyin.</li>
                    </ul>
                </div>
                <div class="item p-2">
                    <i class="large material-icons realtime">subject</i>
                    <h5>Gerçek Zamanlı Veri</h5>
                    <ul class="p-1">
                        <li>- Gerçek zamanlı filtreler ile sadece istediğiniz içerikleri süzün.</li>
                        <li>- Trend olmaya çalışan konuları anında yakalayın.</li>
                        <li>- Ürün, marka veya rakipleriniz hakkında yapılan paylaşımları anında görün.</li>
                        <li>- İlgilendiğiniz içerikleri daha sonra incelemek üzere anında saklayın.</li>
                    </ul>
                </div>
                <div class="item p-2">
                    <i class="large material-icons rotate">toys</i>
                    <h5>Araçlar</h5>
                    <ul class="p-1">
                        <li>- Bir haber, tweet ve dahası hakkında detaylı incelemeler gerçekleştirin.</li>
                        <li>- Bahis veya çıplaklık içeren verileri karantinaya alın.</li>
                        <li>- Yapay zekanın yanılgılarını düzelterek Olive'in öğrenmesine katkıda bulunun.</li>
                    </ul>
                </div>
                <div class="item p-2">
                    <i class="large material-icons cloud">cloud</i>
                    <h5>Arşiv</h5>
                    <ul class="p-1">
                        <li>- Üstün arama filtreleri ile arama motoru deneyimini zirvede yaşayın.</li>
                        <li>- Ortak veri havuzu sayesinde çok daha fazla veriye erişin.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="owl-carousel main-slider">
            <div class="item">Online itibarınızı takip edin</div>
            <div class="item">Gündemi anlık trendlerle ve anlık akışlarla inceleyin</div>
            <div class="item">Gerçek zamanlı alarmlar oluşturun</div>
            <div class="item">Arama sonuçlarınızı görselleştirin</div>
            <div class="item">Rakiplerinizin ve sektörünüzün yeniliklerinden haberdar olun</div>
        </div>

        <div class="browser-mockup">
            <img src="{{ asset('img/search.jpg?v4') }}" alt="Olive Mockup" />
        </div>
    </section>

    <section class="rt-section">
        <div class="section-overlay">
            <div class="container">
                <h2 class="white-text">Tam Anlamıyla Gerçek Zamanlı!</h2>
                <div class="row">
                    <div class="col s12 m6">
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
                            <p class="white-text">Sayfayı yenile, yeni verileri yükle vb. ilkel yöntemlerden kurtulun!</p>
                            <p class="white-text">Belirlediğiniz kriterlere göre veriler anında önünüze düşsün!</p>
                            <p class="white-text">Ayrıca filtrelediğiniz verileri kaçırmadan görmenizi sağlayan eşsiz kullanıcı deneyimini Olive ile yaşayın!</p>

                            <br />

                            <span class="chip blue-grey white-text">haber</span>
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

    <section class="demo-section">
        <div class="container">
            <div class="row">
                <div class="col m12 l6">
                    <div class="card card-unstyled">
                        <div class="card-content">
                            <form id="demo-form" method="post" action="{{ route('demo.request') }}" class="json" data-callback="__demo_request">
                                <span class="card-title">Deneme Sürüşü</span>
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
                            <h4>Gerek duymuyorsanız ödemeyin!</h4>
                            <p>Hemen bilgilerinizi bırakın, en kısa sürede size tam özellikli bir Olive ile dönüş sağlayalım.</p>
                            <p>Olive'i denerken, kullanmadığınız özellikleri tespit edin, paketinizden çıkaralım!</p>
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
