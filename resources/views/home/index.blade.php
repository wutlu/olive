@extends('layouts.app', [
    'title' => [
        'text' => $type ? $type['title'] : 'Veri Zone Teknoloji'
    ],
    'description' => 'Veri Zone Teknoloji, Büyük Veri Takip ve Raporlama Merkezi!',
    'chat' => true
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

    @media (max-width: 1024px) {
        .section {
            width: 100vw;
        }
    }

    .section h1,
    .section h2,
    .section h3 {
        margin: 0;
        padding: 0;
        color: #fff;
        font-weight: bold;
    }
    .section h1 {
        font-size: 32px;
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
        font-weight: bold;
    }

    .brand-logo {
        width: 128px;
        height: auto;
    }
@endpush

@push('local.scripts')
    var phone_form = false;
    $(window).scroll(function() {
        var test_top = ($('.section-test').offset().top) - 96;
        var scroll_top = $(this).scrollTop();

        if (scroll_top >= test_top && phone_form === false)
        {
            phone_form = true;

            modal({
                'id': 'info',
                'title': 'Telefon Bırakın Sizi Arayalım!',
                'class': 'with-bg',
                'body': $('<div />', {
                    'html': $('<form />', {
                        'class': 'json',
                        'method': 'post',
                        'action': '{{ route('demo.request') }}',
                        'id': 'phone_form',
                        'data-callback': '__phone_form',
                        'html': [
                            $('<p />', {
                                'class': 'grey-text mb-0',
                                'html': 'Sizi anlamak ve daha iyi bir hizmet verebilmek için aramamıza izin verin.'
                            }),
                            $('<div />', {
                                'class': 'd-flex',
                                'html': [
                                    $('<div />', {
                                        'class': 'input-field',
                                        'css': { 'width': '50%' },
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
                                                'html': 'Adınız *'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text',
                                                'html': 'Size nasıl hitap edelim?'
                                            })
                                        ]
                                    }),
                                    $('<div />', {
                                        'class': 'input-field',
                                        'css': { 'width': '50%' },
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
                                                'html': 'Organizasyon'
                                            }),
                                            $('<span />', {
                                                'class': 'helper-text',
                                                'html': 'Varsa kurum, şirket veya organizasyon adınız?'
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
                                        'html': 'Telefon Numaranız *'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text',
                                        'html': 'Size hangi numaradan ulaşalım?'
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
                'size': 'modal-large',
                'options': {
                    dismissible: false
                },
                'footer': [
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat red-text',
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
    <div class="navbar-fixed">
        <nav id="main-nav">
            <div class="container">
                <div class="nav-wrapper">
                    <a href="{{ route('home') }}" class="brand-logo left">
                        <img alt="{{ config('app.name') }}" src="{{ asset('img/veri.zone_logo.svg') }}" />
                    </a>

                    <ul class="right">
                        <li class="hide-on-med-and-down">
                            <a href="#" class="white blue-text text-darken-2 fonted-menu">+90 850 302 16 30</a>
                        </li>
                        <li>
                            <a href="{{ route('user.login') }}" class="blue darken-2 white-text waves-effect">Olive'e Git</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="cookie-alert z-depth-1 hide">
        <a href="{{ route('page.view', 'cerez-politikasi') }}" class="blue-text">Çerezler</a> özel bir deneyim sunarak ziyaretçilerimize daha iyi hizmet vermemizi ve daha faydalı bilgiler sunmak üzere kendi dahili amaçlarımız için kullanılacaktır.
    </div>

    <!-- section 1 -->

    <section class="section section-header d-flex">
        <div class="container align-self-center">
            <div class="d-flex flex-wrap row">
                <div class="col s12 m12 l6 xl8 p-1 align-self-center left-align">
                    <h1 class="black-text mb-2">Dijital İtibarınızı Anlık Olarak Takip Edin</h1>
                    @if ($type)
                        <p class="lead black-text mb-1">{{ $type['description'] }}</p>
                    @else
                        <p class="lead black-text mb-2">Yalnızca markanızı ve kampanyalarınızı değil, aynı zamanda çok sayıda rakip ve segmentin kendisini izleyin ve ölçün.</p>
                        <div class="mb-2">
                            <a href="{{ route('home', [ 'type' => 'kisiler' ]) }}" class="blue-text text-darken-2" style="padding: .4rem; margin: 2px;">Kişiler</a>
                            <a href="{{ route('home', [ 'type' => 'markalar' ]) }}" class="blue-text text-darken-2" style="padding: .4rem; margin: 2px;">Markalar</a>
                            <a href="{{ route('home', [ 'type' => 'reklam-ajanslari' ]) }}" class="blue-text text-darken-2" style="padding: .4rem; margin: 2px;">Reklam Ajansları</a>
                        </div>
                    @endif
                </div>
                <div class="col s12 m12 l6 xl4 p-1 align-self-center">
                    <div class="card">
                        <div class="card-content">
                            <div class="p-1 left-align">
                                <div class="input-field">
                                    <input data-name="report" type="text" name="report_subject" data-alias="subject" id="report_subject" placeholder="Konu" maxlength="155" />
                                    <span class="helper-text">Rapor almak istediğiniz konuyu belirtin. Bu bir isim de olabilir.</span>
                                </div>
                                <div>
                                    <a href="#" class="chip waves-effect waves-light" data-input="#report_subject" data-input-value='"Ahmet Kural"'>"Ahmet Kural"</a>
                                    <a href="#" class="chip waves-effect waves-light" data-input="#report_subject" data-input-value="Dolar">Dolar</a>
                                    <a href="#" class="chip waves-effect waves-light" data-input="#report_subject" data-input-value="Migros">Migros</a>
                                    <a href="#" class="chip waves-effect waves-light" data-input="#report_subject" data-input-value="Ankara && Deprem" data-tooltip="ve">Ankara && Deprem</a>
                                    <a href="#" class="chip waves-effect waves-light" data-input="#report_subject" data-input-value="Ankara || İstanbul" data-tooltip="veya">Ankara || İstanbul</a>
                                </div>
                                <div class="input-field">
                                    <input data-name="report" type="text" name="report_name" data-alias="name" placeholder="Sizin veya Şirketinizin Adı" maxlength="55" />
                                    <span class="helper-text">Size hitap edebilmemiz için bir isim girin.</span>
                                </div>
                                <div class="input-field">
                                    <input data-name="report" type="text" name="report_phone" data-alias="phone" placeholder="GSM" />
                                    <span class="helper-text">Rapor sonucunu ücretsiz sms olarak göndereceğimiz bir gsm numarası girin.</span>
                                </div>
                                <div class="center-align">
                                    <a href="#" class="btn-flat btn-large align-self-center white waves-effect heartbeat" data-trigger="report">Ücretsiz Rapor Al</a>
                                    <p class="m-0 grey-text">
                                        veya <a href="{{ route('user.login') }}">dene!</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('local.scripts')
        $('input[name=report_phone]').mask('(999) 999 99 99')

        function __report_form(__, obj)
        {
            if (obj.status == 'ok')
            {
                $('[data-name=report]').val('')

                $('#modal-info').modal('close')

                flash_alert('Başarılı!', 'green white-text')

                return modal({
                    'id': 'alert',
                    'body': 'Raporunuz sıraya alındı. Hazır olduğunda SMS ile bilgilendirileceksiniz.',
                    'title': keywords.info,
                    'size': 'modal-small',
                    'options': {},
                    'footer': [
                       $('<a />', {
                           'href': '#',
                           'class': 'modal-close waves-effect btn-flat',
                           'html': keywords.ok
                       })
                    ]
                })
            }
        }

        $(document).on('click', '[data-trigger=report]', function() {
            modal({
                'id': 'info',
                'title': 'Ücretsiz Rapor Al',
                'body': $('<div />', {
                    'html': $('<form />', {
                        'class': 'json',
                        'method': 'post',
                        'action': '{{ route('report.request') }}',
                        'id': 'report_form',
                        'data-callback': '__report_form',
                        'data-include': 'report_name,report_subject,report_phone',
                        'html': [
                            $('<p />', {
                                'class': 'mb-1 red-text heartbeat',
                                'html': 'Raporun iyi bir sonuç vermesi için Olive\'in sizi anlaması gerekiyor. Lütfen anlamlı bir kelime veya isim girdiğinizden emin olun.'
                            }),
                            $('<label />', {
                                'html': [
                                    $('<input />', {
                                        'type': 'checkbox',
                                        'name': 'terms',
                                        'value': 1
                                    }),
                                    $('<span />', {
                                        'html': [
                                            $('<a />', {
                                                'class': 'blue-grey-text text-darken-2',
                                                'target': '_blank',
                                                'href': '{{ route('page.view', 'kullanim-kosullari') }}',
                                                'html': 'Kullanım Koşulları'
                                            }),
                                            $('<span />', {
                                                'html': 've',
                                                'class': 'pl-1 pr-1'
                                            }),
                                            $('<a />', {
                                                'class': 'blue-grey-text text-darken-2',
                                                'target': '_blank',
                                                'href': '{{ route('page.view', 'gizlilik-politikasi') }}',
                                                'html': 'Gizlilik Politikası'
                                            }),
                                            $('<span />', {
                                                'html': 'şartlarını okudum ve kabul ediyorum.',
                                                'class': 'pl-1 pr-1'
                                            })
                                        ]
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
                       'class': 'modal-close waves-effect btn-flat red-text',
                       'html': keywords.cancel
                   }),
                   $('<a />', {
                       'href': '#',
                       'class': 'waves-effect btn-flat json',
                       'html': keywords.send,
                       'data-json-target': '#report_form'
                   })
                ]
            })

            captcha()
            M.updateTextFields()
        })
    @endpush

    @push('local.styles')
        .section-header {
            text-align: center;
            background-image: url('{{ asset('img/banner.svg') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }
        .section-header h1 {
            font-size: 48px;
            text-transform: uppercase;
            max-width: 400px;
        }
        .section-header p.lead {
            font-size: 24px;
            max-width: 400px;
        }

        .header-logos {
            opacity: .6;
        }
    @endpush

    <div class="d-flex flex-wrap justify-content-center header-logos">
        <div class="p-1">
            <a target="_blank" rel="nofollow" href="https://www.btk.gov.tr/">
                <img alt="Logo" src="{{ asset('img/logo-btk.png') }}" class="brand-logo" />
            </a>
        </div>
        <div class="p-1">
            <a target="_blank" rel="nofollow" href="https://www.almbase.com/">
                <img alt="Logo" src="{{ asset('img/logo-almbase.png') }}" class="brand-logo" />
            </a>
        </div>
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

    <!-- section 6 -->

    <section class="section section-test d-flex with-bg">
        <div class="section-overlay blue darken-2"></div>
        <div class="container align-self-center">
            <div class="pl-2 pr-2 mb-2">
                <h2 class="mb-1">Ücretsiz Deneyin!</h2>
                <p class="lead mb-2">Olive'i 7 gün boyunca ücretsiz olarak deneyebilirsiniz!<br />Deneme süreniz bittiğinde, bir çok özellikten <span class="white blue-text text-darken-2">ücretsiz</span> faydalanmaya devam edebileceğinizi de unutmayın!</p>
                <a href="{{ route('user.login') }}" class="btn-flat btn-large white waves-effect heartbeat">ÜCRETSİZ DENEYİN</a>
            </div>
        </div>
    </section>

    @push('local.styles')
        .section-test {
            text-align: center;
            margin: 0 auto 10vh;
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
