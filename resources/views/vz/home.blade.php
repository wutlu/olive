<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, shring-to-fit=no, user-scalable=no" />

  <title>veri.zone</title>
  <meta name="description" content="veri.zone, dijital dünyayı sizin için anlamlandırır." />

  <link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300" rel="stylesheet" />

  <link rel="icon" href="{{ asset('favicon.ico') }}" />

  <style type="text/css">
	::selection {
	  background-color: rgb(126, 255, 247);
	  color: #2488d5;
	}

	::-moz-selection {
	  background-color: rgb(126, 255, 247);
	  color: #2488d5;
	}

	* {
	    font-family: 'Open Sans Condensed', sans-serif;
	}

	a {
		color: #7efff7;
		text-decoration: none;
	}

	html {
		height: 100%;
	}

	body {
		background-color: #333;
		background-image: url('../img/low_full.png');
		background-position: top right;
		background-repeat: no-repeat;
		background-attachment: scroll;
	}

	section.main {
		max-width: 340px;
		margin: 0 auto;
		color: #fff;
		text-align: left;
		display: none;
	}

	header.main > img {
		max-width: 340px;
		margin: 10% auto 1rem;
		display: table;
	}
	footer.main {
	    display:         flex;
		display: 	     flex;
		display: -webkit-flex;

	    		flex-wrap: wrap;
	    -webkit-flex-wrap: wrap;

		align-items: center;
		justify-content: center;

		margin-bottom: 4rem;
	}
	footer.main > span,
	footer.main > a {
  		display: -ms-flexbox;
  		display: flex;
		color: #2488d5;
		padding: 0 1rem;
	}
	footer.main > a > img {
		width: 32px;
		height: 32px;
		margin: 0 .4rem 0 0;
	}
	footer.main > a > img,
	footer.main > a > span {
  		-ms-flex-item-align: center;
  		align-self: center;
  		color: #7efff7;
	}
	footer.main > span {
		color: #7efff7;
	}

	@media (max-width: 36em) {
		header.main > img {
			max-width: 240px;
		}
	}

	section {
		display: table;
		margin: 0 auto;
	}
  </style>

  <meta name="theme-color" content="#2488d5" />
</head>
<body>
	<header class="main">
		<img alt="veri.zone" src="{{ asset('img/veri.zone-logo.svg') }}" />
	</header>
	<footer class="main">
		<a href="https://www.instagram.com/veri.zone">
			<img alt="Instagram" src="{{ asset('img/o_instagram.svg') }}" />
			<span>veri.zone</span>
		</a>
		<a href="https://twitter.com/veridotzone">
			<img alt="Twitter" src="{{ asset('img/o_twitter.svg') }}" />
			<span>veridotzone</span>
		</a>
		<a href="#" data-trigger="contact">
			<img alt="Contact" src="{{ asset('img/o_contact.svg') }}" />
			<span>İletişim</span>
		</a>
		<span>© 2019 | veri.zone</span>
	</footer>
	<section class="main" data-name="contact">
		<h4>İletişim Bilgileri</h4>
		<p>Yeni Mah. Eti Cad. 78/B Polatlı/ANKARA</p>
		<p>+90 850 302 1630</p>
		<p>bilgi@veri.zone</p>
		<p>Alper Mutlu TOKSÖZ</p>
	</section>

    <script src="{{ asset('js/jquery.min.js?v='.config('system.version')) }}"></script>
    <script>
    	$(document).on('click', '[data-trigger=contact]', function() {
    		$('[data-name=contact]').toggle()
    	})
    </script>
</body>
</html>
