@extends('layouts.app', [
    'title' => [
        'text' => '8vz'
    ],
    'description' => '8vz, Büyük Veri Takip ve Raporlama Merkezi!',
    'chat' => true
])

@push('local.styles')
    .section {
        margin: 0 auto;
        padding: 0;
        width: 100vw;
        position: relative;
    }
    .section > .container {
        padding: 20vh 0;
        position: relative;
        z-index: 1;
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
        font-size: 48px;
        text-transform: uppercase;
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

    .section .text-container {
        max-width: 600px;
    }

    .section-text > .container {
        padding: 10vh 0;
    }
    .section-text p.lead {
        color: #263238;
        text-align: center;
    }
@endpush

@section('content')
    <div class="navbar-fixed">
        <nav id="main-nav">
            <div class="container">
                <div class="nav-wrapper">
                    <a href="{{ route('home') }}" class="brand-logo left">
                        <img alt="{{ config('app.name') }}" src="{{ asset('img/8vz.net_logo.svg') }}" />
                    </a>

                    <ul class="right">
                        <li class="hide-on-med-and-down">
                            <a href="tel:8503021630" class="white fonted-menu">+90 850 302 16 30</a>
                        </li>
                        <li class="btn-li d-flex">
                            <a href="{{ route('user.login') }}" class="btn blue darken-4 white-text waves-effect align-self-center">
                                <span class="hide-on-med-and-down">Ücretsiz</span>
                                Deneyin
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <!-- section 1 -->

    <section class="section section-header">
        <div class="container">
            <div class="text-container">
                <h1 class="mb-1">Dijital İtibarınızı Anlık Olarak Takip Edin</h1>
                <p class="lead mb-2">Yalnızca markanızı ve kampanyalarınızı değil, aynı zamanda çok sayıda rakip ve segmenti izleyin ve ölçümleyin.</p>
                <a href="{{ route('user.login') }}" class="btn btn-large blue darken-4 white-text waves-effect">Ücretsiz Dene</a>
                <a href="{{ route('user.login') }}?q=giris" class="btn-flat btn-large blue-grey darken-4 white-text waves-effect">Giriş Yap</a>
            </div>
        </div>
    </section>

    <div class="d-table mx-auto mt-2">
        <a href="#" class="btn-floating btn-large blue-grey darken-4 pulse" data-scroll-to=".header-logos">
            <i class="material-icons">arrow_downward</i>
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-center header-logos mt-2 mb-2">
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

    @push('local.styles')
        .header-logos {
            opacity: .6;
        }
        .header-logos img.brand-logo {
            width: 148px;
            height: auto;
        }

        .section-header {
            background-position: center;
            background-size: cover;
            background-color: #263238;
        }
        .section-header h1 {
            color: #fff;
        }
        .section-header p.lead {
            color: #fff;
        }
        .section-header > .container {
            background-repeat: no-repeat;
            background-position: right 32px center;
            background-image: url('{{ asset('img/reputation.png') }}');
            background-size: 400px;
        }
        @media (max-width: 1024px) {
            .section-header > .container {
                background: none;
            }
        }
    @endpush

    <!-- section 2 -->

    <section class="section section-rebuild blue-grey">
        <div class="container">
            <h2 class="mb-1">Bugüne kadar kullandığınız araçları unutun!</h2>
            <p class="lead mb-1">Eş zamanlı akışlar, trendler ve bulut rapor editörü yeniliklerden sadece birkaçı.</p>
            <a href="{{ route('home.features') }}" class="btn blue-grey darken-2 waves-effect">Yeniliklere Göz Atın</a>
        </div>
    </section>

    @push('local.styles')
        .section-rebuild > .container {
            padding: 10vh 0 10vh 348px;
            background-repeat: no-repeat;
            background-position: left 32px center;
            background-image: url('{{ asset('img/rebuild.png') }}');
            background-size: 256px;
        }
        @media (max-width: 1024px) {
            .section-rebuild > .container {
                background: none;
                padding: 10vh 0;
            }
        }
    @endpush

    <!-- section 3 -->

    <section class="section section-best center-align">
        <div class="container">
            <h2 class="mb-1">Erken Kalkan Yol Alır</h2>
            <p class="lead">Hızlı davranın, ilgilendiğiniz konulardan ilk siz haberdar olun!</p>
        </div>
    </section>

    @push('local.styles')
        .section-best > .container {
            padding: 10vh 0;
        }
        .section-best h2,
        .section-best p.lead {
            color: #263238;
        }
    @endpush

    <!-- section 4 -->

    <div class="section section-mockup model-1 d-flex flex-wrap justify-content-between">
        <div class="item align-self-center">
            <div class="browser-mockup z-depth-5">
                <img src="{{ asset('img/mockup-slide-1.jpg?v=1') }}" alt="Mockup 1" />
            </div>
        </div>
        <div class="item align-self-center">
            <h3 class="mb-1">Özgürce Arama Yapın</h3>
            <p class="lead">Bir Finansal Araştırmacı, Kamu Sektörü Analisti veya Strateji Uzmanı olun, ilgi alanınız hakkında derin ve anlamlı bilgiler edinmek için VERİ.ZONU'u kullanabilirsiniz.</p>
        </div>
    </div>

    <!-- section 5 -->

    <div class="section section-mockup model-2 d-flex flex-wrap justify-content-between">
        <div class="item align-self-center">
            <h3 class="mb-1">Eş Zamanlı Trendler</h3>
            <p class="lead">İnternette olan biten her şeyi <u>eş zamanlı</u> keşfedin.</p>
        </div>
        <div class="item align-self-center">
            <div class="browser-mockup z-depth-5">
                <img src="{{ asset('img/mockup-slide-3.jpg?v=2') }}" alt="Mockup 3" />
            </div>
        </div>
    </div>

    <!-- section 6 -->

    <div class="section section-mockup model-1 d-flex flex-wrap justify-content-between">
        <div class="item align-self-center">
            <div class="browser-mockup z-depth-5">
                <img src="{{ asset('img/mockup-slide-2.jpg?v=3') }}" alt="Mockup 2" />
            </div>
        </div>
        <div class="item align-self-center">
            <h3 class="mb-1">Bulut Rapor Editörü</h3>
            <p class="lead">Bir konu hakkında, sadece tıklamalarla en detaylı raporları oluşturun. Güne hızlı başlayın!</p>
        </div>
    </div>

    @push('local.styles')
        .section-mockup {
            max-width: 80%;
        }
        .section-mockup > .item {
            padding: 32px;
        }
        .section-mockup.model-1 > .item:first-child,
        .section-mockup.model-2 > .item:last-child {
            width: 70%;
        }
        .section-mockup.model-1 > .item:last-child,
        .section-mockup.model-2 > .item:first-child {
            width: 30%;
        }

        @media (max-width: 1024px) {
            .section-mockup > .item {
                width: 100% !important;
            }
        }

        .section-mockup h3 {
            color: #263238;
        }
        .section-mockup p.lead {
            color: #607d8b;
            font-weight: 400;
        }
    @endpush

    <!-- section 7 -->

    <div class="section section-mockup model-1 d-flex flex-wrap justify-content-between">
        <div class="item align-self-center">
            <div class="tr-map">
                <small class="state state-aydin" data-title="Aydın" style="background-color: rgb(255, 0, 0);">8</small>
                <small class="state state-kahramanmaras" data-title="Kahramanmaraş" style="background-color: rgb(219, 0, 0);">7</small>
                <small class="state state-kocaeli" data-title="Kocaeli" style="background-color: rgb(219, 0, 0);">7</small>
                <small class="state state-istanbul" data-title="İstanbul" style="background-color: rgb(219, 0, 0);">7</small>
                <small class="state state-izmir" data-title="İzmir" style="background-color: rgb(219, 0, 0);">7</small>
                <small class="state state-antalya" data-title="Antalya" style="background-color: rgb(146, 0, 0);">5</small>
                <small class="state state-bursa" data-title="Bursa" style="background-color: rgb(109, 0, 0);">4</small>
                <small class="state state-konya" data-title="Konya" style="background-color: rgb(109, 0, 0);">4</small>
                <small class="state state-nevsehir" data-title="Nevşehir" style="background-color: rgb(73, 0, 0);">3</small>
                <small class="state state-trabzon" data-title="Trabzon" style="background-color: rgb(73, 0, 0);">3</small>
                <small class="state state-van" data-title="Van" style="background-color: rgb(73, 0, 0);">3</small>
                <small class="state state-adana" data-title="Adana" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-erzincan" data-title="Erzincan" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-kayseri" data-title="Kayseri" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-kibris" data-title="Kıbrıs" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-malatya" data-title="Malatya" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-mus" data-title="Muş" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-osmaniye" data-title="Osmaniye" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-rize" data-title="Rize" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-samsun" data-title="Samsun" style="background-color: rgb(36, 0, 0);">2</small>
                <small class="state state-amasya" data-title="Amasya" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-ankara" data-title="Ankara" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-bolu" data-title="Bolu" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-diyarbakir" data-title="Diyarbakır" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-erzurum" data-title="Erzurum" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-karaman" data-title="Karaman" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-kars" data-title="Kars" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-kirsehir" data-title="Kırşehir" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-sakarya" data-title="Sakarya" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-yozgat" data-title="Yozgat" style="background-color: rgb(0, 0, 0);">1</small>
                <small class="state state-sanliurfa" data-title="Şanlıurfa" style="background-color: rgb(0, 0, 0);">1</small>
            </div>
        </div>
        <div class="item align-self-center">
            <h3 class="mb-1">Yerel Basın ve Demografi</h3>
            <p class="lead mb-1">Yerel veya genel basın farketmeksizin hakkınızda yazılanları en küçük kaynağına kadar en detaylı şekilde inceleyin.</p>
            <p class="lead">Tüketicilerinizin demografik bilgileri, davranışları, marka tercihleri ve ilgi grafiklerini analiz ederek mikroskobik olarak anlaşılmalarını sağlayın.</p>
        </div>
    </div>

    <!-- section 8 -->

    <section class="section section-realtime d-flex flex-wrap justify-content-center">
        <div class="item align-self-center p-2">
            <h2>Tam Anlamıyla Gerçek Zamanlı</h2>
        </div>
        <div class="item align-self-center p-2">
            <p class="lead">8vz dijitalde olan bitenleri; istediğiniz kriterler doğrultusunda, paylaşıldığı an önünüze getirir.</p>
        </div>
    </section>

    @push('local.styles')
        .section-realtime {
            padding: 10vh 0;
            width: 50%;
        }
        .section-realtime > .item {
            width: 50%;
        }

        @media (max-width: 1024px) {
            .section-realtime {
                width: 100%;
            }
        }

        .section-realtime h2,
        .section-realtime p.lead {
            color: #263238;
        }
        .section-realtime p.lead {
            font-weight: 400;
        }
    @endpush

    <!-- section 9 -->

    <div class="section section-realtime">
        <div class="center-align pb-1">
            <span class="chip red white-text">haber</span>
            <span class="chip">+bilgi</span>
            <span class="chip">+teknoloji</span>
            <span class="chip">+internet</span>
            <span class="chip">+türkiye</span>
            <span class="chip">+spor</span>
            <span class="chip">+futbol</span>
            <span class="chip">+basketbol</span>
        </div>
        <div class="p-1">
            <div class="browser-mockup">
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

    @push('local.styles')
        .section-realtime {
            padding: 32px 0;
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

    <!-- section 10 -->

    <section class="section section-text">
        <div class="container">
            <p class="lead">Kitlenizi tanıyın, marka ve kampanyalarınızı izleyin, tüketici duyarlılığı ve davranışındaki değişiklikleri derinlemesine keşfedin.</p>
        </div>
    </section>

    <!-- section 11 -->

    <div class="section section-report d-flex flex-wrap justify-content-center blue-grey darken-2">
        <div class="item align-self-center">
            <div class="input-field input-unstyled">
                <input class="white-text" data-name="report" type="text" name="report_subject" data-alias="subject" id="report_subject" placeholder="Konu" maxlength="155" />
                <span class="helper-text blue-grey-text text-lighten-4">Rapor almak istediğiniz konuyu belirtin. Bu bir isim de olabilir.</span>
            </div>
            <div>
                <a href="#" class="chip blue-grey lighten-4 waves-effect waves-light" data-input="#report_subject" data-input-value='"Ahmet Kural"'>"Ahmet Kural"</a>
                <a href="#" class="chip blue-grey lighten-4 waves-effect waves-light" data-input="#report_subject" data-input-value="Dolar">Dolar</a>
                <a href="#" class="chip blue-grey lighten-4 waves-effect waves-light" data-input="#report_subject" data-input-value="Migros">Migros</a>
                <a href="#" class="chip blue-grey lighten-4 waves-effect waves-light" data-input="#report_subject" data-input-value="Ankara && Deprem" data-tooltip="ve">Ankara && Deprem</a>
                <a href="#" class="chip blue-grey lighten-4 waves-effect waves-light" data-input="#report_subject" data-input-value="Ankara || İstanbul" data-tooltip="veya">Ankara || İstanbul</a>
            </div>
            <div class="input-field input-unstyled">
                <input class="white-text" data-name="report" type="text" name="report_name" data-alias="name" placeholder="Sizin veya Şirketinizin Adı" maxlength="55" />
                <span class="helper-text blue-grey-text text-lighten-4">Size hitap edebilmemiz için bir isim girin.</span>
            </div>
            <div class="input-field input-unstyled">
                <input class="white-text" data-name="report" type="text" name="report_phone" data-alias="phone" placeholder="GSM" />
                <span class="helper-text blue-grey-text text-lighten-4">Rapor sonucunu ücretsiz sms olarak göndereceğimiz bir gsm numarası girin.</span>
            </div>
            <a href="#" class="btn blue-grey darken-4 waves-effect" data-trigger="report">Ücretsiz Rapor Alın</a>
        </div>
        <div class="item align-self-center">
            <h2 class="mb-1 white-text">Üye Olmadan Deneyin</h2>
            <p class="lead blue-grey-text text-lighten-4">Bu formu doldurarak hızlı bir deneme yapabilirsiniz!</p>
        </div>
    </div>

    @push('local.styles')
        .section-report {
            padding: 10vh 0;
        }
        .section-report > .item {
            max-width: 400px;
            padding: 32px;
        }

        @media (max-width: 1024px) {
            .section-report {
                width: 100%;
            }
        }
    @endpush

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
                                'html': 'Raporun iyi bir sonuç verebilmesi için sizi anlamamız gerekiyor. Lütfen anlamlı bir kelime veya isim girdiğinizden emin olun.'
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
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
    <script src='//www.google.com/recaptcha/api.js'></script>
@endpush
