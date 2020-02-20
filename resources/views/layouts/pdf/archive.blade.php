<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ $archive->name }}</title>
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

           .data > .item-comment {
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
                <img class="logo" alt="Logo" src="{{ asset('img/8vz.net_logo.svg') }}" />
            </a>
        </div>
        <div class="right">
            <time>{{ date('d.m.Y H:i') }}</time>
        </div>
    </div>
    <h1>{{ $archive->name }}</h1>
</header>

@foreach ($items as $item)
    <div class="data {{ $item->type }}">
        @isset ($item->content['title'])
            <h3>{{ title_case($item->content['title']) }}</h3>
        @endisset

        <time>{{ date('d.m.Y H:i:s', strtotime($item->content['created_at'])) }}</time>

        @if ($item->type == 'tweet')
            <a class="url" href="https://twitter.com/{{ $item->content['user']['screen_name'] }}/status/{{ $item->content['id'] }}" target="_blank">https://twitter.com/{{ $item->content['user']['screen_name'] }}/status/{{ $item->content['id'] }}</a>
            <p>
                <a href="https://twitter.com/intent/user?user_id={{ $item->content['user']['id'] }}" target="_blank">{{ '@'.$item->content['user']['screen_name'] }}</a>
                <span>{{ $item->content['user']['name'] }}</span>
                <span>({{ $item->content['platform'] }})</span>
            </p>
            <div class="text">{!! Term::tweet($item->content['text']) !!}</div>
        @elseif ($item->type == 'article')
             <a class="url" href="{{ $item->content['url'] }}" target="_blank">{{ $item->content['url'] }}</a>
             <div class="text">{!! nl2br($item->content['description']) !!}</div>
        @elseif ($item->type == 'document')
             <a class="url" href="{{ $item->content['url'] }}" target="_blank">{{ $item->content['url'] }}</a>
             <div class="text">{!! nl2br($item->content['description']) !!}</div>
        @elseif ($item->type == 'entry')
            <a class="url" href="{{ $item->content['url'] }}" target="_blank">{{ $item->content['url'] }}</a>
            <div class="text">{!! nl2br($item->content['entry']) !!}</div>
        @elseif ($item->type == 'product')
            @isset ($item->content['address'])
                <ul>
                    @foreach ($item->content['address'] as $key => $segment)
                        <li>{{ $segment['segment'] }}</li>
                    @endforeach
                </ul>
            @endisset

            @isset ($item->content['breadcrumb'])
                <ul>
                    @foreach ($item->content['breadcrumb'] as $key => $segment)
                        <li>{{ $segment['segment'] }}</li>
                    @endforeach
                </ul>
            @endisset

            <a href="{{ $item->content['url'] }}" target="_blank">{{ $item->content['url'] }}</a>
            <div>
                <span>{{ title_case($item->content['seller']['name']) }}</span>

                @isset ($item->content['seller']['phones'])
                    <ul>
                        @foreach ($item->content['seller']['phones'] as $key => $phone)
                            <li>{{ $phone['phone'] }}</li>
                        @endforeach
                    </ul>
                @endisset
            </div>

            @isset ($item->content['description'])
                <div class="text">{!! nl2br($item->content['description']) !!}</div>
            @endisset

            <p>
               <span>{{ number_format($item->content['price']['amount']) }}</span>
               <span>{{ $item->content['price']['currency'] }}</span>
            </p>
        @elseif ($item->type == 'comment')
            <a class="url" href="https://www.youtube.com/watch?v={{ $item->content['video_id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $item->content['video_id'] }}</a>
            <p>
                <a href="https://www.youtube.com/channel/{{ $item->content['channel']['id'] }}" target="_blank">{{ '@'.$item->content['channel']['title'] }}</a>
            </p>
            <div class="text">{!! nl2br($item->content['text']) !!}</div>
        @elseif ($item->type == 'video')
            <a class="url" href="https://www.youtube.com/watch?v={{ $item->content['id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $item->content['id'] }}</a>
            <p>
                <a href="https://www.youtube.com/channel/{{ $item->content['channel']['id'] }}" target="_blank">{{ '@'.$item->content['channel']['title'] }}</a>
            </p>

            @isset ($item->content['description'])
                <div class="text">{!! nl2br($item->content['description']) !!}</div>
            @endisset
        @endif

        @if ($item->comment)
            <div class="item-comment">{!! nl2br($item->comment) !!}</div>
        @endif
    </div>
@endforeach

</body>
</html>
