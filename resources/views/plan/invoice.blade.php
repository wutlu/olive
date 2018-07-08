<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- charset -->
    <meta charset="utf-8" />

    <!-- title -->
    <title>FATURA #{{ $oi->invoice_id }}</title>

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
    header.invoice {
        display: -ms-flexbox;
        display: flex;

        -ms-flex-pack: justify;
        justify-content: space-between;

        margin: 0 0 2rem;
    }
    header.invoice > .logo {
        width: 256px;

        -ms-flex-item-align: center;
        align-self: center;
    }
    header.invoice > .logo > img { width: 100%; }
    header.invoice > .title {
        -ms-flex-item-align: center;
        align-self: center;

        text-align: right;
    }
    header.invoice > .title > span {
        font-size: 32px;
        font-weight: bold;
        color: #777;
    }
    .middle {
        display: -ms-flexbox;
        display: flex;

        -ms-flex-pack: justify;
        justify-content: space-between;
    }
    .middle > .seal > img {
        max-width: 128px;
    }
    .middle > .invoice {
        border-width: 4px 0 0;
        border-style: solid;
        border-color: #666;

        padding: 1rem 0;
    }
    .row {
        display: -ms-flexbox;
        display: flex;
        margin: 0 0 1rem;
        width: 300px;

        -ms-flex-pack: justify;
        justify-content: space-between;
    }
    .row > .item + .item {
        margin: 0 0 0 1rem;
    }
    .title {
        display: block;
        font-weight: bold;
    }
    .dashed-sub-line {
        border-width: 0 0 1px;
        border-style: dashed;
        border-color: #999;

        padding: 1rem 0;
    }
    p {
        padding: 0;
        margin: 0 0 1rem;
    }
    table {
        width: 100%;
    }
    table > thead > tr > th {
        text-transform: uppercase;
    }
    table th,
    table td {
        padding: 1rem 0;
    }
    footer.invoice {
        display: -ms-flexbox;
        display: flex;

        -ms-flex-pack: end;
        justify-content: flex-end;
    }
    footer.invoice > .total {
        border-width: 0 0 4px;
        border-style: solid;
        border-color: #666;
    }
    </style>
</head>
<body>

<div class="container">
    <header class="invoice">
        <div class="logo">
            <img alt="veri.zone-logo" src="{{ asset('img/veri.zone-logo.svg') }}" />
        </div>
        <div class="title">
            <span>FATURA</span>
        </div>
    </header>
    <div class="middle">
        <div class="company">
            <p>
                OYT Yazılım Teknolojileri A.Ş.<br />
                Tomtom Mah. Nur-i Ziya Sok. 16/1<br />
                34433 Beyoğlu İstanbul
            </p>
            <p>
                www.parasut.com<br />
                iletisim@parasut.com<br />
                0 212 292 04 94
            </p>
            <p>
                <span class="title">Beyoğlu V.D. N<sup>o</sup></span>
                11111
            </p>
            <p>
                <span class="title">Ticaret Sicil N<sup>o</sup></span>
                11111
            </p>
        </div>
        <div class="seal">
            <img alt="maliye-damga" src="{{ asset('img/maliye-damga.svg') }}" />
        </div>
        <div class="invoice">
            <div class="row dashed-sub-line">
                <div class="item title">SERİ</div>
                <div class="item">
                    <span>14</span>
                </div>
                <div class="item title">SIRA</div>
                <div class="item">
                    <span>12</span>
                </div>
            </div>
            <div class="consumer dashed-sub-line">
                <p class="title">MÜŞTERİ</p>
                <p>
                    OYT Yazılım Teknolojileri A.Ş.<br />
                    Tomtom Mah. Nur-i Ziya Sok. 16/1<br />
                    34433 Beyoğlu İstanbul
                </p>
            </div>
            <div class="row dashed-sub-line">
                <div class="item title">FATURA TARİHİ</div>
                <div class="item">
                    <span>08.07.2018</span>
                </div>
            </div>
        </div>
    </div>
    <table class="dashed-sub-line">
        <thead>
            <tr>
                <th class="dashed-sub-line" style="text-align: left;">Açıklama</th>
                <th class="dashed-sub-line" style="color: #999; text-align: right; padding: 0 1rem 0 0;">Miktar</th>
                <th class="dashed-sub-line" style="color: #999; text-align: left; width: 150px;">Birim Fiyatı</th>
                <th class="dashed-sub-line" style="text-align: right; width: 150px;">Toplam</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left;">Açıklama</td>
                <td style="color: #999; text-align: right; padding: 0 1rem 0 0;">Miktar</td>
                <td style="color: #999; text-align: left; width: 150px;">Birim Fiyatı</td>
                <td style="text-align: right; width: 150px;">Toplam</td>
            </tr>
        </tbody>
    </table>
    <footer class="invoice">
        <div class="total">
            <div class="row dashed-sub-line">
                <div class="item title">ARA TOPLAM</div>
                <div class="item">0</div>
            </div>
            <div class="row dashed-sub-line">
                <div class="item title">TOPLAM K.D.V.</div>
                <div class="item">0</div>
            </div>
            <div class="row dashed-sub-line">
                <div class="item title">GENEL TOPLAM</div>
                <div class="item">0</div>
            </div>
        </div>
    </footer>
</div>

</body>
</html>
