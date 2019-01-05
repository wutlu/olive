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

    <!-- master styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <link rel="stylesheet" href="{{ asset('css/materialize.min.css?v='.config('app.version')) }}" />
    <link rel="stylesheet" href="{{ asset('css/theme.css?v='.config('app.version')) }}" />

    <!-- manifest -->
    <link rel="manifest" href="{{ asset(route('olive.manifest').'?v='.config('app.version')) }}" />

    <!-- favicons -->
    <link rel="icon" href="{{ asset('favicon.ico?v='.config('app.version')) }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicons/favicon-16x16.png?v='.config('app.version')) }}" sizes="16x16" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicons/favicon-32x32.png?v='.config('app.version')) }}" sizes="32x32" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicons/apple-touch-icon.png?v='.config('app.version')) }}" />
    <link rel="mask-icon" href="{{ asset('img/favicons/safari-pinned-tab.svg?v='.config('app.version')) }}" color="{{ config('view.color') }}" />

    <!-- theme color -->
    <meta name="theme-color" content="{{ config('view.color') }}" />

    <!-- external include header -->
    @stack('external.include.header')

    <!-- local styles -->
    <style>
    @stack('local.styles')
    </style>
</head>
<body>
    <div class="window-size z-depth-1">
        <p>Ekran çözünürlüğünüz çok küçük. Tam manasıyla bir Olive için çözünürlüğünüzü yükseltmeniz gerekiyor.</p>
    </div> 
    @if (@$sidenav_fixed_layout)
        @auth
            @if (!auth()->user()->verified)
                <div id="modal-confirmation" class="modal bottom-sheet">
                    <div class="modal-content">
                        <div class="card mb-0">
                            <div class="card-content">
                                <p>E-posta ({{ auth()->user()->email }}) adresinizi henüz doğrulamadınız.</p>
                                <p>Bu adres size ait değilse <a href="{{ route('settings.account') }}">Hesap Bilgileri</a> bölümünden size ait bir e-posta tanımlayın.</p>
                            </div>
                            <div class="card-action">
                                <a href="#" class="waves-effect btn-flat json" data-href="{{ route('user.register.resend') }}" data-method="post" data-callback="__resend">Tekrar Gönder</a>
                                <button href="#" class="waves-effect btn-flat modal-close">Tamam</button>
                            </div>
                        </div>
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
                                M.toast({ html: 'Lütfen e-posta kutunuzu kontrol edin.', classes: 'green darken-2' })
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
        @endauth

        <div class="navbar-fixed">
            @auth
                <ul id="user-top-dropdown" class="dropdown-content">
                    @if (auth()->user()->organisation)
                    <li>
                        <a class="waves-effect" href="{{ route('settings.organisation') }}">
                            <i class="material-icons">group_work</i> {{ auth()->user()->organisation->name }}
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="waves-effect" href="{{ route('settings.account') }}">
                            <i class="material-icons">person</i> Hesap Bilgileri
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect" href="{{ route('settings.support') }}">
                            <i class="material-icons">help</i> Destek
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect" href="{{ route('user.logout') }}">
                            <i class="material-icons">exit_to_app</i> Çıkış
                        </a>
                    </li>
                </ul>
            @endauth
            <nav class="cyan darken-2">
                <div class="{{ auth()->check() ? 'sidenav-fixed-layout' : 'container' }}">
                    <div class="nav-wrapper">
                        <a href="{{ route('dashboard') }}" class="brand-logo center">
                            <img alt="{{ config('app.name') }}" src="{{ asset('img/olive-logo-white.svg') }}" />
                        </a>
                        @auth
                        <a href="#" data-target="slide-out" class="sidenav-trigger">
                            <i class="material-icons">menu</i>
                        </a>
                        @endauth
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
                            @auth
                            <li>
                                <a class="dropdown-trigger waves-effect" href="#" data-target="user-top-dropdown" data-align="right">
                                    {{ auth()->user()->name }} <i class="material-icons right">arrow_drop_down</i>
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        @auth
            <ul id="slide-out" class="sidenav sidenav-fixed collapsible">
                <li>
                    <div class="user-view">
                        <small class="white-text right">{{ config('app.version') }}</small>
                        <div class="background" style="background-image: url('{{ asset('img/card-2.jpg') }}');"></div>
                        <img alt="{{ auth()->user()->name }}" class="circle" src="{{ asset(auth()->user()->avatar()) }}" />
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
                <li>
                    <a class="waves-effect" href="{{ route('admin.carousels') }}">
                        <i class="material-icons">view_carousel</i>
                        Carousel Yönetimi
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ route('admin.proxies') }}">
                        <i class="material-icons">vpn_key</i>
                        Vekil Sunucu Yönetimi
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <div class="collapsible-header waves-effect">
                        <i class="material-icons">settings</i>
                        <span>Bot Yönetimi</span>
                        <i class="material-icons arrow">keyboard_arrow_down</i>
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
                            <li class="divider"></li>
                            <li>
                                <a class="waves-effect" href="{{ route('admin.twitter.settings') }}">
                                    <i class="material-icons">widgets</i>
                                    Twitter Ayarları
                                </a>
                            </li>
                            <li>
                                <a class="waves-effect" href="{{ route('admin.youtube.settings') }}">
                                    <i class="material-icons">widgets</i>
                                    YouTube Ayarları
                                </a>
                            </li>
                            <li>
                                <a class="waves-effect" href="{{ route('admin.google.settings') }}">
                                    <i class="material-icons">widgets</i>
                                    Google Ayarları
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
                        <i class="material-icons arrow">keyboard_arrow_down</i>
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
                    <a class="waves-effect" href="{{ route('forum.index') }}">
                        <i class="material-icons">forum</i>
                        Forum
                    </a>
                </li>
                <li class="divider"></li>
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
        @endauth

        @push('local.scripts')
            $('#slide-out').sidenav({
                draggable: true
            });
        @endpush

        @isset($breadcrumb)
            <nav class="cyan darken-4" id="breadcrumb">
                <div class="{{ auth()->check() ? 'sidenav-fixed-layout' : '' }}">
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
            <div class="{{ auth()->check() ? 'sidenav-fixed-layout' : '' }}">
                @if (trim($__env->yieldContent('wildcard')))
                    <div class="wildcard">
                        @yield('wildcard')
                    </div>
                @endif

                <div class="container">
                    @isset($dock)
                    <aside id="dock-content">
                        <div class="content">
                            @yield('content')
                        </div>
                        <div class="menu">
                            @yield('dock')
                        </div>
                    </aside>
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
        <div class="fixed-action-btn">
            <a data-trigger="module-search" id="search-trigger" class="btn-floating btn-large red darken-2 waves-effect" data-tooltip="Modül Ara (CTRL + G)" data-position="left">
                <i class="material-icons">search</i>
            </a>
        </div>

        <div class="search-wrapper" id="module-search">
            <div class="search-content">
                <div class="right-align">
                    <a href="#" class="btn-floating red waves-effect" data-trigger="module-search-close">
                        <i class="material-icons">close</i>
                    </a>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">search</i>
                    <input
                        name="search_input"
                        id="search_input"
                        type="text"
                        class="validate json"
                        data-href="{{ route('module.search') }}"
                        data-method="post"
                        data-delay="1"
                        data-callback="__module_search" />
                    <label for="search_input">Arayın</label>
                </div>
                <div id="ms-results"></div>
            </div>
        </div>

        @push('local.scripts')
            var ms_results = $('#ms-results');

            $(document).keydown(function(e) {
                if (e.ctrlKey && e.keyCode == 71)
                {
                    $('[data-trigger=module-search]').click()

                    e.preventDefault()
                }
            })

            $(document).on('click', '[data-trigger=module-search]', function() {
                var search_wrapper = $('#module-search');
                var input = search_wrapper.find('input[name=search_input]');

                $('body').addClass('module-search-active')

                    vzAjax(input)

                    input.focus()

                    setTimeout(function() {
                        input.focus()
                    }, 200)
            }).on('click', '[data-trigger=module-search-close]', function() {
                $('body').removeClass('module-search-active')
            }).on('click', '.search-wrapper', function() {
                var search_wrapper = $('#module-search');
                    search_wrapper.find('input[name=search_input]').focus();
            })

            $('#module-search').keydown(function(e) {
                if (e.which == 27)
                {
                    $('body').removeClass('module-search-active')
                }
                else if (e.keyCode == 37)
                {
                    console.log('Left')
                }
                else if (e.keyCode == 38)
                {
                    console.log('Up')
                }
                else if (e.keyCode == 39)
                {
                    console.log('Right')
                }
                else if (e.keyCode == 40)
                {
                    console.log('Down')
                }
            })

            function __module_search(__, obj)
            {
                if (obj.status == 'ok')
                {
                    if (obj.data.length)
                    {
                        ms_results.html('')
                    }

                    if (obj.data.length)
                    {
                        $.each (obj.data, function(key, o) {
                            var item = $('<a />', {
                                'class': 'ms-item waves-effect json',
                                'href': '#',
                                'data-href': '{{ route('module.go') }}',
                                'data-method': 'post',
                                'data-callback': '__go',
                                'html': [
                                    $('<i />', { 'class': 'material-icons medium', 'html': o.icon }),
                                    $('<span />', { 'class': 'd-block', 'html': o.name })
                                ],
                                'data-module_id': o.module_id,
                                'data-include': 'search_input'
                            });

                            if (o.route)
                            {
                                item.attr('data-route', o.route)
                            }

                            item.addClass(o.root ? 'yellow-text' : '')
                            item.appendTo(ms_results)
                        })
                    }
                }
            }
        @endpush

        <div class="load" data-href="{{ route('dashboard.monitor') }}" data-method="post" data-callback="__monitor"></div>
        <div class="push-notifications">
            <div class="notification hide _model">
                <div class="card z-depth-5">
                    <div class="card-content">
                        <p data-name="text"></p>
                    </div>
                    <div class="card-action right-align">
                        <a href="#" data-name="ok" class="btn-flat waves-effect">Tamam</a>
                        <a href="#" data-name="action" class="hide"></a>
                    </div>
                </div>
            </div>
        </div> 
        @push('local.scripts')
            function __monitor(__, obj)
            {
                var monitorTimer;

                if (obj.status == 'ok')
                {
                    @if (auth()->user()->root())
                        $('[data-id=ticket-count]').html(obj.data.ticket.count).addClass(obj.data.ticket.count > 0 ? 'red' : 'grey').removeClass(obj.data.ticket.count > 0 ? 'grey' : 'red')
                    @endif

                    if (obj.data.push_notifications.length)
                    {
                        var pn = $('.push-notifications');
                            pn_model = pn.children('.notification._model')

                        $.each(obj.data.push_notifications, function(key, o) {
                            var item = pn_model.clone();
                                item.removeClass('_model hide')
                                item.find('[data-name=text]').html(o.title)

                            if (o.button)
                            {
                                var button = item.find('[data-name=action]');

                                if (o.button.text)
                                {
                                    button.addClass(o.button.class)
                                          .html(o.button.text)
                                          .attr('href', o.button.action)
                                }

                                button.removeClass('hide')
                            }

                            item.appendTo(pn)
                        })

                        $.playSound('{{ asset('push-notification.mp3') }}')
                    }

                    window.clearTimeout(monitorTimer)

                    monitorTimer = setTimeout(function() {
                        vzAjax($('[data-callback=__monitor]'))
                    }, 10000)
                }
            }

            $('.push-notifications').on('click', '[data-name=ok]', function() {
                var __ = $(this);
                    __.closest('.notification').remove()

            })
        @endpush
    @endauth

    <div class="@auth{{ @$sidenav_fixed_layout ? 'sidenav-fixed-layout' : '' }}@endauth">
        <ul class="partners grey lighten-4">
            <li class="partner">
                <a href="https://laravel.com/" target="_blank">
                    <img alt="client" src="{{ asset('img/clients-logo1.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="https://materializecss.com/" target="_blank">
                    <img alt="client" src="{{ asset('img/clients-logo2.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="https://www.elastic.co/" target="_blank">
                    <img alt="client" src="{{ asset('img/clients-logo3.png') }}" />
                </a>
            </li>
            <li class="partner">
                <a href="https://www.postgresql.org/" target="_blank">
                    <img alt="client" src="{{ asset('img/clients-logo4.png') }}" />
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
                            <li><a class="grey-text" href="{{ route('forum.index') }}">Forum</a></li>
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
    var verifications = {!! json_encode(__('global.verifications')) !!};
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
