@extends('layouts.app')

@push('local.scripts')
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

@push('external.include.footer')
    <script src="{{ asset('js/jquery.typewrite.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('content')
    <header id="main">
        <div class="parallax-container">
            <div class="parallax indigo lighten-5">
                <img src="{{ asset('img/bg.svg') }}" alt="background" />
            </div>

            <div class="container">
                <div class="row">
                    <div class="col">
                        <a href="#" id="logo">
                            <img src="{{ asset('img/olive_logo.svg') }}" alt="olive-logo" class="responsive-img" />
                        </a>

                        <div id="dword">
                            Olive, <span class="text"></span>
                        </div>
                        <p class="grey-text text-darken-2 lead">Internet artık daha net!</p>

                        <a href="{{ route('user.login') }}" class="waves-effect btn btn-large cyan darken-2">@auth{{ 'Olive\'e Gidin'}}@else{{ 'Giriş Yapın' }}@endauth</a>

                        <div class="down-area center-align">
                            <a href="#" class="waves-effect btn-large btn-floating pulse grey darken-4">
                                <i class="material-icons white-text">arrow_downward</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/sepbg.svg') }}" alt="sep" />
        </div>

        <div class="container">
            <div class="d-table mx-auto">
                <span class="d-flex white-text">
                    <h5 class="align-self-center">Türkiye'nin en enlamlı büyük verisi.</h5>
                </span>
            </div>
        </div>
    </div>

    <div class="parallax-container" id="more-step">
        <div class="parallax">
            <img src="{{ asset('img/bg-2.svg') }}" alt="grabg" />
        </div>

        <div class="container">
            <img id="vz-logo-top" src="{{ asset('img/olive_logo-grey.svg') }}" alt="olive-logo" />
            <div class="item-group" id="features">
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
    </div>

    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/sepbg.svg') }}" alt="sep" />
        </div>

        @push('local.scripts')
            function __counter(__, obj)
            {
                if (obj.status == 'ok')
                {
                    __.html(obj.data.count)

                    setTimeout(function() {
                        vzAjax($('[data-id=loader]'))
                    }, 30000)
                }
            }
        @endpush

        <div class="container">
            <div class="d-table mx-auto">
                <h5 class="d-flex white-text" id="data-count">
                    <small class="align-self-center mr-1">Hızla büyüyen veritabanı ve</small>
                    <span class="align-self-center load" data-id="loader" data-href="{{ route('home.data.counter') }}" data-method="post" data-callback="__counter">0</span>
                    <i class="align-self-center material-icons">add</i>
                    <small class="align-self-center ml-1">veri.</small>
                </h5>
            </div>
        </div>
    </div>

@section('hide')
    <div class="parallax-container">
        <div class="parallax">
            <img src="{{ asset('img/obg.svg') }}" alt="obg" />
        </div>

        <div class="container">
            @if (@$discountDay)
                <p class="center-align grey-text text-darken-2">Hemen şimdi üye olun ve bugüne özel <span class="chip">{{ $discountDay->discount_rate }}%</span> indirim kuponuna anında sahip olun.</p>
            @endif

            <div class="plans">
                @foreach (config('plans') as $key => $plan)
                    <div class="plan {{ $plan['class'] }}" data-title="{{ $plan['name'] }}">
                        <ul>
                            @foreach ($plan['properties'] as $item)
                                <li class="d-flex justify-content-between">
                                    <span>{{ $item['text'] }}</span>

                                    @if ($item['value'] === true)
                                        <i class="material-icons green-text">check</i>
                                    @elseif ($item['value'] === false)
                                        <i class="material-icons red-text">close</i>
                                    @elseif (is_integer($item['value']))
                                        <span>{{ $item['value'] }}</span>
                                    @else
                                        <i class="material-icons teal-text">streetview</i>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="price teal white-text">
                            <div class="d-flex justify-content-between">
                                <span class="new">
                                    <small>1 Ay</small>
                                    {{ config('formal.currency') }} {{ $plan['price'] }}
                                </span>
                                <span class="new">
                                    <small>12+ Ay</small>
                                    {{ config('formal.currency') }} {{ $plan['price'] - ($plan['price'] / 100 * config('formal.discount_with_year')) }}
                                </span>
                            </div>
                            <small>Vergiler dahil değildir.</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="center-align">
                <a href="{{ route('user.login') }}" class="waves-effect btn-flat btn-large">Hemen Başlayın!</a>
            </div>
        </div>
    </div>
@endsection
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
        $('.parallax').parallax()
    })

    $('.down-area').on('click', 'a.btn-large', function(e) {
        scrollTo({
            'target': '#more-step'
        })
    })
@endpush
