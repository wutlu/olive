@extends('layouts.app', [
    'footer_hide' => true
])

@section('content')
    <div class="d-table mx-auto pt-2 mt-2 pb-2 mb-2">
        <div class="bb-edge mb-2">
            <a class="bb" href="{{ route('home') }}"></a>
        </div>
        <div class="card mx-auto mb-2" style="max-width: 300px;">
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
                    <div class="right-align">
                        <button type="submit" class="waves-effect waves-light btn-flat">Giriş Yap</button>
                    </div>
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
                            <span class="helper-text">Hesap şifreniz.</span>
                        </div>
                        <div class="input-field">
                            <input name="name" id="name" type="text" class="validate" />
                            <label for="name">Kullanıcı Adı</label>
                            <span class="helper-text">Eşsiz kullanıcı adınız.</span>
                        </div>
                        <div class="input-field">
                            <div class="captcha" data-id="register-captcha"></div>
                        </div>
                        <label>
                            <input name="terms" type="checkbox" value="1" />
                            <span>
                                <a target="_blank" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a> ve <a target="_blank" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a> sayfalarındaki maddeleri okudum, kabul ediyorum.
                            </span>
                        </label>
                        <div class="right-align">
                            <button type="submit" class="waves-effect waves-light btn-flat">Kaydol</button>
                        </div>
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
                    <div class="right-align">
                        <button type="submit" class="waves-effect waves-light btn-flat">Şifre Gönder</button>
                    </div>
                </form>
            </div>
        </div>

        <p class="grey-text pl-1 pr-1">{{ date('Y') }} © <a href="https://veri.zone/">Veri Zone</a> Bilişim Teknolojileri ve Danışmanlık Ltd. Şti.</p>
    </div>
@endsection

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
@endpush

@push('local.scripts')
    function __login(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Giriş Gerçekleştirildi!', classes: 'teal' })

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
