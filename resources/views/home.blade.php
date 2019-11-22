@extends('layouts.app', [
    'title' => [
        'text' => 'Veri Zone Teknoloji'
    ],
    'description' => 'Veri Zone Teknoloji, web ve sosyal medya verilerine anlam kazandıran teknolojiler geliştirir.',
    'footer_hide' => true
])

@section('content')
    <div id="fullpage">
        <div class="section section-home">
            <div class="d-table mx-auto p-2 center-align">
                <h1 class="mb-1">Büyük Veri Takip ve Raporlama Merkezi</h1>
                <p class="lead mb-2">eş zamanlı web takibi | kolay ve hızlı raporlar</p>
                <a href="{{ route('user.login') }}" class="btn-flat btn-large cyan white-text waves-effect heartbeat">ÜCRETSİZ DENEYİN</a>
            </div>
        </div>
        @push('local.styles')
            .section.section-home {
                background-image: url('{{ asset('img/photo/xolive.jpg?v5') }}');
            }
            .section.section-home h1 {
                margin: 0;
                font-size: 48px;
                color: #fff;
            }
            .section.section-home p.lead {
                margin: 0;
                font-size: 20px;
                color: #fff;
            }
        @endpush
        <div class="section section-1">
            <div class="container">
                <div class="limiter">
                    <img alt="Olive" src="{{ asset('img/olive_logo.svg') }}" class="logo mb-1" />
                    <p class="lead mb-1">Heyecanlıyız ve her şeyi merak ediyoruz! Markaları, kurumları, şirketleri ve kişileri! Web dünyası hızla gelişip büyürken, merakımızı gidermek de bir hayli zorlaştı. Bu durumu daha kolay hale getirmek için bol kahve eşliğinde geçirilen uzun ve zorlu mesailerin sonunda Olive'i geliştirdik.</p>
                    <p class="lead mb-1">Olive "Büyük Veri Takip ve Raporlama Merkezi", açık kaynak konuşmaları gerçek zamanlı takip etmek ve ilgilenilen konuların hızlı bir şekilde raporlanmasını sağlamak üzere tasarlanmıştır.</p>
                    <p class="lead">Olive ile Web ve sosyal medyada; şirketiniz, organizasyonunuz veya yöneticileriniz için içgörüler elde edebilir, müşteri ilişkilerinden kurumsal iletişime kadar her konudan haberdar olabilirsiniz.</p>
                </div>
            </div>
        </div>
        @push('local.styles')
            .section.section-1 {
                background-image: url('{{ asset('img/bg-small.svg?v1') }}');
            }
            .section.section-1 p.lead {
                margin: 0;
                font-size: 16px;
                color: #111;
            }
            .section.section-1 img.logo {
                width: auto;
                height: 64px;
            }
        @endpush
        <div class="section section-2">
            <div class="container">
                <div class="row">
                    <div class="col s12 m6">
                        <div class="browser-mockup mockup-news mb-2">
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
                        <h3 class="mb-1">GERÇEK ZAMANLI AKIŞ</h3>
                        <p class="lead mb-1">Belirlediğiniz kriterlere göre, veriler anında ekranınıza düşsün!</p>
                        <p class="lead mb-1">Ayrıca çeşitli araçlar sayesinde, filtrelediğiniz verileri veya gündemi kaçırmadan takip edebilirsiniz.</p>

                        <span class="chip cyan white-text">haber</span>
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
            </div>
        </div>
        @push('local.styles')
            /*!
             * main slider
             */
            .main-slider .owl-stage-outer {
                margin: 0;
                padding: 0;
                font-size: 16px;
            }
            .main-slider .item {
                display: block;
                padding: 16px;
            }
            .main-slider .item > .canvas {
                color: #fff;
                text-align: center;
                padding: 16px 0;
            }

            .section.section-2 {
                background-image: url('{{ asset('img/photo/olivey.jpg?v1') }}');
            }
            .section.section-2 h3 {
                margin: 0;
                font-size: 32px;
                color: #fff;
            }
            .section.section-2 p.lead {
                margin: 0;
                font-size: 16px;
                color: #fff;
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
        @endpush
        @push('local.scripts')
            $('.main-slider').owlCarousel({
                responsiveClass: true,
                smartSpeed: 200,
                dots: false,
                nav: false,
                responsive: {
                    0: { items: 1 },
                    800: { items: 2 },
                    1400: { items: 3 }
                },
                loop: true,
                autoplay: true,
                autoplayTimeout: 1000,
                autoplayHoverPause: true,
                autoHeight: true
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
        <div class="section section-3">
            <div class="center-align">
                <h3 class="mb-1">SÜREKLİ GELİŞEN TEKNOLOJİ</h3>
                <p class="lead mb-2">Olive, sürekli gelişen bir teknolojiye sahiptir. Her sabah yeni bir özellikle karşılaşabilirsiniz.</p>
                <div class="browser-mockup mockup-slider z-depth-5">
                    <div class="owl-carousel">
                        <img src="{{ asset('img/mockup-slide-1.jpg?v1') }}" alt="Olive Mockup 1" />
                        <img src="{{ asset('img/mockup-slide-2.jpg?v1') }}" alt="Olive Mockup 2" />
                        <img src="{{ asset('img/mockup-slide-3.jpg?v1') }}" alt="Olive Mockup 3" />
                        <img src="{{ asset('img/mockup-slide-4.jpg?v1') }}" alt="Olive Mockup 4" />
                        <img src="{{ asset('img/mockup-slide-5.jpg?v1') }}" alt="Olive Mockup 4" />
                    </div>
                </div>
            </div>
        </div>
        @push('local.styles')
            .section.section-3 {
                background-image: url('{{ asset('img/obg.svg?v1') }}');
            }
            .section.section-3 h3 {
                margin: 0;
                font-size: 32px;
            }
            .section.section-3 p.lead {
                margin: 0;
                font-size: 20px;
            }

            /*!
             * mockup slider
             */
            .mockup-slider {
                margin: 0 auto;
                padding: 0;

                max-width: 1024px;
            }
            .mockup-slider .owl-stage-outer {
                margin: 0;
                padding: 0;

                width: 100%;
                background-color: #fff;
            }
        @endpush
        @push('local.scripts')
            $('.mockup-slider').children('.owl-carousel').owlCarousel({
                responsiveClass: true,
                smartSpeed: 100,
                dots: false,
                nav: false,
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 2000,
                autoplayHoverPause: true,
                lazyLoad: true,
                center: true
            })

            $('.main-slider').owlCarousel({
                responsiveClass: true,
                smartSpeed: 200,
                dots: false,
                nav: false,
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 1000,
                autoplayHoverPause: true,
                autoHeight: true
            })
        @endpush
        <div class="section section-4">
            <div class="container">
                <div class="d-flex justify-content-between">
                    <div class="align-self-center hide-on-med-and-down">
                        <div class="browser-mockup mockup-news z-depth-5" style="max-width: 100%;">
                            <img src="{{ asset('img/mockup-slide-6.jpg?v1') }}" alt="Olive Mockup 6" />
                        </div>
                    </div>
                    <div class="align-self-center p-2">
                        <div class="pl-2 lr-2">
                            <h3 class="mb-2">BULUT RAPORLAMA</h3>
                            <p class="lead mb-1">Olive Rapor Editörü sayesinde, araştırmanızı yaparken eş zamanlı olarak ve sadece tıklamalar ile hızlı bir şekilde raporunuzu oluşturabilirsiniz.</p>
                            <p class="lead mb-1">Olive Bulut Raporlama sistemi raporlarınızın kopyalanması engel olur.</p>
                            <p class="lead mb-1">Raporunuz bulutta güvende! Sadece bir bağlantı ile raporunuzu hedefine ulaştırabilirsiniz. Ayrıca raporunuzu şifreleyerek, sadece hedef kullanıcının okumasını sağlayabilirsiniz.</p>
                            <p class="lead">Tüm araçlarda olduğu gibi raporlarınızı da limitsizce oluşturabilirsiniz.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @push('local.styles')
            .section.section-4 {
                background-image: url('{{ asset('img/photo/olivez.jpg?v1') }}');
            }
            .section.section-4 h3 {
                margin: 0;
                font-size: 32px;
                color: #fff;
            }
            .section.section-4 p.lead {
                margin: 0;
                font-size: 16px;
                color: #fff;
            }
        @endpush
        <div class="section section-5">
            <div class="container">
                <div class="center-align">
                    <h3 class="mb-1">EN İYİ ARAÇLAR</h3>
                    <p class="lead">Eş zamanlı web ve sosyal medya takip, ölçümleme, analiz ve içgörü için en iyi iş zekası platformu.</p>
                    <p class="lead mb-2">Çevrimiçi itibarınıza yönelik olası tehditleri herkesten önce fark edin, stratejinizi geliştirin ve harekete geçin.</p>
                </div>

                <div class="media-cards owl-carousel">
                    @foreach ($array as $key => $item)
                        <div class="p-1">
                            <div class="d-flex mb-1">
                                <span class="circle-rank z-depth-1 white align-self-center mr-1 d-flex justify-content-end" style="background-image: url('{{ $item['icon'] }}');">
                                    <span class="align-self-end">{{ $key+1 }}</span>
                                </span>
                                <h5 class="align-self-center m-0">{{ $item['title'] }}</h5>
                            </div>
                            <p class="lead left-align">{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @push('local.styles')
            .section.section-5 {
                background-image: url('{{ asset('img/bg-small.svg?v1') }}');
            }
            .section.section-5 h3 {
                margin: 0;
                font-size: 32px;
            }
            .section.section-5 p.lead {
                margin: 0;
                font-size: 16px;
            }
        @endpush
        @push('local.scripts')
            $('.media-cards').owlCarousel({
                responsive:{
                    0: { items: 1 },
                    1024: { items: 2 },
                    1366: { items: 3 }
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
        @endpush
        <div class="section section-6">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between">
                    <div class="align-self-start p-1">
                        <p class="lead">Mustafa Kemal Mh. Dumlupınar Blv. ODTÜ Teknokent Bilişim İnovasyon Merkezi</p>
                        <p class="lead">280/G No:1260 Alt Zemin Kat Çankaya, Ankara</p>
                        <br />
                        <a class="d-table" href="tel:+908503021630">+90 850 302 16 30</a>
                        <a class="d-table" href="mailto:bilgi@veri.zone">bilgi@veri.zone</a>
                    </div>
                    <div class="align-self-start p-1">
                        <ul class="collection collection-unstyled">
                            <li class="collection-item">
                                <a class="grey-text" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a>
                            </li>
                            <li class="collection-item">
                                <a class="grey-text" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a>
                            </li>
                            <li class="collection-item">
                                <a class="grey-text" href="{{ route('page.view', 'cerez-politikasi') }}">Çerez Politikası</a>
                            </li>
                            <li class="collection-item">
                                <a class="grey-text" href="{{ route('page.view', 'hakkimizda') }}">Hakkımızda</a>
                            </li>
                            <li class="collection-item">
                                <a class="grey-text" href="{{ route('page.view', 'iletisim') }}">İletişim</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @push('local.styles')
            .section.section-6 h3 {
                margin: 0;
                font-size: 32px;
            }
            .section.section-6 p.lead {
                margin: 0;
                font-size: 16px;
            }
        @endpush
    </div>

    <div id="footer">
        <div class="d-flex">
            <div class="logo align-self-center">
                <img alt="Veri Zone Teknoloji" class="logo" src="{{ asset('img/veri.zone_logo.svg') }}" />
            </div>
            <div class="social align-self-center ml-auto">
                <a target="_blank" href="https://twitter.com/verizonetek" class="btn-flat btn-floating">
                    <i class="social-icon icon-twitter white-text cyan">&#xe803;</i>
                </a>
                <a target="_blank" href="https://www.linkedin.com/company/verizonetek/" class="btn-flat btn-floating">
                    <i class="social-icon icon-linkedin white-text cyan">&#xe805;</i>
                </a>
                <a target="_blank" href="https://www.instagram.com/verizonetek/" class="btn-flat btn-floating">
                    <i class="social-icon icon-instagram white-text cyan">&#xe808;</i>
                </a>
            </div>
        </div>
    </div>
    <div class="cookie-alert z-depth-1 grey lighten-4 hide">
        <a href="{{ route('page.view', 'cerez-politikasi') }}" class="blue-grey-text" style="font-weight: bold;">Çerezler</a> özel bir deneyim sunarak ziyaretçilerimize daha iyi hizmet vermemizi ve daha faydalı bilgiler sunmak üzere kendi dahili amaçlarımız için kullanılacaktır.
        <a href="#" class="close">
            <i class="material-icons">close</i>
        </a>
    </div>
@endsection

@push('local.scripts')
    var myFullpage = new fullpage('#fullpage', {
        navigation: true,
        navigationPosition: 'right',
        scrollBar: true,
        anchors: [ 'ana-sayfa', 'olive', 'gercek-zamanli', 'gorseller', 'bulut-raporlama', 'araclar', 'iletisim' ],
        navigationTooltips: [ 'Ana Sayfa', 'Olive', 'Gerçek Zamanlı', 'Görseller', 'Bulut Raporlama', 'Araçlar', 'İletişim' ],
        showActiveTooltip: true
    })
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/fullpage.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/fullpage.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.styles')
    .section {
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
    }

    #footer {
        position: fixed;
        display: block;
        width: 100%;
        z-index: 9;
        text-align: center;
        padding: 1rem;
    }

    #footer{
        bottom: 0;
    }
    #footer > .d-flex > .logo > img {
        width: auto;
        height: 48px;
    }

    #fp-nav ul li a span {
        background-color: #fff;
    }

    .limiter {
        max-width: 70vh;
    }

    /*!
     * cookie
     */
    .cookie-alert {
        position: fixed;

        top: 1rem;
        right: auto;
        bottom: auto;
        left: 1rem;

        z-index: 1000;

        background-color: #fff;

        border-radius: 3px;

        padding: 1rem 1.5rem 1rem;

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

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush
