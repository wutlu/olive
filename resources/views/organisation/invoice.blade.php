<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- charset -->
    <meta charset="utf-8" />

    <!-- title -->
    <title>FATURA #{{ $invoice->invoice_id }}</title>

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
    .grey-text {
        color: #999;
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
        @if ($invoice->paid_at)
        <div class="row-col seal">
            <img id="seal" alt="veri.zone-logo" src="{{ asset('img/maliye-damga.svg') }}" />
        </div>
        @endif
        <div class="row-col invoice static-width">
            <h1>FATURA</h1>
            <ul class="row dashed-line dashed-bottom">
                <li class="row-col p-1 title">FATURA</li>
                <li class="row-col p-1">#{{ $invoice->invoice_id }}</li>
            </ul>
            @if ($invoice->paid_at)
            <ul class="row dashed-line dashed-bottom">
                <li class="row-col p-1 title">SERİ</li>
                <li class="row-col p-1">{{ $invoice->serial }}</li>

                <li class="row-col p-1 title">NO</li>
                <li class="row-col p-1">{{ $invoice->no }}</li>
            </ul>
            @endif
            <div class="self-area dashed-line dashed-bottom">
                <div class="body">
                    <ul>
                        <li class="title">{{ $invoice->info->type == 'person' ? 'Gerçek (Şahıs Şirketi)' : $invoice->info->type == 'individual' ? 'Bireysel' : 'Tüzel' }}</li>
                        @if ($invoice->info->type == 'person' || $invoice->info->type == 'individual')
                            <li class="title">{{ $invoice->info->person_name.' '.$invoice->info->person_lastname }}</li>
                        @endif
                        @if ($invoice->info->type == 'person')
                        <li>{{ $invoice->info->person_tckn }}</li>
                        @endif
                        @if ($invoice->info->type == 'person' || $invoice->info->type == 'corporate')
                            <li>{{ $invoice->info->merchant_name }}</li>
                        @endif
                        @if ($invoice->info->type == 'corporate')
                            <li>{{ $invoice->info->tax_number }}</li>
                        @endif
                        @if ($invoice->info->type == 'person')
                            <li>{{ $invoice->info->tax_office }}</li>
                        @endif
                    </ul>
                    <ul class="mb-0">
                        <li class="title">Adres</li>
                        <li>{{ $invoice->info->address }}</li>
                        <li>{{ $invoice->info->postal_code }}, {{ $invoice->info->city }}</li>
                        <li>{{ $invoice->info->state->name }}, {{ $invoice->info->country->name }}</li>
                    </ul>
                </div>
            </div>
            <ul class="row mb-0">
                <li class="row-col p-1 title">SİPARİŞ TARİHİ</li>
                <li class="row-col p-1">{{ date('d.m.Y', strtotime($invoice->created_at)) }}</li>
            </ul>
            @if ($invoice->paid_at)
            <ul class="row mb-0 green-text">
                <li class="row-col title">ÖDENDİĞİ TARİH</li>
                <li class="row-col">{{ date('d.m.Y', strtotime($invoice->paid_at)) }}</li>
            </ul>
            @else
            <ul class="row red-text dashed-line dashed-bottom">
                <li class="row-col p-1 title">SON ÖDEME TARİHİ</li>
                <li class="row-col p-1">{{ date('d.m.Y', strtotime('+30 days', strtotime($invoice->created_at))) }}</li>
            </ul>
            @endif
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
            <tr>
                <td class="description">
                    <p>{{ $plan->name }} ({{ $plan->properties->capacity->value }} kullanıcı)</p>
                </td>
                <td class="quantity">{{ $invoice->month }} Ay</td>
                <td class="unit-price">
                    <p>{{ config('formal.currency').' '.number_format($invoice->unit_price) }}</p>
                </td>
                <td class="total">{{ config('formal.currency').' '.number_format($invoice->total_price) }}</td>
            </tr>
        </tbody>
    </table>
    <footer class="row row-end">
        <div class="total static-width">
            <ul class="row mb-0">
                <li class="row-col p-1 title">ARA TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency').' '.number_format($invoice->total_price) }}</li>
            </ul>
            @if ($invoice->fee()->discount)
            <ul class="row mb-0">
                <li class="row-col title">İNDİRİM</li>
                <li class="row-col">({{ $invoice->fee()->discount['rate'] }}%) {{ config('formal.currency').' -'.number_format($invoice->fee()->discount['amount']) }}</li>
            </ul>
                @if ($invoice->fee()->discount['price'] > 0)
                <ul class="row mb-0">
                    <li class="row-col title">İNDİRİM</li>
                    <li class="row-col">{{ config('formal.currency').' -'.number_format($invoice->fee()->discount['price']) }}</li>
                </ul>
                @endif
            @endif
            <ul class="row mb-0">
                <li class="row-col title">TOPLAM {{ config('formal.tax_name') }}</li>
                <li class="row-col">({{ $invoice->tax }}%) {{ config('formal.currency').' +'.number_format($invoice->fee()->amount_of_tax) }}</li>
            </ul>
            <ul class="row mb-0">
                <li class="row-col p-1 title">GENEL TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency').' '.number_format($invoice->fee()->total_price) }}</li>
            </ul>
        </div>
    </footer>
    <div class="self-area">
        @if ($invoice->discountCoupon)
        <div class="title">({{ $invoice->discountCoupon->key }}) kodu ile bir indirim kuponu kullandınız.</div>
            @if ($invoice->discountCoupon->rate > 0)
                <div class="body red-text">-{{ $invoice->discountCoupon->rate }}% (kupon)</div>
            @endif
            @if ($invoice->discountCoupon->rate_year > 0)
                <div class="body red-text">-{{ $invoice->discountCoupon->rate_year }}% (12 ay ve üzeri ödeme)</div>
            @endif
            @if ($invoice->discountCoupon->price > 0)
                <div class="body red-text">{{ config('formal.currency').' -'.$invoice->discountCoupon->price }} (extra)</div>
            @endif
        @endif
    </div>
    <div class="self-area">
        <div class="title">Hesap Bilgisi</div>
        <div class="body">Ödemenizi; fatura numarası açıklamada olacak şekilde aşağıdaki hesap numaralarından herhangi birine yapabilirsiniz.</div>
        <div class="body">Daha sonra <a href="{{ route('settings.support', [ 'type' => 'payment-receipt' ]) }}"><strong>Ayarlar/Destek</strong></a> sayfasından ödeme bildirimi yapmanız gerekiyor.</div>
    </div>
    <div class="self-area">
        @foreach(config('formal.banks') as $key => $bank)
        <div class="body">
            <p>{{ $bank['iban'] }}</p>
            <p>{{ $bank['name'] }}</p>
        </div>
        @endforeach
    </div>
</div>

</body>
</html>
