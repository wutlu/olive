@extends('layouts.app')

@push('local.styles')
    .main-slider {
    }
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
        background-image: url('{{ asset('img/bg-small.svg') }}');
        background-repeat: no-repeat;
        background-position: top right;
        background-size: contain;
        background-attachment: scroll;
        color: #fff;
    }

    .pricing-section {
        padding: 100px 0;
        background-image: url('{{ asset('img/bg-2.svg') }}');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-attachment: scroll;
    }

    .y-section {
        background-image: url({{ asset('img/obg.svg') }});
        background-size: contain;
        background-attachment: scroll;
        background-position: center bottom;
    }

    .x-section {
        background-image: url({{ asset('img/photo/xolive.jpg') }});
    }

    .rt-section {
        background-image: url({{ asset('img/photo/live.jpg') }});
        background-position: center bottom;
    }

    .x-section > .section-overlay {
        min-height: 100vh;
    }

    .section-overlay {
        background-color: rgba(26, 28, 32, .6);
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
        width: 128px;
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

    $('#dword').children('.text').typewrite({
        actions: [
            { delay: 500 },
            { type: '1k+ haber kaynağı' },
            { delay: 2000 },
            { select: { from: 0, to: 17 } },
            { delay: 1000 },
            { remove: { num: 17, type: 'whole' } },

            { type: 'günlük 50k+ haber' },
            { delay: 2000 },
            { select: { from: 0, to: 17 } },
            { delay: 1000 },
            { remove: { num: 17, type: 'whole' } },

            { type: '4 büyük sözlük' },
            { delay: 2000 },
            { select: { from: 0, to: 14 } },
            { delay: 1000 },
            { remove: { num: 14, type: 'whole' } },

            { type: 'günlük 40k+ entry' },
            { delay: 2000 },
            { select: { from: 0, to: 17 } },
            { delay: 1000 },
            { remove: { num: 17, type: 'whole' } },

            { type: 'aylık 200m+ tweet' },
            { delay: 2000 },
            { select: { from: 0, to: 17 } },
            { delay: 1000 },
            { remove: { num: 17, type: 'whole' } },

            { type: 'aylık 1m+ youtube video yorumu' },
            { delay: 2000 },
            { select: { from: 0, to: 30 } },
            { delay: 1000 },
            { remove: { num: 30, type: 'whole' } },

            { type: 'sürekli gelişen bir veri ekosistemi' }
        ]
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
    <section class="x-section">
        <div class="section-overlay">
            <div class="container">
                <img align="Olive" src="{{ asset('img/olive_logo-white.svg') }}" class="logo" />
                <h1 class="white-text">Medya & Sosyal Medya Takip Platformu</h1>
                <p id="dword" class="mb-2">
                    <span class="text white-text"></span>
                    &nbsp;
                </p>
                <a href="{{ route('dashboard') }}" class="btn-flat btn-large white waves-effect">Giriş Yapın</a>
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
            <div class="item-group pt-2" id="features">
                <div class="item">
                    <i class="large material-icons analytics">poll</i>
                    <h5>Analiz</h5>
                    <ul>
                        <li>- Duygusal analizler gerçekleştirin.</li>
                        <li>- Kitlenizi cinsiyet ve konumlarına göre ölçümleyin.</li>
                        <li>- Nefret söylemlerine ve soru içeriklerine anında erişin.</li>
                        <li>- Ürün veya markanızı rakiplerinizle kıyaslayın.</li>
                    </ul>
                </div>
                <div class="item">
                    <i class="large material-icons realtime">subject</i>
                    <h5>Gerçek Zamanlı Veri</h5>
                    <ul>
                        <li>- Herhangi bir konu trend olmadan gündemine hakim olun.</li>
                        <li>- Ürün veya markanızı anlık ve duygusal olarak takip edin.</li>
                        <li>- Anlık gündemi yakalayın ve daha sonra inceleyin.</li>
                    </ul>
                </div>
                <div class="item">
                    <i class="large material-icons rotate">toys</i>
                    <h5>Araçlar</h5>
                    <ul>
                        <li>- Duygusal analizler gerçekleştirin.</li>
                        <li>- Kitlenizi cinsiyet ve konumlarına göre ölçümleyin.</li>
                        <li>- Nefret söylemlerine ve soru içeriklerine anında erişin.</li>
                    </ul>
                </div>
                <div class="item">
                    <i class="large material-icons cloud">cloud</i>
                    <h5>Arşiv</h5>
                    <ul>
                        <li>- Kriter belirtin sizin için erişelim.</li>
                        <li>- Ortak veritabanı ile çok daha fazla veriye ulaşın.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="owl-carousel main-slider">
            <div class="item">Online itibarınızı takip edin</div>
            <div class="item">Gündemi anlık trendlerle veya anlık akışlarla inceleyin</div>
            <div class="item">Gerçek zamanlı alarmlar kurun</div>
            <div class="item">Arama sonuçlarınızı görselleştirin</div>
            <div class="item">Rakiplerinizin ve sektörünüzün yeniliklerinden haberdar olun</div>
        </div>

        <div class="browser-mockup">
            <img src="{{ asset('img/search.jpg') }}" alt="Olive Mockup" />
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
                                class="card time-line load"
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
                            <span class="chip teal white-text">haber</span>
                            <br />
                            <span class="chip white">+bilgi</span>
                            <span class="chip white">+teknoloji</span>
                            <span class="chip white">+internet</span>
                            <span class="chip white">+türkiye</span>
                            <br />
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
                M.toast({ html: 'Formunuzu Aldık!', classes: 'green darken-2' })
                M.toast({ html: 'Ekibimiz en kısa sürede sizinle iletişime geçecektir.', classes: 'teal' })

                __.find('input[type=text]').html('')
            }
        }
    @endpush

    @push('local.styles')
        .packages {
            width: 100%;
            padding: 2rem;
        }
        .packages > li {
            background-repeat: no-repeat;
            background-position: top right;
            background-size: 128px 128px;

            padding: 4rem;
        }
        .packages > li.starter {
            background-image: url('{{ asset('img/icons/pack/starter.svg') }}');
        }
        .packages > li.pro {
            background-image: url('{{ asset('img/icons/pack/pro.svg') }}');
        }
        .packages > li.enterprise {
            background-image: url('{{ asset('img/icons/pack/enterprise.svg') }}');
        }
        .packages > li h4 {
            font-size: 24px;
            margin: 0 64px 24px;
        }
        .packages > li span.price {
            font-size: 20px;
        }
    @endpush

    <section class="pricing-section">
        <div class="container">
            <h2>Planlar</h2>

            <ul class="d-flex flex-wrap justify-content-center packages">
                @foreach([
                    'starter' => [
                        'title' => 'Başlangıç',
                        'details' => [
                            'Kullanıcı' => 1,
                            'Gerçek Zamanlı Akış' => '1 grup',
                            'Alarm' => 1,
                            'Pin Grubu' => 1,
                            'Arşiv' => '7 Gün',
                        ],
                        'price' => config('formal.currency').' 196 +kdv / ay'
                    ],
                    'pro' => [
                        'title' => 'Profesyonel',
                        'details' => [
                            'Kullanıcı' => 2,
                            'Gerçek Zamanlı Akış' => '2 grup',
                            'Alarm' => 2,
                            'Pin Grubu' => 2,
                            'Arşiv' => '30 Gün',
                            'Canlı Trend' => '7/24',
                            'Analiz' => '+',
                            'Destek' => '7/24',
                        ],
                        'price' => config('formal.currency').' 424 +kdv / ay'
                    ],
                    'enterprise' => [
                        'title' => 'Kurumsal',
                        'details' => [
                            'Kullanıcı' => '+',
                            'Gerçek Zamanlı Akış' => '+',
                            'Alarm' => '+',
                            'Pin Grubu' => '+',
                            'Arşiv' => '+',
                            'Canlı Trend' => '7/24',
                            'Analiz' => '+',
                            'Destek' => '7/24',
                        ],
                        'price' => 'Teklif Alın'
                    ]
                ] as $key => $item)
                    <li class="flex-fill {{ $key }} right-align">
                        <h4 class="cyan-text">{{ $item['title'] }}</h4>
                        @foreach ($item['details'] as $k => $v)
                            <p class="grey-text text-darken-2">{{ $k }} <span class="cyan-text text-darken-4">{{ $v }}</span></p>
                        @endforeach
                        <span class="price cyan-text">{{ $item['price'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="demo-section cyan darken-2">
        <div class="container">
            <h2>Demo İsteyin</h2>
            <form id="demo-form" method="post" action="{{ route('demo.request') }}" class="json" data-callback="__demo_request">
                <div class="row">
                    <div class="col m12 l5">
                        <div class="input-field white-text">
                            <i class="material-icons prefix">account_circle</i>
                            <input id="icon_prefix" name="name" type="text" class="validate" />
                            <label for="icon_prefix">Firma / Kurum</label>
                        </div>
                    </div>
                    <div class="col m12 l5">
                        <div class="input-field white-text">
                            <i class="material-icons prefix">phone</i>
                            <input id="icon_telephone" name="phone" type="text" class="validate" />
                            <label for="icon_telephone">Telefon</label>
                        </div>
                    </div>
                    <div class="col m12 l2">
                        <div class="input-field white-text">
                            <div class="captcha" data-id="demo-captcha"></div>
                        </div>
                        <button type="submit" class="btn-flat waves-effect white-text">Gönder</button>
                    </div>
                </div>
            </form>
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
