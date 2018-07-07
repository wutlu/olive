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
        $('.invoice-month'               ).html(obj.result.month)
        $('.invoice-total_price'         ).html(obj.result.total_price)
        $('.invoice-tax'                 ).html(obj.result.tax)
        $('.invoice-total_price_with_tax').html(obj.result.total_price_with_tax)

        if (obj.result.discount)
        {
            $('.invoice-discount'     ).html(obj.result.discount.amount)
            $('.invoice-discount_rate').html(obj.result.discount.rate)
            $('tr.discount-row'       ).removeClass('d-none')
        }
        else
        {
            $('tr.discount-row'       ).addClass('d-none')
        }

        $('#pay-button').removeAttr('disabled')
    }
}

$(document).on('click', '#pay-button', function() {
    var mdl = modal({
            'id': 'next',
            'title': 'Sipariş Onayı',
            'body': [
                $('<p />', {
                    'html': 'Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderilecek.'
                }),
                $('<p />', {
                    'html': 'Organizasyonunuz ödeme işlemi gerçekleştikten sonra aktif edilecektir.'
                })
            ],
            'size': 'modal-medium'
        });

        mdl.find('.modal-footer')
           .html([
               $('<a />', {
                   'href': '#',
                   'class': 'modal-close waves-effect btn-flat',
                   'html': 'Vazgeç'
               }),
               $('<span />', { 'html': ' ' }),
               $('<a />', {
                   'href': '{{ route('organisation.create', [ 'step' => 3, 'plan' => session('plan') ]) }}',
                   'class': 'waves-effect btn',
                   'html': 'Onayla'
               })
           ])
})

$(document).ready(function() {
    $('textarea').characterCounter()
})

@endpush

@section('content')
<div class="step-title">
    <span class="step">2</span>
    <span class="text">Ödeme Detayı</span>
</div>

<form autocomplete="off" id="calculate-form" method="post" action="{{ route('organisation.create.calculate') }}" class="json" data-callback="__calculate">
    <div class="row">
        <div class="col s12 m6">
            <div class="card card-unstyled">
                <div class="card-content">
                    <h3 class="center-align">₺ {{ $plan['price'] }}<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="center-align grey-text">{{ $plan['name'] }}</p>
                    <input name="plan" type="hidden" value="{{ session('plan') }}" />

                    <div class="row">
                        <div class="input-field col s12">
                            <select name="month" id="month">
                                <option value="1" selected>1 Ay</option>
                                @for ($i = 2; $i <= 24; $i++)
                                <option value="{{ $i }}">{{ $i }} Ay</option>
                                @endfor
                            </select>
                            <label for="month">Plan Süresi</label>
                            <span class="helper-text">12 aylık ödeme seçeneğinde varsa indirim kuponunuza ek {{ config('app.discount_with_year') }}% indirim uygulanır.</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="coupon" name="coupon" type="text" class="validate" />
                            <label for="coupon">İndirim Kuponu</label>
                            <span class="helper-text">Varsa indirim kuponunuz.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12 m6">
            <div class="card card-unstyled">
                <div class="card-content">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input id="name" name="name" type="text" class="validate" />
                            <label for="name">Ad</label>
                            <span class="helper-text">Fatura için Ad.</span>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="lastname" name="lastname" type="text" class="validate" />
                            <label for="lastname">Soyad</label>
                            <span class="helper-text">Fatura için Soyad.</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="address" name="address" class="materialize-textarea validate" data-length="255"></textarea>
                            <label for="address">Adres</label>
                            <span class="helper-text">Fatura için adres.</span>
                        </div>
                        <div class="input-field col s12">
                            <textarea id="notes" name="notes" class="materialize-textarea validate" data-length="255"></textarea>
                            <label for="notes">Not</label>
                            <span class="helper-text">Varsa notunuz.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="card card-unstyled">
                <div class="card-content center-align">
                    <button type="submit" class="btn teal waves-effect">Uygula</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <span class="card-title">Ödeme Detayı</span>
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
                    <tr class="discount-row d-none">
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
                            <span class="invoice-tax">0</span>
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
        </div>
        <div class="card-content">
            <div class="center-align">
                <a disabled href="#" class="btn-flat btn-large waves-effect" id="pay-button">Siparişi Tamamla</a>
            </div>
        </div>
    </div>
</form>
@endsection
