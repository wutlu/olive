<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- charset -->
    <meta charset="utf-8" />

    <!-- viewport -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="robots" content="noindex" />

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, shring-to-fit=no, user-scalable=no" />

    @isset($breadcrumb)
        @php $title = end($breadcrumb); @endphp
    @endisset

    <!-- title -->
    <title>@yield('title', isset($title) ? $title['text'] : config('app.name'))</title>

    <!-- master styles -->
    <link rel="stylesheet" href="//fonts.googleapis.com/icon?family=Material+Icons" />

    <link rel="stylesheet" href="{{ asset('css/materialize.min.css?v='.config('system.version')) }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css?v='.config('system.version')) }}" />

    @isset($help)
        <link rel="stylesheet" href="{{ asset('css/driver.min.css?v='.config('system.version')) }}" />
    @endisset

    <!-- manifest -->
    <link rel="manifest" href="{{ asset(route('olive.manifest').'?v='.config('system.version')) }}" />

    <!-- favicons -->
    <link rel="icon" href="{{ asset('favicon.ico?v='.config('system.version')) }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicons/favicon-16x16.png?v='.config('system.version')) }}" sizes="16x16" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicons/favicon-32x32.png?v='.config('system.version')) }}" sizes="32x32" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicons/apple-touch-icon.png?v='.config('system.version')) }}" />
    <link rel="mask-icon" href="{{ asset('img/favicons/safari-pinned-tab.svg?v='.config('system.version')) }}" color="{{ config('view.color') }}" />

    <!-- theme color -->
    <meta name="theme-color" content="{{ config('view.theme_color') }}" />

    <!-- external include header -->
    @stack('external.include.header')

    <!-- local styles -->
    <style>
    @stack('local.styles')
    </style>
</head>
<body>
    @auth
        @if (isset($email) != 'hide')
            @push('local.scripts')
                @if (auth()->user()->email == 'anonymous@veri.zone')
                    modal({
                        'title': 'E-posta',
                        'id': 'modal-registration',
                        'body': $('<span />', {
                            'class': 'red-text',
                            'html': 'Hesabınızı aktif edebilmek için bir e-posta adresi tanımlamanız gerekiyor.'
                        }),
                        'size': 'modal-small',
                        'options': {
                            'dismissible': false
                        },
                        'footer': [
                            $('<a />', {
                                'href': '{{ route('settings.account') }}',
                                'class': 'waves-effect btn-flat grey-text text-darken-4',
                                'html': buttons.ok
                            })
                        ]
                    })
                @endif
            @endpush
        @endif

        @if (isset($term) != 'hide')
            @if (auth()->user()->term_version != config('system.term_version'))
                @push('local.scripts')
                    modal({
                        'title': 'Koşullar',
                        'id': 'modal-term',
                        'body': '<a class="grey-text" target="_blank" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a> ve <a class="grey-text" target="_blank" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a> güncellendi. Tekrar gözden geçirip kabul etmeniz gerekiyor.',
                        'size': 'modal-small',
                        'options': {
                            'dismissible': false
                        },
                        'footer': [
                            $('<a />', {
                                'href': '#',
                                'class': 'modal-close waves-effect btn-flat grey-text text-darken-4 json',
                                'data-method': 'post',
                                'data-href': '{{ route('term.version') }}',
                                'html': buttons.iagree
                            })
                        ]
                    })
                @endpush
            @endif
        @endif

        @isset($pin_group)
            @include('pin.group.dock')
        @endisset
    @endauth

    @if (isset($sidenav_fixed_layout) || isset($sidenav_layout))
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
                    <li>
                        <a href="{{ route('user.profile', auth()->user()->id) }}" class="d-flex">
                            <img
                                alt="{{ auth()->user()->name }}"
                                class="align-self-center mr-1"
                                src="{{ asset(auth()->user()->avatar()) }}"
                                style="width: 32px; height: 32px;" />
                            <span class="align-self-center">
                                <span class="d-block">{{ auth()->user()->name }}</span>
                                <span class="grey-text">{{ auth()->user()->email }}</span>
                            </span>
                        </a>
                    </li>
                    <li class="divider"></li>
                    @if (auth()->user()->organisation)
                    <li>
                        <a class="waves-effect" href="{{ route('settings.organisation') }}">
                            <i class="material-icons">group_work</i> Organizasyon
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

            <nav id="main-nav">
                <div class="{{ isset($sidenav_layout) ? '' : (auth()->check() ? 'sidenav-fixed-layout' : 'container') }}">
                    <div class="nav-wrapper">
                        <a href="{{ route('dashboard') }}" class="brand-logo center">
                            <img alt="{{ config('app.name') }}" src="{{ asset('img/olive_logo-white.svg') }}" />
                        </a>

                        @auth
                            <a href="#" data-target="slide-out" class="sidenav-trigger {{ isset($sidenav_layout) ? 'show-on-medium-and-up' : '' }}">
                                <i class="material-icons">menu</i>
                            </a>

                            @if (trim($__env->yieldContent('panel')))
                            <ul class="hide-on-med-and-up">
                                <li>
                                    <a href="#" data-class="#panel" data-class-toggle="active">
                                        <i class="material-icons">@yield('panel-icon', 'view_compact')</i>
                                    </a>
                                </li>
                            </ul>
                            @endif
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

                        @auth
                            <ul class="right hide-on-med-and-down">
                                <li>
                                    <a class="dropdown-trigger waves-effect" href="#" data-target="user-top-dropdown" data-align="right">
                                        {{ auth()->user()->name }} <i class="material-icons right">arrow_drop_down</i>
                                    </a>
                                </li>
                            </ul>

                            <ul class="right">
                                @isset($delete)
                                    @if (auth()->user()->admin())
                                        @push('local.scripts')
                                            $(document).on('click', '[data-trigger=delete-forever]', function() {
                                                return modal({
                                                    'id': 'confirmation',
                                                    'body': $('<span />', {
                                                        'html': 'Bu içeriğin kaynağına tekrar gidilmediği sürece veritabanlarımızdan kalıcı olarak silinecektir. Bu işlemi onalıyor musunuz?'
                                                    }),
                                                    'title': '[Admin] İçerik Sil',
                                                    'size': 'modal-small',
                                                    'options': {},
                                                    'footer': [
                                                        $('<a />', {
                                                            'href': '#',
                                                            'class': 'modal-close waves-effect btn-flat green-text',
                                                            'html': buttons.cancel
                                                        }),
                                                        $('<span />', {
                                                            'html': ' '
                                                        }),
                                                        $('<a />', {
                                                            'href': '#',
                                                            'class': 'waves-effect btn-flat red-text json',
                                                            'data-method': 'delete',
                                                            'data-href': '{{ route('admin.content.delete', [ 'es_index' => $delete['index'], 'es_type' => $delete['type'], 'es_id' => $delete['id'] ]) }}',
                                                            'data-callback': '__forever_deleted',
                                                            'html': buttons.ok
                                                        })
                                                    ]
                                                })
                                            })

                                            function __forever_deleted(__, obj)
                                            {
                                                if (obj.status == 'ok')
                                                {
                                                    M.toast({ html: 'İçerik Silindi', classes: 'teal darken-2' })

                                                    $('#modal-confirmation').modal('close')
                                                }
                                            }
                                        @endpush
                                        <li>
                                            <a data-trigger="delete-forever" href="#">
                                                <i class="material-icons">delete_forever</i>
                                            </a>
                                        </li>
                                    @endif
                                @endisset
                                @isset($pin_group)
                                    <li>
                                        <a href="#" data-class="#pin-groups-dock" data-class-toggle="active" data-dock="close">
                                            <i class="material-icons">fiber_pin</i>
                                        </a>
                                    </li>
                                @endisset
                                @isset($help)
                                    <li class="hide-on-small-only" id="help-button">
                                        <a href="#" onclick="{{ $help }}">
                                            <i class="material-icons">help</i>
                                        </a>
                                    </li>
                                @endisset
                            </ul>
                        @endauth
                    </div>
                </div>
            </nav>
        </div>

        @auth
            <ul id="slide-out" class="sidenav {{ isset($sidenav_layout) ? '' : 'sidenav-fixed' }} collapsible">
                <li>
                    <div class="user-view">
                        <small class="right {{ config('app.env') == 'local' ? 'red-text' : '' }}">{{ config('system.version') }}</small>
                        <img alt="{{ auth()->user()->name }}" class="circle" src="{{ asset(auth()->user()->avatar()) }}" />
                        <span class="name">{{ auth()->user()->name }}</span>
                        <span class="email white-text">{{ auth()->user()->email }}</span>
                    </div>
                </li>

                @if (auth()->user()->partner)
                    <!-- sadece partner -->
                    <li>
                        <a href="{{ route('partner.user.list') }}" class="d-flex waves-effect">
                            <img class="align-self-center mr-1" alt="{{ auth()->user()->partner }}" src="{{ asset('img/partner-'.auth()->user()->partner) }}.png" style="width: 32px; height: 32px;" />
                            <span class="align-self-center">{{ strtoupper(auth()->user()->partner) }} PARTNER</span>
                        </a>
                    </li>
                    <li class="divider"></li> 
                @endif

                @if (auth()->user()->root())
                    <!-- sadece yönetici -->
                    <li>
                        <a href="#" class="subheader">Sistem Sorumlusu</a>
                    </li>
                    <li>
                        <div class="collapsible-header waves-effect">
                            <i class="material-icons">settings</i>
                            <span>Bot Yönetimi</span>
                            <i class="material-icons arrow">keyboard_arrow_down</i>
                        </div>
                        <div class="collapsible-body">
                            <ul>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('crawlers.media.list') }}">
                                        <i class="material-icons">widgets</i>
                                        Medya Botları
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('crawlers.sozluk.list') }}">
                                        <i class="material-icons">widgets</i>
                                        Sözlük Botları
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('crawlers.shopping.list') }}">
                                        <i class="material-icons">widgets</i>
                                        E-ticaret Botları
                                    </a>
                                </li>
                                <li class="tiny" style="opacity: 0.4;">
                                    <a class="waves-effect" href="#">
                                        <i class="material-icons">widgets</i>
                                        Forum Botları
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('crawlers.blog.list') }}">
                                        <i class="material-icons">widgets</i>
                                        Blog Botları
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.twitter.settings') }}">
                                        <i class="material-icons">widgets</i>
                                        Twitter Ayarları
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.youtube.settings') }}">
                                        <i class="material-icons">widgets</i>
                                        YouTube Ayarları
                                    </a>
                                </li>
                                <li class="tiny" style="opacity: 0.4;">
                                    <a class="waves-effect" href="#">
                                        <i class="material-icons">widgets</i>
                                        Facebook Ayarları
                                    </a>
                                </li>
                                <li class="tiny" style="opacity: 0.4;">
                                    <a class="waves-effect" href="#">
                                        <i class="material-icons">widgets</i>
                                        Instagram Ayarları
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="subheader">Genel Ayarlar</a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.trend.settings') }}">
                                        <i class="material-icons">widgets</i>
                                        Trend Ayarları
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.proxies') }}">
                                        <i class="material-icons">vpn_key</i>
                                        Vekil Sunucu Yönetimi
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.hosts.file') }}">
                                        <i class="material-icons">location_searching</i>
                                        Hosts Dosyası
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <div class="collapsible-header waves-effect">
                            <i class="material-icons">computer</i>
                            <span>Sistem İzleme</span>
                            <i class="material-icons arrow">keyboard_arrow_down</i>
                        </div>
                        <div class="collapsible-body">
                            <ul>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.session.logs') }}">
                                        <i class="material-icons">accessibility</i>
                                        Ziyaretçi Logları
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.monitoring.server') }}">
                                        <i class="material-icons">desktop_mac</i>
                                        Sunucu Bilgisi
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.monitoring.background') }}">
                                        <i class="material-icons">hourglass_empty</i>
                                        Arkaplan İşleri
                                    </a>
                                </li>
                                <li class="tiny">
                                    <a class="waves-effect" href="{{ route('admin.monitoring.log') }}">
                                        <i class="material-icons">code</i>
                                        Log Ekranı
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="divider"></li>
                @endif

                @if (auth()->user()->admin)
                    <!-- sadece moderatör -->
                    <li>
                        <a href="#" class="subheader grey-text text-darken-2">Yönetici</a>
                    </li>
                    <li class="tiny">
                        <a class="waves-effect" href="#" data-name="organisation-route">
                            <i class="material-icons">group_work</i>
                            Organizasyonlar
                            <span class="badge grey white-text" data-id="organisation-count" data-tooltip="İşlem Bekleyen Pasif Organizasyonlar" data-position="right">0</span>
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect" href="{{ route('admin.tickets') }}">
                            <i class="material-icons">mail</i>
                            Destek Talepleri
                            <span class="badge grey white-text" data-id="ticket-count">0</span>
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect" href="{{ route('admin.partner.history') }}">
                            <i class="material-icons">account_balance_wallet</i>
                            Partner Ödemeleri
                            <span class="badge grey white-text" data-id="partner_payments-count" data-tooltip="İşlem Bekleyen" data-position="right">0</span>
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect" href="{{ route('admin.invoices') }}">
                            <i class="material-icons">credit_card</i>
                            Faturalar
                            <span class="badge grey white-text" data-id="organisation-invoices-count" data-tooltip="İşlem Bekleyen" data-position="right">0</span>
                        </a>
                    </li>
                    <li class="tiny">
                        <a class="waves-effect" href="{{ route('admin.user.list') }}">
                            <i class="material-icons">people</i>
                            Kullanıcılar
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li class="tiny">
                        <a class="waves-effect" href="{{ route('admin.page.list') }}">
                            <i class="material-icons">pages</i>
                            Sayfalar
                        </a>
                    </li>
                    <li class="tiny">
                        <a class="waves-effect" href="{{ route('analysis.dashboard') }}">
                            <i class="material-icons">grain</i>
                            Kelime Hafızası
                        </a>
                    </li>
                    <li class="tiny">
                        <a class="waves-effect" href="{{ route('admin.carousels') }}">
                            <i class="material-icons">view_carousel</i>
                            Carousel Yönetimi
                        </a>
                    </li>
                    <li class="divider"></li>
                @endif

                <li class="tiny">
                    <a class="waves-effect" href="{{ route('forum.index') }}">
                        <i class="material-icons">forum</i>
                        Forum
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="subheader">Kullanıcı</a>
                </li>
                @if (auth()->user()->organisation)
                    <li class="tiny">
                        <a class="waves-effect" href="{{ route('settings.organisation') }}">
                            <i class="material-icons">group_work</i>
                            Organizasyon
                        </a>
                    </li>
                    <li class="divider"></li>
                @endif
                <li class="tiny">
                    <a class="waves-effect" href="{{ route('settings.account') }}">
                        <i class="material-icons">person</i>
                        Hesap Bilgileri
                    </a>
                </li>
                <li class="tiny">
                    <a class="waves-effect" href="{{ route('settings.support') }}">
                        <i class="material-icons">help</i>
                        Destek
                    </a>
                </li>
                <li class="tiny">
                    <a class="waves-effect" href="{{ route('user.logout') }}">
                        <i class="material-icons">exit_to_app</i>
                        Çıkış
                    </a>
                </li>
                @isset($footer_hide)
                    <li class="divider"></li>
                    <li class="copyright">
                        <p class="grey-text">{{ date('Y') }} © <a href="https://veri.zone/" class="grey-text">Veri Zone</a> Bilişim Teknolojileri ve Danışmanlık Ltd. Şti.</p>
                    </li>
                @endisset
            </ul>
        @endauth

        @isset($breadcrumb)
            @php
                $br_count = count($breadcrumb)-1;
            @endphp
            <nav id="breadcrumb">
                <div class="{{ isset($sidenav_layout) ? '' : (auth()->check() ? 'sidenav-fixed-layout' : '') }}">
                    <div class="{{ isset($wide) ? 'container container-wide' : 'container' }}">
                        <a href="{{ route('dashboard') }}" class="breadcrumb">Olive</a>
                        @foreach ($breadcrumb as $key => $row)
                            @if (isset($row['link']))
                                <a href="{{ $row['link'] }}" class="breadcrumb">{{ $row['text'] }}</a>
                            @else
                                @if ($key == $br_count)
                                    <span class="breadcrumb" data-name="breadcrumb">{{ $row['text'] }}</span>
                                @else
                                    <span class="breadcrumb">{{ $row['text'] }}</span>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            </nav>
        @endisset

        <main>
            <div class="{{ isset($sidenav_layout) ? 'content' : (auth()->check() ? 'content sidenav-fixed-layout' : 'content') }}">
                @if (trim($__env->yieldContent('action-bar')))
                    <div class="{{ isset($wide) ? 'container container-wide' : 'container' }}">
                        <div class="action-bar">
                            @yield('action-bar')
                        </div>
                    </div>
                @endif

                @if (trim($__env->yieldContent('wildcard')))
                    <div class="wildcard">
                        @stack('wildcard-top')
                        @yield('wildcard')
                        @stack('wildcard-bottom')
                    </div>
                @endif

                <div class="{{ isset($wide) ? 'container container-wide' : 'container' }} d-flex">
                    @if (trim($__env->yieldContent('panel')))
                        <div id="panel">
                            @yield('panel')
                        </div>
                    @endif
                    <div class="flex-fill">
                        @isset($dock)
                            <aside id="dock-content">
                                <div class="content">
                                    @if (trim($__env->yieldContent('action-bar:half')))
                                        <div class="action-bar action-bar-half">
                                            @yield('action-bar:half')
                                        </div>
                                    @endif

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

                @if (trim($__env->yieldContent('subcard')))
                    <div class="subcard">
                        @yield('subcard')
                    </div>
                @endif
            </div>
        </main>

        <div class="dock-overlay" data-class="body" data-class-remove="dock-active"></div>
    @else
        @yield('content')
    @endisset

    @auth
        <div class="fixed-action-btn">
            <a data-trigger="module-search" id="search-trigger" class="btn-floating btn-large white waves-effect" data-tooltip="Modül Ara (CTRL + G)" data-position="left">
                <i class="material-icons grey-text text-darken-4">search</i>
            </a>
        </div>

        <div class="search-wrapper" id="module-search">
            <div class="search-content">
                <div class="right-align">
                    <a href="#" class="btn-floating btn-flat waves-effect" data-trigger="module-search-close">
                        <i class="material-icons white-text">close</i>
                    </a>
                </div>
                <div class="input-field">
                    <input
                        name="search_input"
                        id="search_input"
                        type="text"
                        class="validate json"
                        placeholder="Arayın"
                        data-href="{{ route('module.search') }}"
                        data-method="post"
                        data-delay="1"
                        data-callback="__module_search" />
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
                                'href': o.route,
                                'data-href': '{{ route('module.go') }}',
                                'data-method': 'post',
                                'data-callback': '__go',
                                'html': [
                                    $('<i />', { 'class': 'material-icons', 'html': o.icon }),
                                    $('<span />', { 'class': 'd-block', 'html': o.name })
                                ],
                                'data-module_id': o.module_id,
                                'data-include': 'search_input'
                            });

                            if (o.route)
                            {
                                item.attr('data-route', o.route)
                            }

                            item.addClass(o.root ? 'teal-text' : '')
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
                    @if (auth()->user()->admin())
                        $('[data-id=ticket-count]').html(obj.data.ticket.count).addClass(obj.data.ticket.count > 0 ? 'red' : 'grey').removeClass(obj.data.ticket.count > 0 ? 'grey' : 'red')
                        $('[data-id=organisation-count]').html(obj.data.organisation.pending.count).addClass(obj.data.organisation.pending.count > 0 ? 'red' : 'grey').removeClass(obj.data.organisation.pending.count > 0 ? 'grey' : 'red')
                        $('[data-id=organisation-invoices-count]').html(obj.data.organisation.invoices.count).addClass(obj.data.organisation.invoices.count > 0 ? 'red' : 'grey').removeClass(obj.data.organisation.invoices.count > 0 ? 'grey' : 'red')
                        $('[data-id=partner_payments-count]').html(obj.data.partner.payments.count).addClass(obj.data.partner.payments.count > 0 ? 'red' : 'grey').removeClass(obj.data.partner.payments.count > 0 ? 'grey' : 'red')

                        $('[data-name=organisation-route]').attr('href', obj.data.organisation.pending.count ? '{{ route('admin.organisation.list', [ 'status' => 'off' ]) }}' : '{{ route('admin.organisation.list', [ 'status' => '' ]) }}')
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

    @isset($footer_hide)
    @else
        <div class="{{ isset($sidenav_layout) ? '' : (auth()->check() ? isset($sidenav_fixed_layout) ? 'sidenav-fixed-layout' : '' : '') }}">
            <footer class="page-footer">
                <div class="{{ isset($wide) ? 'container container-wide' : 'container' }}">
                    <div class="row">
                        <div class="col l6 s12">
                            <img id="vz-logo" src="{{ asset('img/veri.zone_logo-grey.svg') }}" alt="veri.zone-logo" />
                            <p class="grey-text mb-0">© {{ date('Y') }} Veri Zone Bilişim Teknolojileri ve Danışmanlık Ltd. Şti.</p>
                            <p class="grey-text mb-0">İnönü Mahallesi, 1769. Sk. 1D/1, 06370 Yenimahalle/ANKARA</p>
                            <!--
                            <i class="social-icon icon-tumblr">&#xe800;</i>
                            <i class="social-icon icon-email">&#xe801;</i>
                            <i class="social-icon icon-youtube">&#xe802;</i>
                            <i class="social-icon icon-skype">&#xe804;</i>
                            <i class="social-icon icon-call">&#xe806;</i>
                            -->
                            <a href="#" class="btn-flat btn-small btn-floating social-icon mt-1">
                                <i class="social-icon icon-twitter white-text">&#xe803;</i>
                            </a>
                            <a href="#" class="btn-flat btn-small btn-floating social-icon mt-1">
                                <i class="social-icon icon-linkedin white-text">&#xe805;</i>
                            </a>
                            <a href="#" class="btn-flat btn-small btn-floating social-icon mt-1">
                                <i class="social-icon icon-facebook white-text">&#xe807;</i>
                            </a>
                            <a href="#" class="btn-flat btn-small btn-floating social-icon mt-1">
                                <i class="social-icon icon-instagram white-text">&#xe808;</i>
                            </a>
                        </div>
                        <div class="col l2 offset-l2 s12">
                            <ul class="mt-0 mb-0">
                                <li>
                                    <a class="grey-text" href="{{ route('page.view', 'hakkimizda') }}">Hakkımızda</a>
                                </li>
                                <li>
                                    <a class="grey-text" href="{{ route('forum.index') }}">Forum</a>
                                </li>
                                <li>
                                    <a class="grey-text" href="{{ route('page.view', 'iletisim') }}">İletişim</a>
                                </li>
                                <li>
                                    <a class="grey-text" href="{{ route('sources') }}">Kaynaklar</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col l2 s12">
                            <ul class="mt-0 mb-0">
                                <li>
                                    <a class="grey-text" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a>
                                </li>
                                <li>
                                    <a class="grey-text" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a>
                                </li>
                                <li>
                                    <a class="grey-text" href="{{ route('page.view', 'cerez-politikasi') }}">Çerez Politikası</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer-copyright mt-1">
                    <div class="{{ isset($wide) ? 'container container-wide' : 'container' }} grey-text">
                        {{ date('Y') }} © <a href="https://veri.zone/" class="grey-text">Veri Zone</a> | Tüm hakları saklıdır.
                    </div>
                </div>
            </footer>
        </div>
    @endisset

    <div id="loading">
        <div class="preloader-wrapper big active">
            <div class="spinner-layer">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
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
    <script src="{{ asset('js/jquery.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/materialize.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.timeago.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/core.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/data-pattern.js?v='.config('system.version')) }}"></script>

    <!-- external include -->
    @stack('external.include.footer')

    @isset($help)
        <script src="{{ asset('js/driver.min.js?v='.config('system.version')) }}"></script>
    @endisset

    <!-- local scripts -->
    <script>
    $('.modal').modal({})

    $.each($('.dropdown-trigger'), function() {
        var __ = $(this);

        __.dropdown({
            alignment: __.data('align') ? __.data('align') : 'left'
        })
    })
    $('.collapsible').collapsible()
    $('[data-tooltip]').tooltip()

    $('ul#slide-out').sidenav({
        draggable: true
    })

    @stack('local.scripts')

    function __soft_in(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.reload()
        }
    }
    </script>

    @if (config('services.google.analytics.code'))
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics.code') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag(){dataLayer.push(arguments);}

            gtag('js', new Date());

            gtag('config', '{{ config('services.google.analytics.code') }}');
        </script>
    @endif
</body>
</html>
