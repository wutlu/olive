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
@endpush

@push('external.include.footer')
<script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('app.version')) }}"></script>
<script>
$(document).ready(function() {
    $('input[name=number]').mask('9999 9999 9999 9999');
    $('input[name=ccv]').mask('999');
})
</script>
@endpush

@section('content')
    <div class="step-title">
        <span class="step">3</span>
        <span class="text">Ödeme İşemi</span>
    </div>

    <div class="card">
        <div class="card-content lime lighten-4">
            <span class="card-title">Fatura Detayı</span>
            <table class="highlight invoice">
                <thead>
                    <tr>
                        <th></th>
                        <th>Değer</th>
                        <th style="width: 100px;" class="right-align">Birim</th>
                        <th style="width: 100px;" class="right-align">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $invoice_session->plan->name }}</td>
                        <td>
                            <span class="invoice-month">{{ $invoice_session->month }}</span> Ay <small>(Vergi Hariç)</small>
                        </td>
                        <td class="right-align">₺</td>
                        <td class="right-align">
                            <span class="invoice-total_price">{{ $invoice_session->total_price }}</span>
                        </td>
                    </tr>
                    @if (@$invoice_session->discount)
                    <tr>
                        <td>İndirim</td>
                        <td>
                            <span class="invoice-discount_rate">{{ $invoice_session->discount->rate }}</span>%
                        </td>
                        <td class="right-align">₺</td>
                        <td class="right-align">
                            <span class="invoice-discount">{{ $invoice_session->discount->amount }}</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>Vergiler</td>
                        <td>{{ config('app.tax') }}%</td>
                        <td class="right-align">₺</td>
                        <td class="right-align">
                            <span class="invoice-total_tax">{{ $invoice_session->tax }}</span>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Genel Toplam</th>
                        <th></th>
                        <th class="right-align">₺</th>
                        <th class="right-align">
                            <span class="invoice-total_price_with_tax">{{ $invoice_session->total_price_with_tax }}</span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title">Kart Bilgileriniz</span>
            <div class="row">
                <div class="col s12 m6">
                    <div class="row">
                        <div class="input-field col s12 m8">
                            <input
                                data-focus-class=".credit-card->children(.card-number)"
                                data-focus-class-add="active"
                                data-blur-class=".credit-card->children(.card-number)"
                                data-blur-class-remove="active"
                                data-input-to=".credit-card->children(.card-number)"
                                id="number"
                                name="number"
                                type="text"
                                class="validate" />
                            <label for="number">Kart Numarası</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12 m8">
                            <input
                                data-focus-class=".credit-card->children(.card-name)"
                                data-focus-class-add="active"
                                data-blur-class=".credit-card->children(.card-name)"
                                data-blur-class-remove="active"
                                data-input-to=".credit-card->children(.card-name)"
                                id="name"
                                name="name"
                                type="text"
                                maxlength="50"
                                class="validate" />
                            <label for="name">Kart Üzerindeki İsim</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s6 m4">
                            <select data-input-to=".credit-card->children(.card-month)" id="month" name="month">
                                <option value="" disabled selected>Ay</option>
                                @foreach (__('global.date.months') as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                                @endforeach
                            </select>
                            <label for="month">Ay</label>
                        </div>
                        <div class="input-field col s6 m4">
                            <select data-input-to=".credit-card->children(.card-year)" id="year" name="year">
                                <option value="" disabled selected>Yıl</option>
                                @for ($i = date('y'); $i <= date('y')+10; $i++)
                                <option value="{{ $i }}">20{{ $i }}</option>
                                @endfor
                            </select>
                            <label for="year">Yıl</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s8 m8">
                            <input
                                data-focus-class=".credit-card"
                                data-focus-class-add="card-flip"
                                data-blur-class=".credit-card"
                                data-blur-class-remove="card-flip"
                                data-input-to=".credit-card->children(.card-ccv)"
                                id="ccv"
                                name="ccv"
                                type="text"
                                class="validate"
                                maxlength="3" />
                            <label for="ccv">CCV Güvenlik Kodu</label>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6">
                    <div class="credit-card">
                        <div class="card-item card-front card-chip"></div>
                        <div class="card-item card-front card-name"></div>
                        <div class="card-item card-front card-number"></div>
                        <div class="card-item card-front card-month"></div>
                        <div class="card-item card-front card-year"></div>
                        <div class="card-item card-back card-ccv"></div>
                    </div>
                </div>
            </div>

            <div class="center-align">
                <a href="{{ route('organisation.create', [ 'step' => 4, 'plan' => session('plan') ]) }}" class="btn-flat btn-large waves-effect">Ödemeyi Gerçekleştir</a>
            </div>
        </div>
    </div>
@endsection
