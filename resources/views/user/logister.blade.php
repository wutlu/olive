@extends('layouts.app', [ 'header' => 'hide' ])

@push('local.styles')
    #main > .parallax-container > .parallax {
        background-color: #111;
    }
@endpush

@section('content')
    <header id="main">
        <div class="parallax-container">
            <div class="parallax">
                <img src="{{ asset('img/bg-2.svg') }}" alt="bg-2" />
            </div>

            <div class="container">
                <div class="row">
                    <div class="col l4">
                        <a href="{{ route('home') }}" id="logo">
                            <img src="{{ asset('img/olive-logo.svg') }}" alt="olive-logo" class="responsive-img" />
                        </a>

                        <div id="dword" class="white-text">
                            Olive, daha anlamlı bir internet deneyimi sunar...
                        </div>
                        <p class="cyan-text lead">Internet artık daha net!</p>
                    </div>
                    <div class="col l7 offset-l1 xl5 offset-xl3">
                        <div class="card cyan darken-4" style="margin: 2rem 0;">
                            <div class="card-content">
                                <p class="white-text">Hemen bir hesap oluşturun ve bir çok aracı <strong>ücretsiz</strong> bir şekilde kullanmaya başlayın.</p>
                            </div>
                            <div class="card-tabs">
                                <ul class="tabs tabs-transparent tabs-fixed-width">
                                    <li class="tab">
                                        <a href="#tab-giris" class="active waves-effect waves-light">Giriş</a>
                                    </li>
                                    <li class="tab">
                                        <a href="#tab-kaydol" class="waves-effect waves-light">Kayıt</a>
                                    </li>
                                    <li class="tab">
                                        <a href="#tab-sifre" class="waves-effect waves-light">Şifre</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-content grey lighten-4">
                                <div id="tab-giris">
                                    <form id="login-form" data-callback="__login" action="{{ route('user.login') }}" method="post" class="json">
                                        <div class="row">
                                            <div class="input-field col s12">
                                                <input name="email_login" id="email_login" type="email" class="validate" />
                                                <label for="email_login">E-posta</label>
                                                <span class="helper-text">E-posta adresiniz.</span>
                                            </div>
                                            <div class="input-field col s12">
                                                <input name="password_login" id="password_login" type="password" class="validate" />
                                                <label for="password_login">Şifre</label>
                                                <span class="helper-text">Hesap şifreniz.</span>
                                            </div>
                                            <div class="col s12 right-align">
                                                <button type="submit" class="waves-effect waves-light btn cyan darken-4">Giriş Yap</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div id="tab-kaydol" style="display: none;">
                                    <form id="register-form" data-callback="__register" action="{{ route('user.register') }}" method="put" class="json">
                                        <div class="row">
                                            <div class="input-field col s12">
                                                <input name="email" id="email" type="email" class="validate" />
                                                <label for="email">E-posta</label>
                                                <span class="helper-text">E-posta adresiniz.</span>
                                            </div>
                                            <div class="input-field col s12">
                                                <input name="password" id="password" type="password" class="validate" />
                                                <label for="password">Şifre</label>
                                                <span class="helper-text">Hesap şifreniz.</span>
                                            </div>
                                            <div class="input-field col s12">
                                                <input name="name" id="name" type="text" class="validate" />
                                                <label for="name">Ad</label>
                                                <span class="helper-text">Kullanıcı Adınız.</span>
                                            </div>
                                            <div class="input-field col s12">
                                                <input name="reference_code" id="reference_code" type="text" class="validate" />
                                                <label for="reference_code">Referans Kodu</label>
                                                <span class="helper-text">Varsa referans kodunuz.</span>
                                            </div>
                                            <div class="input-field col s12">
                                                <div class="captcha" data-id="register-captcha"></div>
                                            </div>
                                            <div class="col s12">
                                                <label>
                                                    <input name="terms" type="checkbox" value="1" />
                                                    <span>
                                                        <a target="_blank" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a> ve <a target="_blank" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a> sayfalarındaki maddeleri okudum, kabul ediyorum.
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="col s12 right-align">
                                                <button type="submit" class="waves-effect waves-light btn cyan darken-4">Kaydol</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div id="tab-sifre" style="display: none;">
                                    <form id="password-form" data-callback="__password" action="{{ route('user.password') }}" method="post" class="json">
                                        <div class="row">
                                            <div class="input-field col s12">
                                                <input name="email_password" id="email_password" type="email" class="validate" />
                                                <label for="email_password">E-posta</label>
                                                <span class="helper-text">E-posta adresiniz.</span>
                                            </div>
                                            <div class="input-field col s12">
                                                <div class="captcha" data-id="password-captcha"></div>
                                            </div>
                                            <div class="col s12 right-align">
                                                <button type="submit" class="waves-effect waves-light btn cyan darken-4">Şifre Gönder</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
@endpush

@push('local.scripts')
    function __login(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Giriş gerçekleştiriliyor...', classes: 'green darken-2' })

            setTimeout(goDashboard, 1000)
        }
        else if (obj.status == 'ban')
        {
            var mdl = modal({
                'id': 'err',
                'body': obj.data.reason,
                'title': 'Ban',
                'size': 'modal-small',
                'options': {},
                'footer': [
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat cyan-text',
                       'html': buttons.ok
                   })
                ]
            })
        }
    }

    function __register(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Hesap Oluşturuluyor...', classes: 'green darken-2' })

            setTimeout(goDashboard, 1000)
        }
    }

    function goDashboard()
    {
        location.href = '{{ route('dashboard') }}';
    }

    function __password(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Size bir doğrulama bağlantısı gönderdik.', classes: 'green darken-2' })

            $('#password-form')[0].reset()
            captcha()
        }
    }

    $(document).ready(function() {
        $('.tabs').tabs({
            onShow: function(o) {
                if (o.id == 'tab-kaydol')
                {
                    document.title = '{{ config('app.name') }}: Kaydol';
                }
                else if (o.id == 'tab-giris')
                {
                    document.title = '{{ config('app.name') }}: Giriş Yap';
                }
                else if (o.id == 'tab-sifre')
                {
                    document.title = '{{ config('app.name') }}: Şifre';
                }
            }
        })

        $('.parallax').parallax()
    });
@endpush
