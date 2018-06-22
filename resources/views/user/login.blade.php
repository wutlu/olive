@extends('layouts.app', [ 'header' => 'hide' ])

@section('content')
<header id="main">
    <div class="parallax-container">
        <div class="parallax">
        	<img src="{{ asset('img/bg-2.svg') }}" alt="bg-1" />
        	<video autoplay muted loop id="background-video">
				<source src="{{ asset('video/world.mp4') }}" type="video/mp4">
			</video>
        </div>

		<div class="container">
			<div class="row">
				<div class="col l4">
					<a href="#" id="logo">
					    <img src="{{ asset('img/olive-logo.svg') }}" alt="olive-logo" class="responsive-img" />
					</a>

					<p class="white-text">Olive, büyük veri inceleme ara katmanıdır.</p>
					<p class="grey-text">Hedef bilgiye çok daha hızlı ve daha anlamlı bir şekilde erişmenizi sağlar.</p>
					<p class="grey-text">100% Türkçe platform, 100% yerli veri.</p>
				</div>
				<div class="col l6 offset-l2 xl4 offset-xl4">
					<div class="card cyan darken-4" id="account-card">
						<div class="card-content">
							<p class="white-text">Hemen bir hesap oluşturun ve bir çok aracı <strong>ücretsiz</strong> bir şekilde kullanmaya başlayın.</p>
						</div>
						<div class="card-tabs">
							<ul class="tabs tabs-transparent tabs-fixed-width">
								<li class="tab">
									<a href="#tab-giris" class="active">Giriş Yap</a>
								</li>
								<li class="tab">
									<a href="#tab-kaydol">Kaydol</a>
								</li>
							</ul>
						</div>
						<div class="card-content grey lighten-4">
							<div id="tab-giris" class="active">
								<form>
									<div class="row">
										<div class="input-field col s12">
											<input id="email" type="email" class="validate" />
											<label for="email">E-posta</label>
											<span class="helper-text" data-error="E-posta adresi geçerli değil." data-success="E-posta adresi geçerli.">E-posta adresiniz.</span>
										</div>
										<div class="input-field col s12">
											<input id="password" type="password" class="validate" />
											<label for="password">Şifre</label>
											<span class="helper-text" data-error="Şifre alanı boş kalamaz." data-success="Şifre deseni geçerli.">Hesap şifreniz.</span>
										</div>
										<div class="right-align">
											<a class="waves-effect btn-flat">Yeni Şifre?</a>
											<a class="waves-effect waves-light btn cyan darken-4">Giriş Yap</a>
										</div>
									</div>
								</form>
							</div>
							<div id="tab-kaydol">
								<form>
									<div class="row">
										<div class="input-field col s12">
											<input id="name" type="text" class="validate" />
											<label for="name">Ad</label>
											<span class="helper-text" data-error="Ad geçerli değil." data-success="Ad geçerli.">Tam Adınız.</span>
										</div>
										<div class="input-field col s12">
											<input id="email" type="email" class="validate" />
											<label for="email">E-posta</label>
											<span class="helper-text" data-error="E-posta adresi geçerli değil." data-success="E-posta adresi geçerli.">E-posta adresiniz.</span>
										</div>
										<div class="input-field col s12">
											<input id="password" type="password" class="validate" />
											<label for="password">Şifre</label>
											<span class="helper-text" data-error="Şifre alanı boş kalamaz." data-success="Şifre deseni geçerli.">Hesap şifreniz.</span>
										</div>
										<div class="col s12">
									    	<label>
									    		<input type="checkbox" />
									    		<span>Sayfanın altında bulunan Kullanım Koşulları ve Gizlilik Politikası sayfalarındaki maddeleri okudum, kabul ediyorum.</span>
									    	</label>
									    </div>
										<div class="right-align">
											<a class="waves-effect waves-light btn cyan darken-4">Kaydol</a>
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
