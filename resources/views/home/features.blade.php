@extends('layouts.app', [
    'title' => [
        'text' => 'Yenilikler'
    ],
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
        padding: 10vh 0;
        position: relative;
        z-index: 1;
    }

    .section h1,
    .section h2,
    .section h3 {
        margin: 0;
        padding: 0;
        font-weight: bold;
        color: #000;
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
        color: #000;
        font-size: 20px;
        font-weight: bold;
    }

    .section .text-container {
        max-width: 600px;
    }
@endpush

@section('content')
    <nav id="main-nav">
        <div class="container">
            <div class="nav-wrapper">
                <a href="{{ route('home') }}" class="brand-logo left">
                    <img alt="{{ config('app.name') }}" src="{{ asset('img/veri.zone_logo.svg') }}" />
                </a>

                <ul class="right">
                    <li class="hide-on-med-and-down">
                        <a href="tel:8503021630" class="white fonted-menu">+90 850 302 16 30</a>
                    </li>
                    <li class="btn-li d-flex">
                        <a href="{{ route('user.login') }}" class="btn blue darken-4 white-text waves-effect align-self-center">Ücretsiz Deneyin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- section 1 -->

    <section class="section section-header blue darken-4">
        <div class="container">
            <div class="text-container">
                <h1 class="mb-1">Değişim Başlıyor</h1>
                <p class="lead">Veri madenciliğinin geleceği için VERİ.ZONE'u nasıl tasarladığımızı görün!</p>
            </div>
        </div>
    </section>

    @push('local.styles')
        #main-nav {
            position: relative;
            z-index: 1;
        }

        .section-header {
            background-position: center;
            background-size: cover;
        }
        .section-header > .container {
            background-repeat: no-repeat;
            background-position: right 32px center;
            background-image: url('{{ asset('img/hero.png') }}');
            background-size: 300px;
        }
        @media (max-width: 1024px) {
            .section-header > .container {
                background: none;
            }
        }
        .section-header h1,
        .section-header p.lead {
            color: #fff;
        }
    @endpush

    <!-- section 2 -->

    <section class="section section-team">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-center">
                <img alt="Mutlu" src="{{ asset('img/team-mutlu.jpg') }}" class="team align-self-center" />
                <div class="item align-self-center">
                    <h2>Alper Mutlu Toksöz</h2>
                    <p class="author mb-1">Kurucu, Veri Zone Teknoloji</p>
                    <p class="lead">Hızla gelişen büyük veri dünyasında sabit özelliklere sahip bir teknoloji düşünülemez. En iyiye ulaşmak için veri madenciliği anlayışını VERİ.ZONE ile yeniden tasarladık. Alanımız için en iyi çözümleri üretiyor, üretmeye devam ediyoruz.</p>
                </div>
            </div>
        </div>
    </section>

    @push('local.styles')
        .section-team .container {
            padding: 4rem 0;
        }
        .section-team img.team {
            width: 200px;
            border-radius: 50%;
            border: 24px solid #fff;
            margin: 32px;
        }
        .section-team .item {
            max-width: 600px;
        }

        .section-team p.author {
            margin: 0;
            padding: 0;
            color: #546e7a;
            font-size: 16px;
        }
    @endpush

    <div class="d-table mx-auto mt-2">
        <a href="#" class="btn-floating btn-large blue-grey darken-4 pulse" data-scroll-to=".section-mockup">
            <i class="material-icons">arrow_downward</i>
        </a>
    </div>

    <!-- section 3 -->

    <div class="section section-mockup">
        <div class="container">
            <div class="row d-flex flex-wrap align-items-stretch">
                @foreach ($array as $key => $item)
                    <div class="col l4 m12 olive-tool">
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
        </div>
    </div>
@endsection
