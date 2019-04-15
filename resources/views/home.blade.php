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

    .head-section {
        padding: 4rem 0;
        background-image: url({{ asset('img/obg.svg') }});
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
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

        console.log(e.pageY)

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
                    <a href="#" class="brand-logo">
                        <img alt="Olive" src="{{ asset('img/olive_logo.svg') }}" />
                    </a>
                    <ul id="nav-mobile" class="right hide-on-med-and-down">
                        <li>
                            <a href="#" class="grey-text text-darken-2 waves-effect">Veri Kaynakları</a>
                        </li>
                        <li>
                            <a href="#" class="grey-text text-darken-2 waves-effect">Hizmetler</a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard') }}" class="white-text text-darken-2 waves-effect teal">Giriş</a>
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
            <a data-tooltip="DEMO İSTEYİN" data-position="right" href="#" class="btn-floating btn-large white waves-effect pulse">
                <i class="material-icons grey-text text-darken-2">contacts</i>
            </a>
        </div>
    </section>
    <div class="container">
        <div class="item-group pt-2 pb-2" id="features">
            <div class="item grey-text text-darken-2">
                <i class="large material-icons analytics">poll</i>
                <h5>Analiz</h5>
                <ul>
                    <li>- Ürün veya markanızı rakiplerinizle kıyaslayın.</li>
                    <li>- İlgilendiğiniz konuları daha anlamlı bir şekilde inceleyin.</li>
                </ul>
            </div>
            <div class="item grey-text text-darken-2">
                <i class="large material-icons realtime">subject</i>
                <h5>Gerçek Zamanlı Veri</h5>
                <ul>
                    <li>- Herhangi bir konu trend olmadan gündemine hakim olun.</li>
                    <li>- Ürün veya markanızı anlık ve duygusal olarak takip edin.</li>
                    <li>- Anlık gündemi yakalayın ve daha sonra inceleyin.</li>
                </ul>
            </div>
            <div class="item grey-text text-darken-2">
                <i class="large material-icons rotate">toys</i>
                <h5>Araçlar</h5>
                <ul>
                    <li>- Orjinal kaynaktaki verinin dahasını inceleyin.</li>
                    <li>- Gerçek zamanlı veya geçmişe dönük API'ler alın.</li>
                </ul>
            </div>
            <div class="item grey-text text-darken-2">
                <i class="large material-icons cloud">cloud</i>
                <h5>Arşiv</h5>
                <ul>
                    <li>- Kriter belirleyin sizin için erişelim.</li>
                    <li>- Konu odaklı veri arşivi.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="owl-carousel main-slider z-depth-1 grey lighten-5">
        <div class="item grey-text text-darken-2">Online itibarınızı takip edin</div>
        <div class="item grey-text text-darken-2">Gündemi anlık trendlerle veya anlık akışlarla takip edin</div>
        <div class="item grey-text text-darken-2">Gerçek zamanlı alarmlar kurun</div>
        <div class="item grey-text text-darken-2">Rakiplerinizin ve sektörünüzün yeniliklerinden haberdar olun</div>
    </div>
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.typewrite.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
@endpush
