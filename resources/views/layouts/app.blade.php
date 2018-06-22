<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, shring-to-fit=no, user-scalable=no" />

    <title>@yield('title', config('app.name'))</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <link rel="icon" href="{{ asset('img/favicon.png') }}" />

    <link rel="stylesheet" type="text/css" href="{{ asset('css/materialize.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/master.css') }}" />
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

    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/materialize.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/master.js') }}"></script>
	<script>
	$(document).ready(function(){
		$('.sidenav').sidenav();
		$('.tabs').tabs();
		$('.parallax').parallax();
		$('.modal').modal();
		$('.dropdown-trigger').dropdown();
		$('[data-tooltip]').tooltip();
	});

	$('.down-area').on('click', 'a.btn-large', function(e) {
		scrollTo({
			'target': '#more-step'
		})
	})
	</script>
</body>
</html>