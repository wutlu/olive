<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ $pg->name }}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }

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
    @if ($pin->document()->status == 'ok')
        @php
            $source = $pin->document()->data['_source'];
        @endphp

        <div class="data {{ $pin->document()->data['_type'] }}">
            @isset ($source['title'])
                <h3>{{ title_case($source['title']) }}</h3>
            @endisset

            <time>{{ date('d.m.Y H:i:s', strtotime($source['created_at'])) }}</time>

            @if ($pin->document()->data['_type'] == 'tweet')
                <a class="url" href="https://twitter.com/{{ $source['user']['screen_name'] }}/status/{{ $source['id'] }}" target="_blank">https://twitter.com/{{ $source['user']['screen_name'] }}/status/{{ $source['id'] }}</a>
                <p>
                    <a href="https://twitter.com/intent/user?user_id={{ $source['user']['id'] }}" target="_blank">{{ '@'.$source['user']['screen_name'] }}</a>
                    <span>{{ $source['user']['name'] }}</span>
                    <span>({{ $source['platform'] }})</span>
                </p>
                <div class="text">{!! Term::tweet($source['text']) !!}</div>
                @isset ($source['external'])
                    @php
                        $external_source = $pin->document($source['external']['id']);
                    @endphp

                    @if ($external_source)
                        <ul>
                            <li>
                                <span>Asıl Tweet</span>
                                <time>{{ date('d.m.Y H:i:s', strtotime($external_source['_source']['created_at'])) }}</time>
                                <a class="url" href="https://twitter.com/{{ $external_source['_source']['user']['screen_name'] }}/status/{{ $external_source['_source']['id'] }}" target="_blank">https://twitter.com/{{ $external_source['_source']['user']['screen_name'] }}/status/{{ $external_source['_source']['id'] }}</a>
                                <p>
                                    <a href="https://twitter.com/intent/user?user_id={{ $external_source['_source']['user']['id'] }}" target="_blank" class="red-text">{{ '@'.$external_source['_source']['user']['screen_name'] }}</a>
                                    <span>{{ $external_source['_source']['user']['name'] }}</span>
                                    <span>({{ $external_source['_source']['platform'] }})</span>
                                </p>
                                <div class="text">{!! Term::tweet($external_source['_source']['text']) !!}</div>
                            </li>
                        </ul>
                    @endif
                @endisset
            @elseif ($pin->document()->data['_type'] == 'article')
                 <a class="url" href="{{ $source['url'] }}" target="_blank">{{ $source['url'] }}</a>
                 <div class="text">{!! nl2br($source['description']) !!}</div>
            @elseif ($pin->document()->data['_type'] == 'document')
                 <a class="url" href="{{ $source['url'] }}" target="_blank">{{ $source['url'] }}</a>
                 <div class="text">{!! nl2br($source['description']) !!}</div>
            @elseif ($pin->document()->data['_type'] == 'entry')
                <a class="url" href="{{ $source['url'] }}" target="_blank">{{ $source['url'] }}</a>
                <div class="text">{!! nl2br($source['entry']) !!}</div>
            @elseif ($pin->document()->data['_type'] == 'product')
                @isset ($source['address'])
                    <ul>
                        @foreach ($source['address'] as $key => $segment)
                            <li>{{ $segment['segment'] }}</li>
                        @endforeach
                    </ul>
                @endisset

                @isset ($source['breadcrumb'])
                    <ul>
                        @foreach ($source['breadcrumb'] as $key => $segment)
                            <li>{{ $segment['segment'] }}</li>
                        @endforeach
                    </ul>
                @endisset

                <a href="{{ $source['url'] }}" target="_blank">{{ $source['url'] }}</a>
                <div>
                    <span>{{ title_case($source['seller']['name']) }}</span>

                    @isset ($source['seller']['phones'])
                        <ul>
                            @foreach ($source['seller']['phones'] as $key => $phone)
                                <li>{{ $phone['phone'] }}</li>
                            @endforeach
                        </ul>
                    @endisset
                </div>

                @isset ($source['description'])
                    <div class="text">{!! nl2br($source['description']) !!}</div>
                @endisset

                <p>
                   <span>{{ number_format($source['price']['amount']) }}</span>
                   <span>{{ $source['price']['currency'] }}</span>
                </p>
            @elseif ($pin->document()->data['_type'] == 'comment')
                <a class="url" href="https://www.youtube.com/watch?v={{ $source['video_id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $source['video_id'] }}</a>
                <p>
                    <a href="https://www.youtube.com/channel/{{ $source['channel']['id'] }}" target="_blank">{{ '@'.$source['channel']['title'] }}</a>
                </p>
                <div class="text">{!! nl2br($source['text']) !!}</div>
            @elseif ($pin->document()->data['_type'] == 'video')
                <a class="url" href="https://www.youtube.com/watch?v={{ $source['id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $source['id'] }}</a>
                <p>
                    <a href="https://www.youtube.com/channel/{{ $source['channel']['id'] }}" target="_blank">{{ '@'.$source['channel']['title'] }}</a>
                </p>

                @isset ($source['description'])
                    <div class="text">{!! nl2br($source['description']) !!}</div>
                @endisset
            @endif

            @if ($pin->comment)
                <div class="pin-comment">{!! nl2br($pin->comment) !!}</div>
            @endif
        </div>
    @else
        <div class="data">
            <p>Kaynak Okunamadı.</p>
        </div>
    @endif
@endforeach

</body>
</html>
