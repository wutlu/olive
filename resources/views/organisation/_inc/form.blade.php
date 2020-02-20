<div class="center-align hide p-1" data-id="spinner">
    <div class="preloader-wrapper big active">
        <div class="spinner-layer spinner-red-only">
            <div class="circle-clipper left">
                <div class="circle"></div>
            </div>
            <div class="gap-patch">
                <div class="circle"></div>
            </div>
            <div class="circle-clipper right">
                <div class="circle"></div>
            </div>
        </div>
    </div>
</div>
<div class="parent-form">
    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title">Uzatın</span>
        </div>
        <div class="card-content white">
            <table class="invoice">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Değer</th>
                        <th style="width: 100px;" class="right-align">Birim</th>
                        <th style="width: 100px;" class="right-align">Miktar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1 Aylık 8vz Aboneliği</td>
                        <td data-name="unit-price">{{ $user->organisation->unit_price }}</td>
                        <td class="right-align">{{ config('formal.currency') }}</td>
                        <td class="right-align">
                            <input type="number" max="48" min="3" name="month" value="3" style="width: 48px;" />
                        </td>
                    </tr>
                    <tr class="hide" data-name="discount-area">
                        <td>İndirim</td>
                        <td>
                            <span data-name="discount-rate">0</span>%
                        </td>
                        <td class="right-align">{{ config('formal.currency') }}</td>
                        <td class="right-align" data-name="total-discount">-</td>
                    </tr>
                    <tr>
                        <td>{{ config('formal.tax_name') }}</td>
                        <td>{{ config('formal.tax') }}%</td>
                        <td class="right-align">{{ config('formal.currency') }}</td>
                        <td class="right-align" data-name="total-tax">-</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Genel Toplam</th>
                        <th></th>
                        <th class="right-align">{{ config('formal.currency') }}</th>
                        <th class="right-align" data-name="total-price">-</th>
                    </tr>
                </tfoot>
            </table>

            @push('local.scripts')
                var discountTimer;
                var discount_area = $('[data-name=discount-area]');

                function __calculate()
                {
                    var unit_price = $('[data-name=unit-price]').html();
                    var month = $('input[name=month]').val();

                    var total_price = unit_price*month;

                    if (month >= 12)
                    {
                        var discount = (total_price / 100) * {{ $discount_with_year }};
                            total_price = total_price - discount;

                        $('[data-name=discount-rate]').html('{{ $discount_with_year }}')
                        $('[data-name=total-discount]').html('-' + price_format(discount))

                        if (discount_area.hasClass('hide'))
                        {
                            discount_area.removeClass('hide')
                        }
                    }
                    else
                    {
                        $('[data-name=discount-rate]').html(0)
                        $('[data-name=total-discount]').html('-')

                        if (!discount_area.hasClass('hide'))
                        {
                            discount_area.addClass('hide')
                        }
                    }

                    var tax = total_price/100*{{ config('formal.tax') }};

                    $('[data-name=total-tax]').html(price_format(tax))

                    total_price = total_price + tax;

                    $('[data-name=total-price]').html(price_format(total_price))
                }

                $(document).on('change', 'input[name=month]', __calculate)

                __calculate()
            @endpush
        </div>
    </div>

    @push('local.scripts')
        $('input[name=type]').change(function() {
            var __ = $(this),
                create_form = $('form#billing-form');
                create_form.find('.dynamic-field').addClass('hide')

            if (__.val() == 'corporate')
            {
                create_form.find('.dynamic-field.corporate').removeClass('hide')
            }
            else if (__.val() == 'person')
            {
                create_form.find('.dynamic-field.person').removeClass('hide')
            }
            else if (__.val() == 'individual')
            {
                create_form.find('.dynamic-field.individual').removeClass('hide')
            }
        })

        $(window).on('load', function() {
            $('.dynamic-field.individual').removeClass('hide')
        })
    @endpush

    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title">Fatura Bilgisi</span>
        </div>
        <div class="card-content">
            <form
                autocomplete="off"
                id="billing-form"
                method="patch"
                action="{{ route('organisation.update') }}"
                class="json"
                data-callback="__update"
                data-include="month">
                <div class="row">
                    <div class="col s12 m4 offset-m1">
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
                        <div class="input-field dynamic-field hide individual person">
                            <input name="person_name" id="person_name" type="text" class="validate" />
                            <label for="person_name">Ad</label>
                            <span class="helper-text"></span>
                        </div>
                        <div class="input-field dynamic-field hide individual person">
                            <input name="person_lastname" id="person_lastname" type="text" class="validate" />
                            <label for="person_lastname">Soyad</label>
                            <span class="helper-text"></span>
                        </div>
                        <div class="input-field dynamic-field hide individual person">
                            <input name="person_tckn" id="person_tckn" type="text" class="validate" />
                            <label for="person_tckn">T.C. Kimlik No</label>
                            <span class="helper-text"></span>
                        </div>
                        <div class="input-field dynamic-field hide corporate person">
                            <input name="merchant_name" id="merchant_name" type="text" class="validate" />
                            <label for="merchant_name">Ticari Ünvan</label>
                            <span class="helper-text"></span>
                        </div>
                        <div class="input-field dynamic-field corporate individual person">
                            <input name="phone" id="phone" type="text" class="validate" />
                            <label for="phone">Telefon</label>
                            <span class="helper-text"></span>
                        </div>
                        <div class="input-field dynamic-field hide corporate">
                            <input name="tax_number" id="tax_number" type="text" class="validate" />
                            <label for="tax_number">Vergi No</label>
                            <span class="helper-text"></span>
                        </div>
                        <div class="input-field dynamic-field hide corporate person">
                            <input name="tax_office" id="tax_office" type="text" class="validate" />
                            <label for="tax_office">Vergi Dairesi</label>
                            <span class="helper-text"></span>
                        </div>
                    </div>
                    <div class="col s12 m4 offset-m1">
                        <div
                            class="input-field mb-2 load"
                            data-href="{{ route('geo.countries') }}"
                            data-callback="__countries"
                            data-method="post">
                            <select
                                name="country_id"
                                id="country_id"
                                class="json load"
                                data-load-delay="1000"
                                data-href="{{ route('geo.states') }}"
                                data-include="country_id"
                                data-method="post"
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
                            <span class="helper-text"></span>
                        </div>

                        @push('local.scripts')
                            $(document).ready(function() {
                                $('textarea#address').characterCounter()
                            })
                        @endpush
                    </div>
                </div>

                <ul class="collection collection-unstyled mb-2">
                    <li class="collection-item">
                        <label>
                            <input type="checkbox" name="tos" id="tos" />
                            <span>
                                <a target="_blank" href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a>,
                                <a target="_blank" href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a> ve
                                <a target="_blank" href="{{ route('page.view', 'satis-sozlesmesi') }}">Satış Sözleşmesi</a>'ni okudum, anladım ve kabul ediyorum.
                            </span>
                        </label>
                    </li>
                </ul>

                <button type="submit" class="btn teal waves-effect">Fatura Oluştur</button>
            </form>
        </div>
    </div>
</div>

@push('external.include.footer')
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
    <script>
        $('input#postal_code').mask('99999')
        $('input#person_tckn').mask('99999999999')
        $('input#tax_number').mask('9999999999')
        $('input#phone').mask('(999) 999 99 99')
    </script>
@endpush

@push('local.scripts')
    function __update(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.organisation == 'have')
            {
                window.location.href = '{{ route('settings.organisation') }}';
            }
            else
            {
                M.toast({
                    html: 'Fatura oluşturuluyor...',
                    classes: 'green darken-2'
                })

                $('#tab-2').children('.parent-form').addClass('hide')
                $('#tab-2').children('[data-id=spinner]').removeClass('hide')

                location.href = '{{ route('settings.organisation') }}#tab-2';
                location.reload()
            }
        }
    }
@endpush
