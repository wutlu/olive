@extends('layouts.app', [
    'footer_hide' => true
])

@push('local.styles')
    body {
        overflow: hidden;
    }
    .main {
        height: 100vh;
    }
    .main > .spot {
        background-image: url('{{ $photo['img'] }}');
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
    }
    .main > .spot > .text {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        color: #fff;
        font-size: 24px;
        text-transform: uppercase;
    }
    .main > .area {
        max-width: 400px;
        padding: 4rem 2rem;
        background-color: #fff;
        overflow: auto;
    }

    @media (max-width: 1024px) {
        .main > .spot {
            display: none;
        }
        .main > .area {
            max-width: 100%;
        }
    }
    canvas#bubble {
        width: 100%;
        height: 100%;
        background-color: rgba(70, 70, 120, .4);
    }
@endpush

@section('content')
    <div class="main d-flex">
        <div class="spot flex-fill">
            <div class="text">{{ $photo['text'] }}</div>
            <canvas class="overlay bg-overlay" id="bubble"></canvas>
        </div>
        <div class="area flex-fill z-depth-1">
            <div class="bb"></div>
            <div class="card card-unstyled mx-auto mb-2">
                <div class="card-tabs">
                    <ul class="tabs tabs-fixed-width">
                        <li class="tab">
                            <a href="#tab-giris" class="active waves-effect waves-light">Giriş</a>
                        </li>
                        @if (config('system.user.registration'))
                            <li class="tab">
                                <a href="#tab-kaydol" class="waves-effect waves-light">Kayıt</a>
                            </li>
                        @endif
                        <li class="tab">
                            <a href="#tab-sifre" class="waves-effect waves-light">Şifre</a>
                        </li>
                    </ul>
                </div>
                <div class="card-content white" id="tab-giris">
                    <form id="login-form" data-callback="__login" action="{{ route('user.login') }}" method="post" class="json">
                        <div class="input-field">
                            <input name="value_login" id="value_login" type="text" class="validate" />
                            <label for="value_login">E-posta veya Kullanıcı Adı</label>
                            <span class="helper-text">E-posta adresiniz veya kullanıcı adınız.</span>
                        </div>
                        <div class="input-field">
                            <input name="password_login" id="password_login" type="password" class="validate" />
                            <label for="password_login">Şifre</label>
                            <span class="helper-text">Hesap şifreniz.</span>
                        </div>
                        <button type="submit" class="waves-effect waves-light btn blue-grey" style="width: 100%;">Giriş Yap</button>
                    </form>
                </div>

                @if (config('system.user.registration'))
                    <div class="card-content white" id="tab-kaydol" style="display: none;">
                        <form id="register-form" data-callback="__register" action="{{ route('user.register') }}" method="put" class="json">
                            <div class="input-field">
                                <input name="email" id="email" type="email" class="validate" />
                                <label for="email">E-posta</label>
                                <span class="helper-text">E-posta adresiniz.</span>
                            </div>
                            <div class="input-field">
                                <input name="password" id="password" type="password" class="validate" />
                                <label for="password">Şifre</label>
                                <span class="helper-text">Özel hesap şifreniz.</span>
                            </div>
                            <div class="input-field">
                                <input name="name" id="name" type="text" class="validate" />
                                <label for="name">Kullanıcı Adı</label>
                                <span class="helper-text">Herkese açık benzersiz kullanıcı adınız.</span>
                            </div>
                            <div class="input-field">
                                <div class="captcha" data-id="register-captcha"></div>
                            </div>
                            <label>
                                <input name="terms" type="checkbox" value="1" />
                                <span>
                                    <a class="blue-grey-text text-darken-2" target="_blank" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a>'nı ve<br />
                                    <a class="blue-grey-text text-darken-2" target="_blank" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a>'nı<br />
                                    okudum ve<br />
                                    kabul ediyorum.
                                </span>
                            </label>
                            <button type="submit" class="waves-effect waves-light btn blue-grey mt-1" style="width: 100%;">Kaydol</button>
                        </form>
                    </div>
                @endif

                <div class="card-content white" id="tab-sifre" style="display: none;">
                    <form id="password-form" data-callback="__password" action="{{ route('user.password') }}" method="post" class="json">
                        <div class="input-field">
                            <input name="email_password" id="email_password" type="email" class="validate" />
                            <label for="email_password">E-posta</label>
                            <span class="helper-text">Sisteme kayıtlı e-posta adresiniz.</span>
                        </div>
                        <div class="input-field">
                            <div class="captcha" data-id="password-captcha"></div>
                        </div>
                        <button type="submit" class="waves-effect waves-light btn blue-grey" style="width: 100%;">Şifre Gönder</button>
                    </form>
                </div>
            </div>
            <p class="blue-grey-text">{{ date('Y') }} © <a href="https://veri.zone/" target="_blank">Veri Zone Teknoloji</a> - Tüm hakları saklıdır.</p>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
    <script src="{{ asset('js/bubble.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    function __login(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Giriş Gerçekleştirildi! Yönlendiriliyorsunuz...', classes: 'teal' })

            setTimeout(__goDashboard, 1000)
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
                       'class': 'modal-close waves-effect btn-flat',
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
            M.toast({ html: 'Hesap Oluşturuldu!', classes: 'green darken-2' })

            setTimeout(__goDashboard, 1000)
        }
    }

    function __goDashboard()
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
    })
@endpush
