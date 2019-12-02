@extends('layouts.app', [
    'title' => [
        'text' => $type['title']
    ],
    'description' => 'Veri Zone Teknoloji, Büyük Veri Takip ve Raporlama Merkezi!'
])

@push('local.styles')
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
        font-size: 20px;
    }
@endpush

@push('local.scripts')
    var phone_form = false;

    $(window).scroll(function() {
        var test_top = $('.section-test').offset().top;
        var scroll_top = $(this).scrollTop();

        if (scroll_top >= test_top && phone_form === false)
        {
            phone_form = true;

            modal({
                'id': 'info',
                'title': 'Bilgi Bırakın Sizi Arayalım!',
                'body': $('<div />', {
                    'html': $('<form />', {
                        'class': 'json',
                        'method': 'post',
                        'action': '{{ route('demo.request') }}',
                        'id': 'phone_form',
                        'data-callback': '__phone_form',
                        'html': [
                            $('<div />', {
                                'class': 'd-flex',
                                'html': [
                                    $('<div />', {
                                        'class': 'input-field',
                                        'html': [
                                            $('<input />', {
                                                'type': 'text',
                                                'class': 'validate',
                                                'name': 'name',
                                                'id': 'name',
                                                'maxlength': 100
                                            }),
                                            $('<label />', {
                                                'for': 'name',
                                                'html': 'Adınız'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text'
                                            })
                                        ]
                                    }),
                                    $('<div />', {
                                        'class': 'input-field',
                                        'html': [
                                            $('<input />', {
                                                'type': 'text',
                                                'class': 'validate',
                                                'name': 'corporate_name',
                                                'id': 'corporate_name',
                                                'maxlength': 100
                                            }),
                                            $('<label />', {
                                                'for': 'corporate_name',
                                                'html': 'Organizasyon Adınız'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text'
                                            })
                                        ]
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'input-field',
                                'html': [
                                    $('<input />', {
                                        'type': 'text',
                                        'class': 'validate',
                                        'name': 'phone',
                                        'id': 'phone',
                                        'maxlength': 32
                                    }),
                                    $('<label />', {
                                        'for': 'phone',
                                        'html': 'Telefon Numaranız'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text'
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'input-field',
                                'html': $('<div />', {
                                    'class': 'captcha',
                                    'data-id': 'phone-captcha'
                                })
                            })
                        ]
                    })
                }),
                'size': 'modal-medium',
                'options': {
                    dismissible: false
                },
                'footer': [
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat grey-text',
                       'html': keywords.cancel
                   }),
                   $('<a />', {
                       'href': '#',
                       'class': 'waves-effect btn-flat json',
                       'html': keywords.send,
                       'data-json-target': '#phone_form'
                   })
                ]
            })

            captcha()
            M.updateTextFields()
            $('input#phone').mask('(999) 999 99 99')
        }
    })

    function __phone_form(__, obj)
    {
        if (obj.status == 'ok')
        {
            flash_alert('Bilgileriniz Kaydedildi. Teşekkürler!', 'green white-text')

            $('#modal-info').modal('close')
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
                    <a href="{{ route('home', [ 'type' => $type['key'] ]) }}" class="brand-logo">
                        <img alt="{{ config('app.name') }}" src="{{ @$logo ? $logo : asset('img/veri.zone_logo.svg') }}" />
                    </a>
                    <ul id="nav-mobile" class="right hide-on-med-and-down dropdown-trigger" data-target="change" data-align="right">
                        <li>
                            <a href="#" class="btn-flat waves-effect">{{ $type['title'] }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <ul id="change" class="dropdown-content">
        @foreach ($types as $key => $t)
            <li>
                <a href="{{ route('home', [ $key => $key ]) }}">{{ $t['title'] }}</a>
            </li>
        @endforeach
    </ul>

    <!-- section 1 -->

    <section class="section section-header d-flex">
        <div class="section-overlay"></div>
        <div class="container align-self-center">
            <h1 class="mb-1">Web Takip ve Raporlama Merkezi</h1>
            <p class="mb-2 lead">{{ $type['description'] }}</p>
            <a href="{{ route('user.login') }}" class="btn-flat btn-large white waves-effect heartbeat mb-2">ÜCRETSİZ BAŞLAYIN</a>

            <div class="owl-carousel main-slider">
                <div class="item">
                    <div class="canvas">BOOLE OPERATÖRLERİ</div>
                </div>
                <div class="item">
                    <div class="canvas">SINIRSIZ SORGU!</div>
                </div>
                <div class="item">
                    <div class="canvas">SINIRSIZ SONUÇ!</div>
                </div>
                <div class="item">
                    <div class="canvas">EŞ ZAMANLI GRAFİKLER</div>
                </div>
            </div>
        </div>
    </section>

    @push('local.styles')
        .section-header {
            background-image: url('{{ asset('img/photo/section-home.jpg') }}');
            text-align: center;
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
            color: #fff;
        }
    @endpush

    @push('local.scripts')
        $('.main-slider').owlCarousel({
            responsiveClass: true,
            dots: false,
            nav: false,
            responsive: {
                0: { items: 1 },
                800: { items: 3 },
                1400: { items: 4 }
            }
        })
    @endpush

    <!-- section 2 -->

    <section class="section section-tools d-flex">
        <div class="container align-self-center">
            <h2 class="mb-1">En İyi Araçlar</h2>
            <p class="lead mb-2">Olive araçları sayesinde büyük veri yönetmek çok kolay!</p>

            <div class="row d-flex flex-wrap align-items-stretch mb-2">
                @foreach ($array as $key => $item)
                    <div class="col l4 m12 olive-tool {{ $key >= 3 ? 'hide' : '' }}">
                        <div class="card card-unstyled">
                            <div class="card-content">
                                <img alt="Icon" src="{{ $item['icon'] }}" style="width: 48px; height: 48px;" />
                                <br />
                                <h3 class="mb-1">{{ $item['title'] }}</h3>
                                <p class="lead grey-text">{{ $item['text'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="#" class="btn-flat btn-large waves-effect olive-tools" data-class=".olive-tool" data-class-remove="hide" data-class-hide=".olive-tools">Tüm Araçlar</a>
        </div>
    </section>

    @push('local.styles')
        .section-tools {
            text-align: center;
        }

        .section-tools h1,
        .section-tools h2,
        .section-tools h3 {
            color: #111;
        }
        .section-tools p.lead {
            color: #111;
        }
    @endpush

    <!-- section 4 -->

    <section class="section section-realtime d-flex">
        <div class="section-overlay"></div>
        <div class="container align-self-center">

            <div class="row">
                <div class="col m6 s12 right-align">
                    <h2 class="mb-1">Gerçek Zamanlı</h2>
                    <p class="lead mb-2">Tam anlamıyla gerçek zamanlı ekranlar!</p>

                    <span class="chip white-text">haber</span>
                    <span class="chip white-text">+bilgi</span>
                    <span class="chip white-text">+teknoloji</span>
                    <span class="chip white-text">+internet</span>
                    <br />
                    <span class="chip white-text">+türkiye</span>
                    <span class="chip white-text">+spor</span>
                    <span class="chip white-text">+futbol</span>
                    <span class="chip white-text">+basketbol</span>
                </div>
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
            </div>
        </div>
    </section>
    @push('local.styles')
        .section-realtime {
            background-image: url('{{ asset('img/photo/section-realtime.jpg') }}');
            text-align: center;
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

    <section class="section section-tools d-flex">
        <div class="container align-self-center">
            <h2 class="mb-1">İş Zekâsı</h2>
            <p class="lead mb-2">Çevrimiçi itibarınıza yönelik olası tehditleri herkesten önce fark edin, stratejinizi geliştirin ve harekete geçin.</p>

            <div class="browser-mockup technology-slider z-depth-5">
                <div class="owl-carousel">
                    <img src="https://veri.zone/img/mockup-slide-1.jpg" alt="Olive Mockup 1" />
                    <img src="https://veri.zone/img/mockup-slide-2.jpg" alt="Olive Mockup 2" />
                    <img src="https://veri.zone/img/mockup-slide-3.jpg" alt="Olive Mockup 3" />
                    <img src="https://veri.zone/img/mockup-slide-4.jpg" alt="Olive Mockup 4" />
                    <img src="https://veri.zone/img/mockup-slide-5.jpg" alt="Olive Mockup 5" />
                    <img src="https://veri.zone/img/mockup-slide-6.jpg" alt="Olive Mockup 6" />
                </div>
            </div>
        </div>
    </section>
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
    @push('local.styles')
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

    <!-- section 6 -->

    <section class="section section-test d-flex mb-1">
        <div class="section-overlay cyan darken-2"></div>
        <div class="container align-self-center">
            <div class="pl-2 lr-2 mb-2">
                <h2 class="mb-1">Ücretsiz Deneyin!</h2>
                <p class="lead mb-2">Olive'i 1 gün boyunca ücretsiz deneyebilirsiniz!<br />Deneme süreniz bittiğinde, bir çok özellikten <span class="white cyan-text text-darken-2">ücretsiz</span> faydalanmaya devam edebileceğinizi de unutmayın!</p>
                <a href="{{ route('user.login') }}" class="btn-flat btn-large white waves-effect heartbeat">ÜCRETSİZ DENEYİN</a>
            </div>

            <!-- logos -->

            <div class="d-flex flex-wrap justify-content-center">
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="https://www.btk.gov.tr/">
                        <img alt="Logo" src="{{ asset('img/logo-btk.png') }}" class="brand-logo" />
                    </a>
                </div>
                <!--
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="https://www.almbase.com/">
                        <img alt="Logo" src="{{ asset('img/logo-almbase.png') }}" class="brand-logo" />
                    </a>
                </div>
                -->
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="https://cozone.co/">
                        <img alt="Logo" src="{{ asset('img/logo-cozone.png') }}" class="brand-logo" />
                    </a>
                </div>
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="http://tto.sehir.edu.tr/">
                        <img alt="Logo" src="{{ asset('img/logo-sehir_tto.png') }}" class="brand-logo" />
                    </a>
                </div>
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="https://geometryventure.dev/">
                        <img alt="Logo" src="{{ asset('img/logo-geometry.png') }}" class="brand-logo" />
                    </a>
                </div>
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="https://www.mediaclub.com.tr/">
                        <img alt="Logo" src="{{ asset('img/logo-mediaclub.png') }}" class="brand-logo" />
                    </a>
                </div>
                <div class="p-1">
                    <a target="_blank" rel="nofollow" href="https://www.provizapromosyon.com/">
                        <img alt="Logo" src="{{ asset('img/logo-proviza.png') }}" class="brand-logo" />
                    </a>
                </div>
            </div>
        </div>
    </section>
    @push('local.styles')
        .section-test {
            text-align: center;
        }

        .brand-logo {
            width: 128px;
            height: auto;
        }
    @endpush
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
    <script src='//www.google.com/recaptcha/api.js'></script>
@endpush
