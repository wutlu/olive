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

$('select').formSelect();

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

            $('tr.discount-row').removeClass('d-none')
        }
        else
        {
            $('tr.discount-row').addClass('d-none')
        }

        scrollTo({
            'target': '#payment-details',
            'tolerance': '-92px'
        })
    }
}

$(document).ready(function() {
    $('textarea').characterCounter()
})

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
                    <h3 class="center-align">₺ {{ $plan['price'] }}<sup>.00</sup> <sub>/ Ay</sub></h3>
                    <p class="center-align grey-text">{{ $plan['name'] }}</p>

                    <input name="plan_id" type="hidden" value="{{ $id }}" />

                    <div class="row">
                        <div class="input-field col s12">
                            <select name="month" id="month">
                                <option value="1" selected>1 Ay</option>
                                @for ($i = 2; $i <= 24; $i++)
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
                            <span class="helper-text">Varsa indirim kuponunuz.</span>
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
                    <td>{{ config('formal.tax') }}%</td>
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
</div>

<div class="step-title">
    <span class="step">3</span>
    <span class="text">Fatura Bilgileri</span>
</div>

<div class="card card-unstyled">
    <div class="card-content">
        <div class="row">
            <div class="input-field col s12 m4 l6 xl8">
                <span class="card-title">Fatura Bilgileri</span>
            </div>
            <div class="input-field col s12 m8 l6 xl4">
                <select
                    name="billing-information"
                    id="billing-information"
                    class="json"
                    data-alias="id"
                    data-href="{{ route('billing.information') }}"
                    data-callback="__billling_information">
                    <option value="" disabled selected>Seçin</option>
                    @forelse ($billing_informations as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @empty
                    <option value="" disabled selected>Kayıt Yok</option>
                    @endforelse
                </select>
                <label>Mevcut Kayıt Yükle</label>
                <span class="helper-text">Mevcut kayıtları Ayarlar/Fatura Bilgileri bölümünden güncelleyebilirsiniz.</span>
            </div>
        </div>
        @push('local.scripts')
        function __billling_information(__, obj)
        {
            if (obj.status == 'ok')
            {
                var elements = {
                    "name": {
                        "type":"text"
                    },
                    "type": {
                        "type":"radio"
                    },
                    "name": {
                        "type":"text"
                    },
                    "person_name": {
                        "type":"text"
                    },
                    "person_lastname": {
                        "type":"text"
                    },
                    "person_tckn": {
                        "type":"text"
                    },
                    "merchant_name": {
                        "type":"text"
                    },
                    "tax_number": {
                        "type":"text"
                    },
                    "tax_office": {
                        "type":"text"
                    },
                    "country_id": {
                        "type":"select"
                    },
                    "state_id": {
                        "type":"select",
                        "delay": 1100
                    },
                    "city": {
                        "type":"text"
                    },
                    "address": {
                        "type":"text"
                    },
                    "postal_code": {
                        "type":"text"
                    },
                    "created_at": {
                        "type":"text"
                    },
                    "updated_at": {
                        "type":"text"
                    }
                };

                $.each(obj.data, function(name, val) {
                    var delay = elements[name]['delay'];

                    setTimeout(function() {
                        if (elements[name]['type'] == 'text')
                        {
                            $('form#create-form').find('[name=' + name + ']').val(val);
                        }
                        else if (elements[name]['type'] == 'radio')
                        {
                            $('form#create-form').find('[name=' + name + '][value=' + val + ']').attr('checked', true).trigger('click');
                        }
                        else if (elements[name]['type'] == 'select')
                        {
                            $('form#create-form').find('[name=' + name + ']').val(val).attr('checked', true).trigger('change').formSelect();
                        }
                    }, delay ? delay : 0)
                })

                create_form = $('form#create-form');
                create_form.find('.dynamic-field').addClass('d-none')

                if (obj.data.type == 'corporate')
                {
                    create_form.find('.dynamic-field.corporate').removeClass('d-none')
                }
                else if (obj.data.type == 'person')
                {
                    create_form.find('.dynamic-field.person').removeClass('d-none')
                }
                else if (obj.data.type == 'individual')
                {
                    create_form.find('.dynamic-field.individual').removeClass('d-none')
                }

                M.toast({
                    html: 'Fatura bilgileri yüklendi.',
                    classes: 'blue'
                })
            }
        }

        function __create(__, obj)
        {
            if (obj.status == 'ok')
            {
                if (obj.organisation == 'have')
                {
                    window.location.href = '{{ route('organisation.settings') }}';

                    return false;
                }

                if (obj.created)
                {
                    window.location.href = '{{ route('organisation.create.result') }}';
                }
                else
                {
                    var mdl = modal({
                            'id': 'err',
                            'body': 'Lütfen sayfayı yenileyin ve bir plan süresi belirleyip "Uygula" butonuna basın ve işleminize devam edin. Sorun devam ediyorsa lütfen bizimle iletişime geçin.',
                            'size': 'modal-small',
                            'title': 'Bir şeyler ters gitti :(',
                            'options': { dismissible: false }
                        });

                        mdl.find('.modal-footer')
                           .html([
                               $('<a />', {
                                   'href': '#',
                                   'class': 'modal-close waves-effect btn-flat',
                                   'html': buttons.ok
                               })
                           ])
                }
            }
        }
        @endpush
        <form
            autocomplete="off"
            id="create-form"
            method="post"
            action="{{ route('organisation.create') }}"
            class="json"
            data-callback="__create"
            data-include="plan_id,month,coupon_code">
            <div class="row">
                @push('local.scripts')
                $('input[name=type]').change(function() {
                    var __ = $(this),
                        create_form = $('form#create-form');
                        create_form.find('.dynamic-field').addClass('d-none')

                    if (__.val() == 'corporate')
                    {
                        create_form.find('.dynamic-field.corporate').removeClass('d-none')
                    }
                    else if (__.val() == 'person')
                    {
                        create_form.find('.dynamic-field.person').removeClass('d-none')
                    }
                    else if (__.val() == 'individual')
                    {
                        create_form.find('.dynamic-field.individual').removeClass('d-none')
                    }
                })
                @endpush
                @push('local.scripts')
                $(window).on('load', function() {
                    $('.dynamic-field.individual').removeClass('d-none')
                })
                @endpush
                <div class="col s12 m4 offset-m1">
                    <div class="input-field mb-2">
                        <input name="name" id="name" type="text" class="validate" />
                        <label for="name">Adres Tanımı</label>
                        <span class="helper-text">Bkz: Ev Adresim</span>
                    </div>
                    <p>
                        <label>
                            <input class="with-gap" name="type" value="individual" type="radio" checked />
                            <span>Bireysel</span>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input class="with-gap" name="type" value="person" type="radio" />
                            <span>Gerçek (Şahıs Şirketi)</span>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input class="with-gap" name="type" value="corporate" type="radio" />
                            <span>Tüzel (LTD, AŞ vb.)</span>
                        </label>
                    </p>
                    <br />
                    <div class="input-field dynamic-field d-none individual person">
                        <input name="person_name" id="person_name" type="text" class="validate" />
                        <label for="person_name">Ad</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field dynamic-field d-none individual person">
                        <input name="person_lastname" id="person_lastname" type="text" class="validate" />
                        <label for="person_lastname">Soyad</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field dynamic-field d-none person">
                        <input name="person_tckn" id="person_tckn" type="text" class="validate" />
                        <label for="person_tckn">T.C. Kimlik No</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field dynamic-field d-none corporate person">
                        <input name="merchant_name" id="merchant_name" type="text" class="validate" />
                        <label for="merchant_name">Ticari Ünvan</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field dynamic-field d-none corporate">
                        <input name="tax_number" id="tax_number" type="text" class="validate" />
                        <label for="tax_number">Vergi No</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field dynamic-field d-none corporate person">
                        <input name="tax_office" id="tax_office" type="text" class="validate" />
                        <label for="tax_office">Vergi Dairesi</label>
                        <span class="helper-text"></span>
                    </div>
                </div>
                <div class="col s12 m4 offset-m1">
                    <div class="input-field mb-2 load" data-href="{{ route('geo.countries') }}" data-callback="__countries">
                        <select
                            name="country_id"
                            id="country_id"
                            class="json load"
                            data-load-delay="1000"
                            data-href="{{ route('geo.states') }}"
                            data-include="country_id"
                            data-callback="__states"></select>
                        <label for="country_id">Ülke</label>
                    </div>
                    @push('local.scripts')
                    function __countries(__, obj)
                    {
                        if (obj.status == 'ok')
                        {
                            var select = $('select#country_id');

                            $.each(obj.data, function(key, item) {
                                var option = $('<option />', {
                                    'value': item.id,
                                    'html': item.name
                                });

                                if (item.name == 'Türkiye')
                                {
                                    option.attr('selected', true)
                                }

                                select.append(option)
                            })

                            select.formSelect()
                        }

                        return true;
                    }

                    function __states(__, obj)
                    {
                        if (obj.status == 'ok')
                        {
                            var select = $('select#state_id');

                                select.html('')

                            var option = $('<option />', {
                                    'value': '',
                                    'html': 'Seçin'
                                });

                                select.append(option)

                            $.each(obj.data, function(key, item) {
                                var option = $('<option />', {
                                    'value': item.id,
                                    'html': item.name
                                });

                                select.append(option)
                            })

                            select.formSelect()
                        }

                        return true;
                    }
                    @endpush
                    <div class="input-field mb-2">
                    <select name="state_id" id="state_id" class="validate"></select>
                        <label for="state_id">Şehir</label>
                    </div>
                    <div class="input-field">
                        <input name="city" id="city" type="text" class="validate" />
                        <label for="city">İlçe</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field">
                        <input name="postal_code" id="postal_code" type="text" class="validate" />
                        <label for="postal_code">Posta Kodu</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field">
                        <textarea name="address" id="address" data-length="255" class="materialize-textarea validate"></textarea>
                        <label for="address">Adres</label>
                        <span class="helper-text">Yazışma adresiniz.</span>
                    </div>
                </div>
            </div>

            <div class="card-panel teal">
                <span class="white-text">
                    Bu aşama sonrasında Organizasyonunuz sanal fatura ile birlikte oluşturulur. Ödeme işlemi için gerekli talimatlar sanal faturanızda mevcut olacaktır. Ödeme sonrası sanal faturanız resmi olarak güncellenecektir.
                </span>
            </div>

            <div class="card card-unstyled">
                <div class="card-content">
                    <p>
                        <label>
                            <input type="checkbox" name="protected" id="protected" />
                            <span>Bir sonraki işlemler için bu bilgileri kaydet.</span>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="checkbox" name="tos" id="tos" />
                            <span>
                                <a href="#">Kullanım Koşulları</a>'nı okudum, anladım ve onaylıyorum.
                            </span>
                        </label>
                    </p>
                </div>
                <div class="card-content center-align">
                    <button type="submit" class="btn teal waves-effect">Oluştur</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('external.include.footer')
<script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('app.version')) }}"></script>
<script>
$('input#postal_code').mask('99999')
$('input#person_tckn').mask('99999999999')
$('input#tax_number').mask('9999999999')
</script>
@endpush
