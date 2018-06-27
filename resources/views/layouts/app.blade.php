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
    <link rel="stylesheet" href="{{ asset('css/animate.css?v='.config('app.version')) }}" />
    <link rel="stylesheet" href="{{ asset('css/master.css?v='.config('app.version')) }}" />

    <!-- external include header -->
    @stack('external.include.header')

    <!-- local styles -->
    @stack('local.styles')
</head>
<body>
    @if (@$header != 'hide')
    test
    @endif

    @yield('content')

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
                        <li><a class="grey-text" href="#!">Hakkımızda</a></li>
                        <li><a class="grey-text" href="#!">Blog</a></li>
                        <li><a class="grey-text" href="#!">İletişim</a></li>
                        <li><a class="grey-text" href="#!">Api</a></li>
                    </ul>
                </div>
                <div class="col l2 s12">
                    <ul>
                        <li><a class="grey-text" href="#!">Gizlilik Politikası</a></li>
                        <li><a class="grey-text" href="#!">Kullanım Koşulları</a></li>
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
    <script src="{{ asset('js/jquery.lazy.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/core.js?v='.config('app.version')) }}"></script>

    <!-- local scripts -->
    <script>
    @stack('local.scripts')
    </script>

    <!-- external include -->
    @stack('external.include.footer')
</body>
</html>
