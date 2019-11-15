<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #00949a;
        }
        @page {
            size: 720px 1280px landscape;
        }

        html,
        body {
            margin: 0;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            margin: 32px;
        }
        h1 {
            margin: 0;
            padding: 0;
            font-size: 24px;
        }
        h3 {
            margin: 0;
            padding: 0;
            font-size: 20px;
        }
        p {
            margin: 0;
            padding: 0;
        }
        .logo {
            width: 132px;
            height: 55px;
        }
        .date {
            display: table;
        }

        img.vz {
            width: 200px;
        }

        .left-align {
          text-align: left;
        }

        .right-align {
          text-align: right;
        }

        .center-align {
          text-align: center;
        }

        .image {
            max-width: 700px;
            max-height: 400px;
        }

        .markdown img {
            max-width: 700px;
            max-height: 400px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td align="center">
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <img class="logo" alt="Logo" src="{{ public_path('img/olive_logo.png') }}" />
                <br />
                <br />
                <h1>{{ $data->title }}</h1>
                <div class="date">{{ implode(' - ', $data->dates) }}</div>
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <img class="vz" alt="Veri Zone" src="{{ public_path('img/veri.zone_logo.png') }}" />
            </td>
        </tr>
    </table>
    @foreach ($data->items as $item)
        <div class="page-break"></div>
        <table>
            <tr>
                <td align="left">
                    <h1>{{ $item['title'] }}</h1>
                    @isset ($item['subtitle'])
                        <p>{{ $item['subtitle'] }}</p>
                    @endisset
                </td>
                <td align="right">
                    <img class="logo" alt="Logo" src="{{ public_path('img/olive_logo.png') }}" />
                    <div class="date">{{ implode(' - ', $data->dates) }}<</div>
                </td>
            </tr>
            @if (@$item['text'] && @$item['image'])
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td align="left">
                        <img class="image" alt="Grafik" src="{{ $item['image'] }}" />
                    </td>
                    <td align="left">
                        <div class="markdown">
                            {!! Term::markdown($item['text']) !!}
                        </div>
                    </td>
                </tr>
            @elseif (@$item['text'])
                <tr>
                    <td colspan="2">
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <div class="markdown">
                            {!! Term::markdown($item['text']) !!}
                        </div>
                    </td>
                </tr>
            @elseif (@$item['image'])
                <tr>
                    <td colspan="2">
                        <br />
                        <br />
                        <br />
                        <br />
                        <br />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <img class="image" alt="Grafik" src="{{ $item['image'] }}" />
                    </td>
                </tr>
            @endif
        </table>
    @endforeach
    <div class="page-break"></div>
    <table>
        <tr>
            <td colspan="2" align="center">
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <img class="vz" alt="Logo" src="{{ public_path('img/veri.zone_logo.png') }}" />
                <br />
                <h4>v e r i . z o n e . t e k n o l o j i</h4>
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
            </td>
        </tr>
        <tr>
            <td align="left">
                <a href="mailto:bilgi@veri.zone">bilgi@veri.zone</a>
                <br />
                (+90) 850 301 16 30
                <br />
                <br />
                <a href="https://veri.zone/">https://veri.zone</a>
                <br />
                <a href="https://olive.veri.zone/">https://olive.veri.zone</a>
                <br />
                <br />
                <img class="logo" alt="Logo" src="{{ public_path('img/olive_logo.png') }}" />
                <br />
            </td>
            <td align="right">
                Twitter, <a href="https://twitter.com/verizonetek/">https://twitter.com/<b>verizonetek</b>/</a>
                <br />
                Instagram, <a href="https://www.instagram.com/verizonetek/">https://www.instagram.com/<b>verizonetek</b>/</a>
                <br />
                LinkedIn, <a href="https://www.linkedin.com/company/verizonetek/">https://www.linkedin.com/company/<b>verizonetek</b>/</a>
                <br />
            </td>
        </tr>
    </table>
</body>
</html>
