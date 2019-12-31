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
                    <li class="title">T.C. Kimlik N<sup>o</sup></li>
                    <li>{{ config('formal.company')['tradeRegisterNo'] }}</li>
                </ul>
            @endisset
        </div>

        @if ($invoice->paid_at)
            <div class="row-col seal">
                <img id="seal" alt="veri.zone-logo" src="{{ asset('img/maliye-damga.svg') }}" />
                <small style="display: table; margin: 0 auto; max-width: 100px; opacity: .4;">Bu fatura sayfasının resmi bir geçerliliği yoktur. Gerçeğini temsil eder. Gerçek fatura adresinize gönderilmiştir.</small>
            </div>
        @endif

        <div class="row-col invoice static-width">
            <h1>FATURA</h1>

            @if (auth()->check() && auth()->user()->root())
                @if ($invoice->reason_msg)
                    <div class="self-area" style="background-color: #e00; padding: 1rem;">
                        {{ $invoice->reason_msg }}
                    </div>
                @endif
                <div class="self-area" style="background-color: #f0f0f0; padding: 1rem;">
                    <div class="body">
                        <h3>Faturayı Onayla</h3>
                        <form method="post" action="{{ route('admin.organisation.invoice.approve', $invoice->invoice_id) }}">
                            @csrf
                            <div style="margin: 0 0 1rem;">
                                <label for="no" style="display: block;">No</label>
                                <input type="text" name="no" id="no" value="{{ $invoice->no }}" />
                                <small class="red-text" style="display: block;">{{ $errors->first('no') }}</small>
                            </div>
                            <div style="margin: 0 0 1rem;">
                                <label for="serial" style="display: block;">Seri</label>
                                <input type="text" name="serial" id="serial" value="{{ $invoice->serial }}" />
                                <small class="red-text" style="display: block;">{{ $errors->first('serial') }}</small>
                            </div>
                            @if (!$invoice->paid_at)
                            <div style="margin: 0 0 1rem;">
                                <label for="approve" style="display: block;">Onayla</label>
                                <input type="checkbox" name="approve" id="approve" />
                            </div>
                            @endif
                            <button type="submit" style="margin: 0 0 1rem;">Kaydet</button>
                            @if (!$invoice->paid_at)
                            <small class="red-text" style="display: block;">Fatura bir defa onaylandıktan sonra bu onay tekrar kaldırılamaz. Seri ve No alanları daha sonra güncellenebilir.</small>
                            @endif
                        </form>
                    </div>
                </div>
            @endif

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

                        <li>{{ $invoice->info->phone }}</li>

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
                    <p>1 Aylık Veri Zone Aboneliği</p>
                </td>
                <td class="quantity">{{ $invoice->month }}</td>
                <td class="unit-price">
                    <p>{{ config('formal.currency').' '.$invoice->unit_price }}</p>
                </td>
                <td class="total">{{ config('formal.currency').' '.$invoice->total_price }}</td>
            </tr>
        </tbody>
    </table>
    <footer class="row row-end">
        <div class="total static-width">
            <ul class="row mb-0">
                <li class="row-col p-1 title">ARA TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency').' '.$invoice->fee()->total }}</li>
            </ul>
            @if ($invoice->discount_rate)
                <ul class="row mb-0">
                    <li class="row-col title">İndirim</li>
                    <li class="row-col">({{ $invoice->discount_rate }}%) {{ config('formal.currency').' '.$invoice->fee()->discount }}</li>
                </ul>
            @endif
            <ul class="row mb-0">
                <li class="row-col title">{{ config('formal.tax_name') }}</li>
                <li class="row-col">({{ $invoice->tax }}%) {{ config('formal.currency').' '.$invoice->fee()->tax }}</li>
            </ul>
            <ul class="row mb-0">
                <li class="row-col p-1 title">GENEL TOPLAM</li>
                <li class="row-col p-1">{{ config('formal.currency') }} {{ $invoice->fee()->amount }}</li>
            </ul>
        </div>
    </footer>
    @if (!$invoice->paid_at)
        <div class="self-area">
            <div class="title">Hesap Bilgisi</div>
            <div class="body">Havale/EFT durumunda lütfen transfer açıklama kısmında organizasyon numaranızı ({{ $invoice->organisation_id }}) belirtin.</div>
            <div class="body">İşleminizin daha hızlı sonuçlanması için lütfen <a href="{{ route('settings.support', [ 'type' => 'odeme-bildirimi' ]) }}"><strong>Ödeme Bildirimi</strong></a> yapın.</div>
        </div>
        <div class="self-area">
            @foreach(config('formal.banks') as $key => $bank)
                <div class="body">
                    <p>{{ $key }}</p>
                    <p>{{ $bank['iban'] }}</p>
                    <p>{{ $bank['name'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <p style="color: #e00;">Uyarı: Bu fatura bilgilendirme amaçlı/temsilidir. Resmi bir geçerliliği yoktur. Resmi faturanız vermiş olduğunuz adrese gönderilecektir.</p>
</div>

</body>
</html>
