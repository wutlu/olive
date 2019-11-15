<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ $pg->name }}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
            }

            .clearfix:after {
                display: block;
                clear: both;
                content: "";
            }
            .clearfix > .left { float: left; }
            .clearfix > .right { float: right; }

            time {
                color: #999;
                font-style: italic;
                font-size: 14px;
            }

            a {
                text-decoration: none;
            }

            header {
                padding: 1rem;
                margin: 0;
            }
            header h1 {
                text-align: center;
                margin: 0;
            }
            header img.logo { width: 128px; }

            .data {
                margin: 0 0 12px;
                padding: 24px;
            }
            .data > h3 {
                margin: 0;
            }
            .data time {
                display: block;
            }
            .data > a.url {
                font-style: italic;
                font-size: 14px;
            }

           .data > .pin-comment {
                background-color: #fbc02d;
                margin: 0 0 12px;
                padding: 24px;
                font-size: 14px;
            }

           .data .text {
                background-color: #f6f6f6;
                color: #666;
                margin: 0 0 12px;
                padding: 24px;
            }

            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
<body>
<header>
    <div class="clearfix">
        <div class="left">
            <a href="{{ config('app.url') }}" target="_blank">
                <img class="logo" alt="Logo" src="{{ asset('img/olive_logo.png') }}" />
            </a>
        </div>
        <div class="right">
            <time>{{ date('d.m.Y H:i') }}</time>
        </div>
    </div>
    <h1>{{ $pg->name }}</h1>
</header>

@foreach ($pins as $pin)
        <div class="data {{ $pin->type }}">
            @isset ($pin->content['title'])
                <h3>{{ title_case($pin->content['title']) }}</h3>
            @endisset

            <time>{{ date('d.m.Y H:i:s', strtotime($pin->content['created_at'])) }}</time>

            @if ($pin->type == 'tweet')
                <a class="url" href="https://twitter.com/{{ $pin->content['user']['screen_name'] }}/status/{{ $pin->content['id'] }}" target="_blank">https://twitter.com/{{ $pin->content['user']['screen_name'] }}/status/{{ $pin->content['id'] }}</a>
                <p>
                    <a href="https://twitter.com/intent/user?user_id={{ $pin->content['user']['id'] }}" target="_blank">{{ '@'.$pin->content['user']['screen_name'] }}</a>
                    <span>{{ $pin->content['user']['name'] }}</span>
                    <span>({{ $pin->content['platform'] }})</span>
                </p>
                <div class="text">{!! Term::tweet($pin->content['text']) !!}</div>
            @elseif ($pin->type == 'article')
                 <a class="url" href="{{ $pin->content['url'] }}" target="_blank">{{ $pin->content['url'] }}</a>
                 <div class="text">{!! nl2br($pin->content['description']) !!}</div>
            @elseif ($pin->type == 'document')
                 <a class="url" href="{{ $pin->content['url'] }}" target="_blank">{{ $pin->content['url'] }}</a>
                 <div class="text">{!! nl2br($pin->content['description']) !!}</div>
            @elseif ($pin->type == 'entry')
                <a class="url" href="{{ $pin->content['url'] }}" target="_blank">{{ $pin->content['url'] }}</a>
                <div class="text">{!! nl2br($pin->content['entry']) !!}</div>
            @elseif ($pin->type == 'product')
                @isset ($pin->content['address'])
                    <ul>
                        @foreach ($pin->content['address'] as $key => $segment)
                            <li>{{ $segment['segment'] }}</li>
                        @endforeach
                    </ul>
                @endisset

                @isset ($pin->content['breadcrumb'])
                    <ul>
                        @foreach ($pin->content['breadcrumb'] as $key => $segment)
                            <li>{{ $segment['segment'] }}</li>
                        @endforeach
                    </ul>
                @endisset

                <a href="{{ $pin->content['url'] }}" target="_blank">{{ $pin->content['url'] }}</a>
                <div>
                    <span>{{ title_case($pin->content['seller']['name']) }}</span>

                    @isset ($pin->content['seller']['phones'])
                        <ul>
                            @foreach ($pin->content['seller']['phones'] as $key => $phone)
                                <li>{{ $phone['phone'] }}</li>
                            @endforeach
                        </ul>
                    @endisset
                </div>

                @isset ($pin->content['description'])
                    <div class="text">{!! nl2br($pin->content['description']) !!}</div>
                @endisset

                <p>
                   <span>{{ number_format($pin->content['price']['amount']) }}</span>
                   <span>{{ $pin->content['price']['currency'] }}</span>
                </p>
            @elseif ($pin->type == 'comment')
                <a class="url" href="https://www.youtube.com/watch?v={{ $pin->content['video_id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $pin->content['video_id'] }}</a>
                <p>
                    <a href="https://www.youtube.com/channel/{{ $pin->content['channel']['id'] }}" target="_blank">{{ '@'.$pin->content['channel']['title'] }}</a>
                </p>
                <div class="text">{!! nl2br($pin->content['text']) !!}</div>
            @elseif ($pin->type == 'video')
                <a class="url" href="https://www.youtube.com/watch?v={{ $pin->content['id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $pin->content['id'] }}</a>
                <p>
                    <a href="https://www.youtube.com/channel/{{ $pin->content['channel']['id'] }}" target="_blank">{{ '@'.$pin->content['channel']['title'] }}</a>
                </p>

                @isset ($pin->content['description'])
                    <div class="text">{!! nl2br($pin->content['description']) !!}</div>
                @endisset
            @endif

            @if ($pin->comment)
                <div class="pin-comment">{!! nl2br($pin->comment) !!}</div>
            @endif
        </div>
@endforeach

</body>
</html>
