@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Organizasyon Oluştur'
        ],
        [
            'text' => 'Plan Detayı'
        ]
    ]
])

@push('local.scripts')
    $('select').formSelect()

    function __calculate(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('.invoice-month').html(obj.result.month)
            $('.invoice-total_price').html(obj.result.total_price)
            $('.invoice-tax').html(obj.result.amount_of_tax)
            $('.invoice-total_price_with_tax').html(obj.result.total_price_with_tax)

            if (obj.result.discount)
            {
                $('.invoice-discount').html(obj.result.discount.amount)
                $('.invoice-discount_rate').html(obj.result.discount.rate)

                if (obj.result.discount.price)
                {
                    $('.invoice-discount_price').html('+ {{ config('formal.currency') }} ' + obj.result.discount.price)
                }

                $('tr.discount-row').removeClass('hide')
            }
            else
            {
                $('tr.discount-row').addClass('hide')
            }

            scrollTo({
                'target': '#payment-details',
                'tolerance': '-92px'
            })
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.organisation == 'have')
            {
                window.location.href = '{{ route('settings.organisation') }}';

                return false;
            }

            if (obj.created)
            {
                window.location.href = '{{ route('organisation.create.result') }}';
            }
        }
    }
@endpush

@section('content')
    <div class="step-title">
        <span class="step">2</span>
        <span class="text">Plan Detayı</span>
    </div>

    <div class="row">
        <div class="col s12 xl8 offset-xl2">
            <form autocomplete="off" id="calculate-form" method="post" action="{{ route('organisation.create.calculate') }}" class="json" data-callback="__calculate">
                <div class="card card-unstyled">
                    <div class="card-content">
                        <p class="center-align">{{ $plan['name'] }}</p>

                        <h3 class="center-align">
                            {{ config('formal.currency') }}
                            {{ $plan['price'] }}
                            <sup>.00</sup>
                            <sub><small>/ Ay</small></sub>
                        </h3>

                        <input name="plan_id" type="hidden" value="{{ $id }}" />

                        <div class="row">
                            <div class="input-field col s12">
                                <select name="month" id="month">
                                    <option value="3" selected>3 Ay</option>
                                    @for ($i = 4; $i <= 24; $i++)
                                    <option value="{{ $i }}">{{ $i }} Ay</option>
                                    @endfor
                                </select>
                                <label for="month">Plan Süresi</label>
                                <span class="helper-text">12 aylık ödeme seçeneğinde, varsa indirim kuponunuza ek {{ config('formal.discount_with_year') }}% indirim uygulanır.</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="coupon_code" name="coupon_code" type="text" class="validate" />
                                <label for="coupon_code">İndirim Kuponu</label>
                                <span class="helper-text">Varsa indirim kuponu.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-unstyled">
                    <div class="card-content center-align">
                        <button type="submit" class="btn teal waves-effect">Uygula</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card" id="payment-details">
        <div class="card-content">
            <span class="card-title">Fatura Önizlemesi</span>
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
                        <td>{{ $plan['name'] }}</td>
                        <td>
                            <span class="invoice-month">-</span> Ay <small>(Vergi Hariç)</small>
                        </td>
                        <td class="right-align">{{ config('formal.currency') }}</td>
                        <td class="right-align">
                            <span class="invoice-total_price">-</span>
                        </td>
                    </tr>
                    <tr class="discount-row hide">
                        <td>İndirim</td>
                        <td>
                            <span class="invoice-discount_rate">0</span>%
                            <span class="invoice-discount_price"></span>
                        </td>
                        <td class="right-align">{{ config('formal.currency') }}</td>
                        <td class="right-align">
                            <span class="invoice-discount">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Vergiler</td>
                        <td>{{ config('formal.tax') }}%</td>
                        <td class="right-align">{{ config('formal.currency') }}</td>
                        <td class="right-align">
                            <span class="invoice-tax">0</span>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Genel Toplam</th>
                        <th></th>
                        <th class="right-align">{{ config('formal.currency') }}</th>
                        <th class="right-align">
                            <span class="invoice-total_price_with_tax">-</span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="step-title">
        <span class="step">3</span>
        <span class="text">Fatura Bilgileri</span>
    </div>

    @include('organisation._inc.billing_form', [
        'method' => 'put',
        'route' => route('organisation.create'),
        'callback' => '__create',
        'include' => 'plan_id,month,coupon_code'
    ])
@endsection
