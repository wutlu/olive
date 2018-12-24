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
		color: #fff;
		text-decoration: none;
	}

	html {
		height: 100%;
	}

	body {
		background: #333;
		background:         linear-gradient(45deg, #000000 0%, #2488d5 100%);
		background:    -moz-linear-gradient(45deg, #000000 0%, #2488d5 100%);
		background: -webkit-linear-gradient(45deg, #000000 0%, #2488d5 100%);

		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#000000', endColorstr='#2488d5', GradientType=1 );

		background-repeat: no-repeat;
		background-size: cover;
		background-attachment: scroll;
	}

	header.main {
		margin-bottom: 4rem;
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
		background-color: #fff;
		margin: 0 .4rem 0 0;
		padding: .4rem;
		border-radius: 10%;
	}
	footer.main > a > img,
	footer.main > a > span {
  		-ms-flex-item-align: center;
  		align-self: center;
	}
	footer.main > span {
		color: #fff;
	}

	@media (max-width: 36em) {
		header.main > img {
			max-width: 240px;
		}
	}
  </style>

  <meta name="theme-color" content="#2488d5" />
</head>
<body>
	<header class="main">
		<img alt="veri.zone" src="{{ asset('img/veri.zone-logo.svg') }}" />
	</header>
	<footer class="main">
		<a href="https://www.instagram.com/bigverizone">
			<img alt="Instagram" src="{{ asset('img/icons/instagram.png') }}" />
			<span>bigverizone</span>
		</a>
		<a href="https://twitter.com/bigverizone">
			<img alt="Twitter" src="{{ asset('img/icons/twitter.png') }}" />
			<span>bigverizone</span>
		</a>
		<span><a href="mailto:destek@veri.zone">destek@veri.zone</a></span>
		<span>Ankara'da sevgi ile üretiliyor.</span>
	</footer>
</body>
</html>
