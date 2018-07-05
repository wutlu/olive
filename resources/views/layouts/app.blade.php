<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- charset -->
    <meta charset="utf-8" />

    <!-- viewport -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, shring-to-fit=no, user-scalable=no" />

    <!-- title -->
    <title>@yield('title', config('app.name'))</title>

    <!-- font styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />

    <!-- favicon -->
    <link rel="icon" href="{{ asset('img/favicon.png?v='.config('app.version')) }}" />

    <!-- master styles -->
    <link rel="stylesheet" href="{{ asset('css/materialize.css?v='.config('app.version')) }}" />
    <link rel="stylesheet" href="{{ asset('css/master.css?v='.config('app.version')) }}" />

    <!-- external include header -->
    @stack('external.include.header')

    <!-- local styles -->
    @stack('local.styles')
</head>
<body>
    @isset($sidenav_fixed_layout)
        @if (!auth()->user()->verified)
            <div id="modal-confirmation" class="modal bottom-sheet">
                <div class="modal-content">
                    <p>E-posta ({{ auth()->user()->email }}) adresinizi henüz doğrulamadınız.</p>
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
                <li>
                    <a href="#" class="waves-effect">
                        <i class="material-icons">person</i>
                        Bilgilerimi Güncelle
                    </a>
                </li>
                <li>
                    <a href="#" class="waves-effect">
                        <i class="material-icons">settings</i>
                        Ayarlar
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.logout') }}" class="waves-effect">
                        <i class="material-icons">exit_to_app</i>
                        Çıkış Yap
                    </a>
                </li>
            </ul>
            <nav class="blue-grey darken-3">
                <div class="sidenav-fixed-layout">
                    <div class="nav-wrapper">
                        <a href="{{ route('dashboard') }}" class="brand-logo center">
                            <img alt="{{ config('app.name') }}" src="{{ asset('img/olive-logo-white.svg') }}" />
                        </a>
                        <a href="#" data-target="slide-out" class="sidenav-trigger">
                            <i class="material-icons">menu</i>
                        </a>
                        <ul class="right hide-on-med-and-down">
                            <li>
                                <a class="dropdown-trigger waves-effect" href="#" data-target="user-top-dropdown">
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
                    <div class="background" style="background-image: url('{{ asset('img/md/23.jpg') }}');"></div>

                    <img class="circle" src="{{ asset(auth()->user()->avatar()) }}" />
                    <span class="white-text name">{{ auth()->user()->name }}</span>
                    <span class="white-text email">{{ auth()->user()->email }}</span>
                </div>
            </li>
            <li>
                <a class="subheader">Yönetici Menüsü</a>
            </li>
            <li>
                <div class="collapsible-header waves-effect">
                    <i class="material-icons">security</i>
                    <span>Admin</span>
                    <i class="material-icons arrow">chevron_left</i>
                </div>
                <div class="collapsible-body">
                    <ul>
                        <li>
                            <a class="waves-effect" href="#">
                                <i class="material-icons">people</i>
                                Kullanıcı Yönetimi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        @push('local.scripts')

        $('.sidenav').sidenav({
            draggable: true
        });

        @endpush

        @isset($breadcrumb)
        <nav class="grey darken-4">
            <div class="sidenav-fixed-layout">
                <div class="container">
                    <div class="nav-wrapper">
                        <div class="col s12">
                            <a href="{{ route('dashboard') }}" class="breadcrumb">Panel</a>
                            @foreach ($breadcrumb as $row)
                                @if (@$row['link'])
                                <a href="{{ $row['link'] }}" class="breadcrumb">{{ $row['text'] }}</a>
                                @else
                                <span class="breadcrumb">{{ $row['text'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        @endisset

        <main class="blue-grey lighten-5">
            <div class="sidenav-fixed-layout">
                <div class="container">
                    @yield('content')
                </div>
            </div>
        </main>
    @else
        @yield('content')
    @endisset

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
                            <li><a class="grey-text" href="#">Hakkımızda</a></li>
                            <li><a class="grey-text" href="#">Blog</a></li>
                            <li><a class="grey-text" href="#">İletişim</a></li>
                            <li><a class="grey-text" href="#">Yardım</a></li>
                            <li><a class="grey-text" href="#">Api</a></li>
                        </ul>
                    </div>
                    <div class="col l2 s12">
                        <ul>
                            <li><a class="grey-text" href="#">Gizlilik Politikası</a></li>
                            <li><a class="grey-text" href="#">Kullanım Koşulları</a></li>
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
    <script src="{{ asset('js/materialize.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/jquery.timeago.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/jquery.lazy.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/core.js?v='.config('app.version')) }}"></script>

    <!-- external include -->
    @stack('external.include.footer')

    <!-- local scripts -->
    <script>
    $('.modal').modal();
    $('.dropdown-trigger').dropdown();
    $('[data-tooltip]').tooltip();
    $('.collapsible').collapsible();

    @stack('local.scripts')
    </script>
</body>
</html>
