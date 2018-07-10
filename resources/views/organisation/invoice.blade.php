<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- charset -->
    <meta charset="utf-8" />

    <!-- title -->
    <title>FATURA #{{ $data->invoice['id'] }}</title>

    <style>
    * {
        font-family: 'Courier', sans-serif;
    }
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }
    body {
        padding: 2rem;
    }
    .container {
        width: 1024px;
        margin: 0 auto;
        display: table;
    }
    .row {
        display: -ms-flexbox;
        display: flex;

        -ms-flex-pack: justify;
        justify-content: space-between;

        margin: 0 0 1rem;
    }
    .row-end {
        -ms-flex-pack: end;
        justify-content: flex-end;
    }
    .row-col {
        -ms-flex-item-align: center;
        align-self: center;
    }
    .row > .p-1 {
        padding: 1rem;
    }
    .row > .p-1:first-child {
        padding-left: 0;
    }
    .row > .p-1:last-child {
        padding-right: 0;
    }
    .static-width {
        display: table;
        width: 300px;
    }
    header > .company > img#logo {
        width: 256px;
        margin: 0 0 1rem;
    }
    header > .seal > img#seal {
        width: 128px;
    }
    header > .invoice > h1 {
        margin: 0;
        padding: 0;
        text-align: right;

        border-width: 0 0 .4rem;
        border-style: solid;
        border-color: inherit;
        color: #333;
    }
    ul {
        list-style: none;
        margin: 0 0 1rem;
        padding: 0;
    }
    ul > li {
        line-height: 24px;
    }
    .title {
        font-weight: bold;
    }
    .dashed-line {
        border-style: dashed;
        border-color: #333;
        margin: 0;
    }
    .dashed-line.dashed-bottom {
        border-width: 0 0 1px;
    }
    .self-area {
        padding: 1rem 0;
    }
    .self-area > .title {
        margin: 0 0 1rem;
    }
    .mb-0 {
        margin-bottom: 0;
    }
    table {
        width: 100%;
    }
    table > thead > tr > th {
        border-width: 0 0 1px;
        border-style: dashed;
        border-color: #333;

        padding-top: 1rem;
        padding-bottom: 1rem;

        font-weight: bold;
    }
    table > tbody > tr > td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    table .description {
        text-align: left;
    }
    table .quantity {
        text-align: right;
        padding-right: 2rem;
        color: #999;
    }
    table .unit-price {
        text-align: left;
        width: 150px;
        color: #999;
    }
    table .total {
        text-align: right;
        width: 150px;
    }
    footer > .total {
        border-width: 0 0 .4rem;
        border-style: solid;
        border-color: inherit;
        color: #333;

        padding: 1rem 0;
    }
    .green-text {
        color: #4caf50;
    }
    .red-text {
        color: #e53935;
    }
    p {
        margin: 0;
    }
    </style>
</head>
<body>

<div class="container">
    <header class="row">
        <div class="row-col company">
            <img id="logo" alt="invoice-logo" src="{{ asset(config('formal.company')['logo']) }}" />

            <ul>
                <li class="title">{{ config('formal.company')['name'] }}</li>
                @foreach (config('formal.company')['address'] as $key => $line)
                <li>{{ $line }}</li>
                @endforeach
            </ul>
            <ul>
                @foreach (config('formal.company')['contact'] as $key => $line)
                <li>{{ $line }}</li>
                @endforeach
            </ul>
            @isset(config('formal.company')['taxOffice'])
            <ul>
                <li class="title">{{ config('formal.company')['taxOffice']['name'] }} V.D. N<sup>o</sup></li>
                <li>{{ config('formal.company')['taxOffice']['no'] }}</li>
            </ul>
            @endisset
            @isset(config('formal.company')['tradeRegisterNo'])
            <ul>
                <li class="title">Ticaret Sicil N<sup>o</sup></li>
                <li>{{ config('formal.company')['tradeRegisterNo'] }}</li>
            </ul>
            @endisset
        </div>
        @isset($data->invoice['formal'])
        <div class="row-col seal">
            <img id="seal" alt="veri.zone-logo" src="{{ asset('img/maliye-damga.svg') }}" />
        </div>
        @endisset
        <div class="row-col invoice static-width">
            <h1>FATURA</h1>
            <ul class="row dashed-line dashed-bottom">
                <li class="row-col p-1 title">NO</li>
                <li class="row-col p-1">#{{ $data->invoice['id'] }}</li>
            </ul>
            @isset($data->invoice['formal'])
            <ul class="row dashed-line dashed-bottom">
                <li class="row-col p-1 title">SERİ</li>
                <li class="row-col p-1">{{ $data->invoice['formal']['serial'] }}</li>

                <li class="row-col p-1 title">SIRA</li>
                <li class="row-col p-1">{{ $data->invoice['formal']['no'] }}</li>
            </ul>
            @endisset
            <div class="self-area dashed-line dashed-bottom">
                <div class="title">MÜŞTERİ</div>
                <div class="body">
                    <ul class="mb-0">
                        <li class="title">{{ $data->consumer['name'] }}</li>
                        @foreach ($data->consumer['address'] as $key => $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <ul class="row mb-0">
                <li class="row-col p-1 title">SİPARİŞ TARİHİ</li>
                <li class="row-col p-1">{{ $data->orderDate }}</li>
            </ul>
            @isset($data->paidDate)
            <ul class="row mb-0 green-text">
                <li class="row-col title">ÖDENDİĞİ TARİH</li>
                <li class="row-col">{{ $data->paidDate }}</li>
            </ul>
            @endisset
            @isset($data->dueDate)
            <ul class="row red-text dashed-line dashed-bottom">
                <li class="row-col p-1 title">SON ÖDEME TARİHİ</li>
                <li class="row-col p-1">{{ $data->dueDate }}</li>
            </ul>
            @endisset
        </div>
    </header>
    <table class="dashed-line dashed-bottom">
        <thead>
            <tr>
                <th class="description">AÇIKLAMA</th>
                <th class="quantity">MİKTAR</th>
                <th class="unit-price">BİRİM FİYATI</th>
                <th class="total">TOPLAM</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->products as $key => $product)
            <tr>
                <td class="description">
                @foreach ($product['description'] as $k => $description)
                    <p>{{ $description }}</p>
                @endforeach
                </td>
                <td class="quantity">{{ $product['quantity'] }}</td>
                <td class="unit-price">
                    <p>{{ config('formal.currency') }} {{ number_format($product['unitPrice']) }}</p>
                    @isset($product['tax'])
                    <p>{{ config('formal.currency') }} {{ number_format($product['tax']) }} <small>({{ config('formal.tax') }})</small></p>
                    @endisset
                </td>
                <td class="total">{{ config('formal.currency') }} {{ number_format($product['total']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <footer class="row row-end">
        <div class="total static-width">
            <ul class="row mb-0">
                <li class="row-col p-1 title">ARA TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency') }} {{ number_format($data->subtotal) }}</li>
            </ul>
            @isset ($data->discount)
            <ul class="row mb-0">
                <li class="row-col p-1 title">İNDİRİM</li>
                <li class="row-col p-1">(10%) {{ config('formal.currency') }} {{ number_format($data->discount) }}</li>
            </ul>
            @endisset
            <ul class="row mb-0">
                <li class="row-col title">TOPLAM {{ config('formal.tax') }}</li>
                <li class="row-col">(18%) {{ config('formal.currency') }} {{ number_format($data->totalTax) }}</li>
            </ul>
            <ul class="row mb-0">
                <li class="row-col p-1 title">GENEL TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency') }} {{ number_format($data->total) }}</li>
            </ul>
        </div>
    </footer>
    @isset($data->notes)
        @foreach ($data->notes as $key => $row)
        <div class="self-area">
            @isset($row['title'])
            <div class="title">{{ $row['title'] }}</div>
            @endisset
            <div class="body">
                {{ $row['note'] }}
            </div>
        </div>
        @endforeach
    @endisset
</div>

</body>
</html>
