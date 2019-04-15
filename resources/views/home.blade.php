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
    }

    .head-section {
        background-image: url({{ asset('img/obg.svg') }});
        padding: 4rem 0;
    }
    .head-section h1 {
        font-size: 48px;
    }

    .demo-section {
        background-image: url({{ asset('img/photo/contact.jpg') }});
        background-attachment: fixed;
    }

    .section-overlay {
        background-color: rgba(40, 200, 200, .6);
        padding: 4rem 0;
        top: -4rem;
        bottom: -4rem;
    }

    .section-overlay h2 {
        font-size: 32px;
        color: #fff;
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

    $(window).on( 'scroll', function(e) {
        var nav = $('nav.scrolled');

        if (e.pageY < 48)
        {
            nav.removeClass('active')
        }
        else
        {
            nav.addClass('active')
        }
    })

    $('#dword').children('.text').typewrite({
        actions: [
            { delay: 500 },
            { type: 'daha temiz!' },
            { delay: 1000 },
            { select: { from: 5, to: 11 } },
            { delay: 1000 },
            { remove: { num: 6, type: 'whole' } },

            { type: 'net!' },
            { delay: 1000 },
            { select: { from: 5, to: 9 } },
            { delay: 1000 },
            { remove: { num: 4, type: 'whole' } },

            { type: 'anlamlı!' },
            { delay: 1000 },
            { select: { from: 12, to: 13 } },
            { delay: 1000 },
            { remove: { num: 1, type: 'whole' } },

            { type: ' bir internet deneyimi sunar...' },
        ]
    })
@endpush

@section('content')
    <div class="navbar-fixed">
        <nav class="white scrolled">
            <div class="container">
                <div class="nav-wrapper">
                    <a href="#" class="brand-logo left">
                        <img alt="Olive" src="{{ asset('img/olive_logo.svg') }}" />
                    </a>
                    <ul class="right">
                        <li>
                            <a href="{{ route('dashboard') }}" class="grey-text text-darken-2 waves-effect">GİRİŞ</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <section class="head-section d-flex">
        <div class="container align-self-center">
            <h1>Medya & Sosyal Medya Takip Platformu</h1>
            <p id="dword" class="mb-2">Olive, <span class="text"></span></p>
            <a data-tooltip="DEMO İSTEYİN" data-scroll-to=".demo-section" data-scroll-to-tolerance="64px" data-position="right" href="#" class="btn-floating btn-large white waves-effect pulse">
                <i class="material-icons grey-text text-darken-2">contacts</i>
            </a>
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

    <section class="demo-section">
        <div class="section-overlay">
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
                            <div class="input-field">
                                <div class="captcha" data-id="demo-captcha"></div>
                            </div>
                            <button type="submit" class="btn-flat waves-effect white-text">Gönder</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="owl-carousel main-slider z-depth-1 grey lighten-5">
        <div class="item grey-text text-darken-2">Online itibarınızı takip edin</div>
        <div class="item grey-text text-darken-2">Gündemi anlık trendlerle veya anlık akışlarla takip edin</div>
        <div class="item grey-text text-darken-2">Gerçek zamanlı alarmlar kurun</div>
        <div class="item grey-text text-darken-2">Arama sonuçlarınızı görselleştirin</div>
        <div class="item grey-text text-darken-2">Rakiplerinizin ve sektörünüzün yeniliklerinden haberdar olun</div>
    </div>
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.typewrite.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src='//www.google.com/recaptcha/api.js'></script>
@endpush
