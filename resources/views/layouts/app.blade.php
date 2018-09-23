<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- charset -->
    <meta charset="utf-8" />

    <!-- viewport -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, shring-to-fit=no, user-scalable=no" />

    @isset($breadcrumb)
    @php $title = end($breadcrumb); @endphp
    @endisset
    <!-- title -->
    <title>@yield('title', @$title ? $title['text'] : config('app.name'))</title>

    <!-- font styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />

    <!-- favicon -->
    <link rel="icon" href="{{ asset('img/favicon.png?v='.config('app.version')) }}" />

    <!-- master styles -->
    <link rel="stylesheet" href="{{ asset('css/materialize.css?v='.config('app.version')) }}" />

    <!-- external include header -->
    @stack('external.include.header')

    <!-- local styles -->
    <style>
    @stack('local.styles')
    </style>
</head>
<body>
    @isset($sidenav_fixed_layout)
        @if (!auth()->user()->verified)
            <div id="modal-confirmation" class="modal bottom-sheet">
                <div class="modal-content">
                    <p>E-posta ({{ auth()->user()->email }}) adresinizi henüz doğrulamadınız.</p>
                    <p>Bu adres size ait değilse <a href="{{ route('settings.account') }}">Hesap Bilgileri</a> bölümünden size ait bir e-posta tanımlayın.</p>
                    <a href="#" class="waves-effect btn-flat json" data-href="{{ route('user.register.resend') }}" data-method="post" data-callback="__resend">Tekrar Gönder</a>
                    <a href="#" class="waves-effect btn modal-close">Tamam</a>
                </div>
            </div>

            @push('local.scripts')

            var instance = M.Modal.getInstance($('#modal-confirmation'));
                instance.open()

            function __resend(__, obj)
            {
                if (obj.status == 'ok')
                {
                    M.toast({
                        html: 'Yeni bir doğrulama e-postası gönderildi.',
                        classes: 'blue',
                        completeCallback: function() {
                            M.toast({ html: 'Lütfen e-posta kutunuzu kontrol edin.', classes: 'green' })
                            instance.close()
                        }
                    })
                }
                else if (obj.status == 'err')
                {
                    M.toast({
                        html: 'Mevcut hesap daha önceden doğrulanmış.',
                        classes: 'red',
                        completeCallback: function() {
                            instance.close()
                        }
                    })
                }
            }
            @endpush
        @endif

        <div class="navbar-fixed">
            <ul id="user-top-dropdown" class="dropdown-content">
                @if (auth()->user()->organisation)
                <li>
                    <a class="waves-effect" href="{{ route('settings.organisation') }}">
                        <i class="material-icons">group_work</i>
                        {{ auth()->user()->organisation->name }}
                    </a>
                </li>
                @endif
                <li>
                    <a class="waves-effect" href="{{ route('settings.account') }}">
                        <i class="material-icons">person</i>
                        Hesap Bilgileri
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ route('settings.support') }}">
                        <i class="material-icons">help</i>
                        Destek
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ route('user.logout') }}">
                        <i class="material-icons">exit_to_app</i>
                        Çıkış
                    </a>
                </li>
            </ul>
            <nav class="purple darken-2">
                <div class="sidenav-fixed-layout">
                    <div class="nav-wrapper">
                        <a href="{{ route('dashboard') }}" class="brand-logo center">
                            <img alt="{{ config('app.name') }}" src="{{ asset('img/olive-logo-white.svg') }}" />
                        </a>
                        <a href="#" data-target="slide-out" class="sidenav-trigger">
                            <i class="material-icons">menu</i>
                        </a>
                        <ul class="right">
                            @isset($dock)
                            <li>
                                <a href="#" data-class="body" data-class-toggle="dock-active" class="dock-menu">
                                    <i class="material-icons">blur_linear</i>
                                </a>
                            </li>
                            @endisset
                        </ul>
                        <ul class="right hide-on-med-and-down">
                            <li>
                                <a class="dropdown-trigger waves-effect" href="#" data-target="user-top-dropdown" data-align="right">
                                    {{ auth()->user()->name }} <i class="material-icons right">arrow_drop_down</i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <ul id="slide-out" class="sidenav sidenav-fixed collapsible">
            <li>
                <div class="user-view">
                    <small class="white-text right">Yapı {{ config('app.version') }}</small>
                    <div class="background" style="background-image: url('{{ asset('img/md/23.jpg') }}');"></div>

                    <img class="circle" src="{{ asset(auth()->user()->avatar()) }}" />
                    <span class="white-text name">{{ auth()->user()->name }}</span>
                    <span class="white-text email">{{ auth()->user()->email }}</span>
                </div>
            </li>

            @if (auth()->user()->root())
            <!-- sadece yönetici -->
            <li>
                <a href="#" class="subheader">Yönetici Menüsü</a>
            </li>
            <li>
                <a class="waves-effect" href="{{ route('admin.tickets') }}">
                    <i class="material-icons">mail</i>
                    Destek Talepleri
                    <span class="badge grey white-text" data-id="ticket-count">0</span>
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a class="waves-effect" href="{{ route('admin.page.list') }}">
                    <i class="material-icons">pages</i>
                    Sayfalar
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a class="waves-effect" href="{{ route('admin.organisation.list') }}">
                    <i class="material-icons">group_work</i>
                    Organizasyonlar
                </a>
            </li>
            <li>
                <a class="waves-effect" href="{{ route('admin.user.list') }}">
                    <i class="material-icons">people</i>
                    Kullanıcılar
                </a>
            </li>
            <li>
                <a class="waves-effect" href="{{ route('admin.discount.coupon.list') }}">
                    <i class="material-icons">card_giftcard</i>
                    İndirim Kuponları
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <div class="collapsible-header waves-effect">
                    <i class="material-icons">settings</i>
                    <span>Bot Yönetimi</span>
                    <i class="material-icons arrow">chevron_left</i>
                </div>
                <div class="collapsible-body">
                    <ul>
                        <li>
                            <a class="waves-effect" href="{{ route('crawlers.media.list') }}">
                                <i class="material-icons">widgets</i>
                                Medya Botları
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="{{ route('crawlers.sozluk.list') }}">
                                <i class="material-icons">widgets</i>
                                Sözlük Botları
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="{{ route('crawlers.shopping.list') }}">
                                <i class="material-icons">widgets</i>
                                Alışveriş Botları
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="#">
                                <i class="material-icons">widgets</i>
                                Twitter Ayarları
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="#">
                                <i class="material-icons">widgets</i>
                                Youtube Ayarları
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="divider"></li>
            <li>
                <div class="collapsible-header waves-effect">
                    <i class="material-icons">computer</i>
                    <span>Sistem İzleme</span>
                    <i class="material-icons arrow">chevron_left</i>
                </div>
                <div class="collapsible-body">
                    <ul>
                        <li>
                            <a class="waves-effect" href="{{ route('admin.monitoring.server') }}">
                                <i class="material-icons">desktop_mac</i>
                                Sunucu Bilgisi
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="{{ route('admin.monitoring.background') }}">
                                <i class="material-icons">hourglass_empty</i>
                                Arkaplan İşleri
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="{{ route('admin.monitoring.log') }}">
                                <i class="material-icons">code</i>
                                Log Ekranı
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect" href="{{ route('admin.monitoring.queue') }}">
                                <i class="material-icons">queue</i>
                                Kuyruk Ekranı
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="divider"></li>
            @endif

            <li>
                <a href="#" class="subheader">Kullanıcı Menüsü</a>
            </li>
            @if (auth()->user()->organisation)
            <li>
                <a class="waves-effect" href="{{ route('settings.organisation') }}">
                    <i class="material-icons">group_work</i>
                    {{ auth()->user()->organisation->name }}
                </a>
            </li>
            <li class="divider"></li>
            @endif
            <li>
                <a class="waves-effect" href="{{ route('settings.account') }}">
                    <i class="material-icons">person</i>
                    Hesap Bilgileri
                </a>
            </li>
            <li>
                <a class="waves-effect" href="{{ route('settings.support') }}">
                    <i class="material-icons">help</i>
                    Destek
                </a>
            </li>
            <li>
                <a class="waves-effect" href="{{ route('user.logout') }}">
                    <i class="material-icons">exit_to_app</i>
                    Çıkış
                </a>
            </li>
        </ul>

        @push('local.scripts')

        $('#slide-out').sidenav({
            draggable: true
        });

        @endpush

        @isset($breadcrumb)
        <nav class="grey darken-4" id="breadcrumb">
            <div class="sidenav-fixed-layout">
                <div class="container">
                    <a href="{{ route('dashboard') }}" class="breadcrumb">Olive</a>
                    @foreach ($breadcrumb as $row)
                        @if (@$row['link'])
                        <a href="{{ $row['link'] }}" class="breadcrumb">{{ $row['text'] }}</a>
                        @else
                        <span class="breadcrumb">{{ $row['text'] }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </nav>
        @endisset

        <main>
            <div class="sidenav-fixed-layout">
                <div class="container">
                    @isset($dock)
                    <div id="dock-content">
                        <div class="content">
                            @yield('content')
                        </div>
                        <div class="menu">
                            @yield('dock')
                        </div>
                    </div>
                    @else
                        @yield('content')
                    @endisset
                </div>
            </div>
        </main>

        <div class="dock-overlay" data-class="body" data-class-remove="dock-active"></div>
    @else
        @yield('content')
    @endisset

    @auth
        @if (auth()->user()->root())
            <div class="load" data-href="{{ route('dashboard.monitor') }}" data-callback="__monitor"></div>
            @push('local.scripts')
                function __monitor(__, obj)
                {
                    var monitorTimer;

                    if (obj.status == 'ok')
                    {
                        $('[data-id=ticket-count]').html(obj.data.ticket.count)
                                                  .addClass(obj.data.ticket.count > 0 ? 'red' : 'grey')
                                                  .removeClass(obj.data.ticket.count > 0 ? 'grey' : 'red')

                        window.clearTimeout(monitorTimer)

                        monitorTimer = setTimeout(function() {
                            vzAjax($('[data-callback=__monitor]'))
                        }, 10000)
                    }
                }
            @endpush
        @endif
    @endauth

    <div class="@isset($sidenav_fixed_layout){{ 'sidenav-fixed-layout' }}@endisset">
        <ul class="partners blue-grey darken-4">
            <li class="partner">
                <a href="#">
                    <img alt="client" src="{{ asset('img/clients-logo1.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="#">
                    <img alt="client" src="{{ asset('img/clients-logo2.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="#">
                    <img alt="client" src="{{ asset('img/clients-logo3.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="#">
                    <img alt="client" src="{{ asset('img/clients-logo4.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="#">
                    <img alt="client" src="{{ asset('img/clients-logo5.png') }}" />
                </a>
            </li>
        </ul>

        <footer class="page-footer">
            <div class="container">
                <div class="row">
                    <div class="col l6 s12">
                        <img id="vz-logo" src="{{ asset('img/veri.zone-logo.svg') }}" alt="veri.zone-logo" />
                        <p class="grey-text">veri.zone, açık kaynak internet verilerini toplar ve elde ettiği verilerden anlamlı analizler çıkaran araçlar geliştirir.</p>
                    </div>
                    <div class="col l2 offset-l2 s12">
                        <ul>
                            <li><a class="grey-text" href="{{ route('page.view', 'hakkimizda') }}">Hakkımızda</a></li>
                            <li><a class="grey-text" href="{{ config('services.medium.url') }}">Blog</a></li>
                            <li><a class="grey-text" href="{{ route('page.view', 'iletisim') }}">İletişim</a></li>
                            <li><a class="grey-text" href="#">Api</a></li>
                        </ul>
                    </div>
                    <div class="col l2 s12">
                        <ul>
                            <li><a class="grey-text" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a></li>
                            <li><a class="grey-text" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                <div class="container black-text">
                    © {{ date('Y') }} <a href="http://veri.zone">veri.zone</a> | Tüm hakları saklıdır.
                </div>
            </div>
        </footer>
    </div>

    <!-- scripts -->
    <script>
    var token = {!! '"'.csrf_token().'"' !!};
    var debug = {!! intval(config('app.debug')) !!};
    var recaptcha = {
        'site_key': '{{ config('services.google.recaptcha.site_key') }}'
    };
    var buttons = {!! json_encode(__('global.keywords')) !!};
    var errors = {!! json_encode(__('global.errors')) !!};
    var keywords = {!! json_encode(__('global.keywords')) !!};
    var date = {!! json_encode(__('global.date')) !!};
    </script>
    <!-- master scripts -->
    <script src="{{ asset('js/jquery.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/materialize.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/jquery.timeago.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/core.js?v='.config('app.version')) }}"></script>

    <!-- external include -->
    @stack('external.include.footer')

    <!-- local scripts -->
    <script>
    $('.modal').modal({})
    $.each($('.dropdown-trigger'), function() {
        var __ = $(this);

        __.dropdown({
            alignment: __.data('align') ? __.data('align') : 'left'
        })
    })
    $('[data-tooltip]').tooltip()
    $('.collapsible').collapsible()

    @stack('local.scripts')

    function __soft_in(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.reload()
        }
    }
    </script>
</body>
</html>
