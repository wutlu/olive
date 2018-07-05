@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@push('local.scripts')

$('select').formSelect();

function __calculate(__, obj)
{
    if (obj.status == 'ok')
    {
        var month = $('.invoice-month');
        var total_price = $('.invoice-total_price');
        var tax = $('.invoice-tax');
        var total_price_with_tax = $('.invoice-total_price_with_tax');

        month.html(obj.result.month)

        total_price.html(obj.result.total_price)
        tax.html(obj.result.tax)
        total_price_with_tax.html(obj.result.total_price_with_tax)

        if (obj.result.discount)
        {
            var discount_rate = $('.invoice-discount_rate');
            var discount = $('.invoice-discount');

            discount_rate.html(obj.result.discount.rate)
            discount.html(obj.result.discount.amount)
        }

        $('#pay-button').removeAttr('disabled')
    }
}

@endpush

@section('content')
<div class="step-title">
    <span class="step">2</span>
    <span class="text">Detaylar</span>
</div>

<div class="card card-unstyled">
    <div class="card-content">
        <h3 class="center-align">₺ {{ $plan['price'] }}<sup>.00</sup> <sub>/ Ay</sub></h3>
        <p class="center-align grey-text">{{ $plan['name'] }}</p>

        <form id="calculate-form" method="post" action="{{ route('organisation.create.calculate') }}" class="json" data-callback="__calculate">
            <input name="plan" type="hidden" value="{{ session('plan') }}" />

            <div class="row">
                <div class="input-field col s12 m4 offset-m4">
                    <select name="month" id="month">
                        <option value="1" selected>1 Ay</option>
                        @for ($i = 2; $i <= 48; $i++)
                        <option value="{{ $i }}">{{ $i }} Ay</option>
                        @endfor
                    </select>
                    <label for="month">Plan Süresi</label>
                    <span class="helper-text">12 aylık ödeme seçeneğinde varsa indirim kuponunuza ek {{ config('app.discount_with_year') }}% indirim uygulanır.</span>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m4 offset-m4">
                    <input id="coupon" name="coupon" type="text" class="validate" />
                    <label for="coupon">İndirim Kuponu</label>
                    <span class="helper-text">Varsa indirim kuponunuz.</span>
                </div>
                <div class="col s12 m4 offset-m4">
                    <button type="submit" class="btn teal waves-effect">Uygula</button>
                </div>
            </div>
        </form>
        <table class="highlight invoice">
            <thead>
                <tr>
                    <th></th>
                    <th>Değer</th>
                    <th style="width: 100px;" class="right-align">Birim</th>
                    <th style="width: 100px;" class="right-align">Miktar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ config('plans')[session('plan')]['name'] }}</td>
                    <td>
                        <span class="invoice-month">-</span> Ay <small>(Vergi Hariç)</small>
                    </td>
                    <td class="right-align">₺</td>
                    <td class="right-align">
                        <span class="invoice-total_price">-</span>
                    </td>
                </tr>
                <tr>
                    <td>İndirim</td>
                    <td>
                        <span class="invoice-discount_rate">0</span>%
                    </td>
                    <td class="right-align">₺</td>
                    <td class="right-align">
                        <span class="invoice-discount">0</span>
                    </td>
                </tr>
                <tr>
                    <td>Vergiler</td>
                    <td>{{ config('app.tax') }}%</td>
                    <td class="right-align">₺</td>
                    <td class="right-align">
                        <span class="invoice-tax">-</span>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th>Genel Toplam</th>
                    <th></th>
                    <th class="right-align">₺</th>
                    <th class="right-align">
                        <span class="invoice-total_price_with_tax">-</span>
                    </th>
                </tr>
            </tfoot>
        </table>

        <div class="center-align">
            <a disabled href="{{ route('organisation.create', [ 'step' => 3, 'plan' => session('plan') ]) }}" class="btn-flat btn-large waves-effect" id="pay-button">Ödeme İşlemi</a>
        </div>
    </div>
</div>
@endsection
