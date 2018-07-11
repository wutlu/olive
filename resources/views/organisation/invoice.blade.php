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
        @isset($formal_paid)
        <div class="row-col seal">
            <img id="seal" alt="veri.zone-logo" src="{{ asset('img/maliye-damga.svg') }}" />
        </div>
        @endisset
        <div class="row-col invoice static-width">
            <h1>FATURA</h1>
            <ul class="row dashed-line dashed-bottom">
                <li class="row-col p-1 title">NO</li>
                <li class="row-col p-1">#{{ $invoice->invoice_id }}</li>
            </ul>
            @isset($formal_paid)
            <ul class="row dashed-line dashed-bottom">
                <li class="row-col p-1 title">SERİ</li>
                <li class="row-col p-1">{{ $formal_paid->serial }}</li>

                <li class="row-col p-1 title">SIRA</li>
                <li class="row-col p-1">{{ $formal_paid->no }}</li>
            </ul>
            @endisset
            <div class="self-area dashed-line dashed-bottom">
                <div class="body">
                    <ul>
                        <li class="title">{{ $billing_information->type == 'person' ? 'Gerçek (Şahıs Şirketi)' : $billing_information->type == 'individual' ? 'Bireysel' : 'Tüzel' }}</li>
                        @if ($billing_information->type == 'person' || $billing_information->type == 'individual')
                            <li class="title">{{ $billing_information->person_name.' '.$billing_information->person_lastname }}</li>
                        @endif
                        @if ($billing_information->type == 'person')
                        <li>{{ $billing_information->person_tckn }}</li>
                        @endif
                        @if ($billing_information->type == 'person' || $billing_information->type == 'corporate')
                            <li>{{ $billing_information->merchant_name }}</li>
                        @endif
                        @if ($billing_information->type == 'corporate')
                            <li>{{ $billing_information->tax_number }}</li>
                        @endif
                        @if ($billing_information->type == 'person')
                            <li>{{ $billing_information->tax_office }}</li>
                        @endif
                    </ul>
                    <ul class="mb-0">
                        <li class="title">Adres</li>
                        <li>{{ $billing_information->address }}</li>
                        <li>{{ $billing_information->postal_code }}, {{ $billing_information->city }}</li>
                        <li>{{ $billing_information->state }}, {{ $billing_information->country }}</li>
                    </ul>
                </div>
            </div>
            <ul class="row mb-0">
                <li class="row-col p-1 title">SİPARİŞ TARİHİ</li>
                <li class="row-col p-1">{{ date('d.m.Y', strtotime($invoice->created_at)) }}</li>
            </ul>
            @isset($formal_paid)
            <ul class="row mb-0 green-text">
                <li class="row-col title">ÖDENDİĞİ TARİH</li>
                <li class="row-col">{{ $formal_paid->date }}</li>
            </ul>
            @endisset
            <ul class="row red-text dashed-line dashed-bottom">
                <li class="row-col p-1 title">SON ÖDEME TARİHİ</li>
                <li class="row-col p-1">{{ date('d.m.Y', strtotime('+30 days', strtotime($invoice->created_at))) }}</li>
            </ul>
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
            @isset ($discount)
            <ul class="row mb-0">
                <li class="row-col p-1 title">İNDİRİM</li>
                <li class="row-col p-1">({{ @$discount->rate_extra ? ($discount->rate_extra + $discount->rate) : $discount->rate }}%) {{ config('formal.currency').' '.number_format($discount->amount) }}</li>
            </ul>
            @endisset
            <ul class="row mb-0">
                <li class="row-col title">TOPLAM {{ config('formal.tax_name') }}</li>
                <li class="row-col">(18%) {{ config('formal.currency').' '.number_format($invoice->amount_of_tax) }}</li>
            </ul>
            <ul class="row mb-0">
                <li class="row-col p-1 title">GENEL TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency') }} {{ number_format(($discount ? ($invoice->total_price - $discount->amount) : $invoice->total_price) + $invoice->amount_of_tax) }}</li>
            </ul>
        </div>
    </footer>
    <div class="self-area">
        @if ($discount)
        <div class="title">Bilgi</div>
        <div class="body">{{ $discount->coupon_key }} kupon kodu ile {{ $discount->rate }}% oranında bir indirim kullandınız.</div>
            @isset($discount->rate_extra)
            <div class="body">{{ $invoice->month }} aylık ödemeniz için extra {{ config('formal.discount_with_year') }}% oranında bir indirim ekledik.</div>
            @endisset
        @endif
    </div>
    <div class="self-area">
        <div class="title">Hesap Bilgisi</div>
        <div class="body">Ödemenizi; fatura numarası açıklamada olacak şekilde aşağıdaki hesap numaralarından herhangi birine yapabilirsiniz.</div>
        <div class="body">Daha sonra Ayarlar/Ödemeler sayfasından ödeme bildirimi yapmayı unutmayın.</div>
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
