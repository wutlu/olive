<form id="soft_in-form" data-callback="__soft_in" action="{{ route('user.login') }}" method="post" class="json">
    <div class="row">
        <div class="col s12">
            <div class="card-panel teal white-text">Oturum süresi sona erdi. Lütfen tekrar giriş yapın.</div>
        </div>
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
