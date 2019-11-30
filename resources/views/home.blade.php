@extends('layouts.app', [
    'title' => [
        'text' => 'Veri Zone Teknoloji'
    ],
    'description' => 'Veri Zone Teknoloji, web ve sosyal medya verilerine anlam kazandıran teknolojiler geliştirir.'
])

@push('local.styles')
    .section {
        margin: 0 auto;
        padding: 10vh 0;
        width: 90vw;
        min-height: 80vh;
        background-repeat: no-repeat;
        background-position: center bottom;
        background-size: cover;
        background-attachment: fixed;
        position: relative;
    }
    .section > .section-overlay {
        background-color: rgba(0, 0, 0, .6);
        position: absolute;
        z-index: 0;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
    .section > .container {
        position: relative;
        z-index: 1;
    }

    .section h1,
    .section h2,
    .section h3 {
        margin: 0;
        padding: 0;
        color: #fff;
    }
    .section h1 {
        font-size: 48px;
    }
    .section h2 {
        font-size: 32px;
    }
    .section h3 {
        font-size: 24px;
    }
    .section p.lead {
        margin: 0;
        padding: 0;
        color: #fff;
        font-size: 18px;
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

        padding: 1rem 1.5rem;

        max-width: 300px;

        cursor: pointer;
    }

    @media (max-width: 576px) {
        .cookie-alert {
            max-width: 100%;
            
            top: 0;
            right: 0;
            bottom: auto;
            left: 0;

            border-radius: 0;
        }
    }
@endpush

@section('content')
    <div class="cookie-alert z-depth-1 hide">
        <a href="{{ route('page.view', 'cerez-politikasi') }}" class="cyan-text">Çerezler</a> özel bir deneyim sunarak ziyaretçilerimize daha iyi hizmet vermemizi ve daha faydalı bilgiler sunmak üzere kendi dahili amaçlarımız için kullanılacaktır.
    </div>

    <div class="navbar-fixed">
        <nav id="main-nav">
            <div class="nav-wrapper">
                <div class="container">
                    <a href="#" class="brand-logo">
                        <img alt="{{ config('app.name') }}" src="{{ @$logo ? $logo : asset('img/veri.zone_logo.svg') }}" />
                    </a>
                    <ul id="nav-mobile" class="right hide-on-med-and-down">
                        <li>
                            <a href="{{ route('home') }}" class="grey-text text-darken-2">Ana Sayfa</a>
                        </li>
                        <li>
                            <a href="{{ route('page.view', 'hakkimizda') }}" class="grey-text text-darken-2">Hakkımızda</a>
                        </li>
                        <!--
                            <li>
                                <a href="#" class="grey-text text-darken-2">Blog</a>
                            </li>
                        -->
                        <li>
                            <a href="{{ route('page.view', 'iletisim') }}" class="grey-text text-darken-2">İletişim</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <!-- section 1 -->

    <section class="section section-header d-flex">
        <div class="section-overlay"></div>
        <div class="container align-self-center">
            <h1 class="mb-1">Büyük Veri Takip ve Raporlama Merkezi</h1>
            <p class="mb-2 lead">eş zamanlı web takibi | kolay ve hızlı raporlar</p>
            <a href="{{ route('user.login') }}" class="btn-flat btn-large cyan white-text waves-effect heartbeat">ÜCRETSİZ DENEYİN</a>
        </div>
    </section>

    @push('local.styles')
        .section-header {
            background-image: url('{{ asset('img/photo/xolive.jpg?v9') }}');
        }

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
            text-align: center;
            padding: 16px 0;
        }
    @endpush

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

    @push('local.scripts')
        $('.main-slider').owlCarousel({
            responsiveClass: true,
            smartSpeed: 200,
            dots: false,
            nav: false,
            responsive: {
                0: { items: 1 },
                800: { items: 3 },
                1400: { items: 4 }
            },
            loop: true,
            autoplay: true,
            autoplayTimeout: 1000,
            autoplayHoverPause: true,
            autoHeight: true
        })
    @endpush

    <!-- section 2 -->

    <section class="section section-tools d-flex">
        <div class="section-overlay"></div>
        <div class="container align-self-center">
            <h2 class="mb-1">En İyi Araçlar</h2>
            <p class="lead mb-2">Çevrimiçi itibarınıza yönelik olası tehditleri herkesten önce fark edin, stratejinizi geliştirin ve harekete geçin.</p>

            <br />
            <br />

            <div class="owl-carousel media-cards">
                @foreach ($array as $key => $item)
                    <div class="center-align">
                        <span class="d-table mx-auto circle-rank mb-1 white" style="background-image: url('{{ $item['icon'] }}');">
                            <span class="align-self-end">{{ $key+1 }}</span>
                        </span>
                        <h3 class="mb-1">{{ $item['title'] }}</h3>
                        <p class="lead m-0 p-1">{{ $item['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @push('local.styles')
        .section-tools {
            background-image: url('{{ asset('img/photo/tools.jpg?v10') }}');
            margin: 0 auto 72px;
        }

        .media-cards .owl-stage-outer {
            margin: 0;
            padding: 0;
        }

        @push('local.scripts')
            $('.media-cards').owlCarousel({
                responsive:{
                    0: { items: 1 },
                    1024: { items: 2 },
                    1366: { items: 3 }
                },
                loop: true,
                center: true,

                responsiveClass: true,
                smartSpeed: 100,
                dots: false,
                nav: false,

                autoplay: true,
                autoplayTimeout: 2000,
                autoplayHoverPause: true
            })
        @endpush
    @endpush

    <!-- section 3 -->

    <section class="section section-technology d-flex">
        <div class="section-overlay"></div>
        <div class="container align-self-center">
            <h2 class="mb-1">İş Zekâsı</h2>
            <p class="lead mb-2">Eş zamanlı web takibi, ölçümleme, analiz ve içgörüler için en iyi iş zekâsı platformu.</p>

            <div class="browser-mockup technology-slider z-depth-5">
                <div class="owl-carousel">
                    <img src="{{ asset('img/mockup-slide-1.jpg?v2') }}" alt="Olive Mockup 1" />
                    <img src="{{ asset('img/mockup-slide-2.jpg?v2') }}" alt="Olive Mockup 2" />
                    <img src="{{ asset('img/mockup-slide-3.jpg?v2') }}" alt="Olive Mockup 3" />
                    <img src="{{ asset('img/mockup-slide-4.jpg?v2') }}" alt="Olive Mockup 4" />
                    <img src="{{ asset('img/mockup-slide-5.jpg?v2') }}" alt="Olive Mockup 4" />
                </div>
            </div>
        </div>
    </section>

    @push('local.styles')
        .section-technology {
            background-image: url('{{ asset('img/photo/technology.jpg?v9') }}');
            margin: 0 auto 12vw;
        }

        /*!
         * technology slider
         */
        .technology-slider {
            margin: 0 auto;
            max-width: 1024px;
        }
        .technology-slider .owl-stage-outer {
            margin: 0;
            padding: 0;
        }
    @endpush
    @push('local.scripts')
        $('.technology-slider').children('.owl-carousel').owlCarousel({
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
    @endpush

    <!-- section 4 -->

    <section class="section section-realtime d-flex">
        <div class="section-overlay"></div>
        <div class="container align-self-center">
            <h2 class="mb-1">Gerçek Zamanlı</h2>
            <p class="lead mb-2">Tam anlamıyla gerçek zamanlı ekranlar!</p>

            <div class="row">
                <div class="col m6 s12">
                    <div class="browser-mockup mb-2">
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
                <div class="col m6 s12">
                    <div class="p-1">
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
            </div>
        </div>
    </section>
    @push('local.styles')
        .section-realtime {
            background-image: url('{{ asset('img/photo/olivey.jpg?v9') }}');
        }

        /*!
         * main slider
         */
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

    <!-- section 5 -->

    <section class="section section-cloud d-flex">
        <div class="container align-self-center">
            <div class="d-flex justify-content-between">
                <div class="align-self-center hide-on-med-and-down">
                    <div class="browser-mockup mockup-news z-depth-1" style="max-width: 100%;">
                        <img src="{{ asset('img/mockup-slide-6.jpg?v9') }}" alt="Olive Mockup 6" />
                    </div>
                </div>
                <div class="align-self-center p-2">
                    <div class="pl-2 lr-2">
                        <h2 class="mb-1">Bulut Raporlama</h2>
                        <p class="lead mb-2">Olive Rapor Editörü sayesinde, araştırma yaparken sadece tıklamalar ile eş zamanlı ve hızlı bir şekilde raporunuzu oluşturabilirsiniz.</p>
                        <p class="lead">Raporlarınız bulutta güvende! Raporunuzu şifreleyin, sadece istediğiniz kişiler okusun.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @push('local.styles')
        .section-cloud h2,
        .section-cloud p.lead {
            color: #222;
        }
    @endpush

    <!-- section 6 -->

    <section class="section section-test d-flex mb-1">
        <div class="section-overlay cyan"></div>
        <div class="container align-self-center">
            <div class="pl-2 lr-2">
                <h2 class="mb-1">Deneme Turu</h2>
                <p class="lead mb-2">Olive'i ücretsiz deneyebilirsiniz! Deneme turunuz bittiğinde, bir çok özellikten ücretsiz faydalanmaya devam edebileceğinizi unutmayın!</p>
                <a href="{{ route('user.login') }}" class="btn-flat btn-large cyan darken-2 waves-effect heartbeat">ÜCRETSİZ DENEYİN</a>
            </div>
        </div>
    </section>
    @push('local.styles')
        .section-test h2,
        .section-test p.lead {
            color: #222;
        }
    @endpush
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/simpleParallax.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script type="text/javascript">
    var _smartsupp = _smartsupp || {};
    _smartsupp.key = '{{ config('services.smartsupp.code') }}';
    window.smartsupp||(function(d) {
      var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
      s=d.getElementsByTagName('script')[0];c=d.createElement('script');
      c.type='text/javascript';c.charset='utf-8';c.async=true;
      c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
    })(document);
    </script>
@endpush
