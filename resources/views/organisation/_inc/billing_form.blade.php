<div class="card card-unstyled">
    <div class="card-content">
        <form
            autocomplete="off"
            id="create-form"
            method="{{ $method }}"
            action="{{ $route }}"
            class="json"
            data-callback="{{ $callback }}"
            data-include="{{ @$include }}">
            <div class="row">
                @push('local.scripts')
                    $('input[name=type]').change(function() {
                        var __ = $(this),
                            create_form = $('form#create-form');
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
                    <div class="input-field dynamic-field hide person">
                        <input name="person_tckn" id="person_tckn" type="text" class="validate" />
                        <label for="person_tckn">T.C. Kimlik No</label>
                        <span class="helper-text"></span>
                    </div>
                    <div class="input-field dynamic-field hide corporate person">
                        <input name="merchant_name" id="merchant_name" type="text" class="validate" />
                        <label for="merchant_name">Ticari Ünvan</label>
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
                    @push('local.scripts')
                        $(document).ready(function() {
                            $('textarea#address').characterCounter()
                        })
                    @endpush
                </div>
            </div>

            <div class="card-panel teal white-text">
                <p>Fatura oluşturulduktan sonra ödemenizi gerçekleştirin ve ödeme bildirimi yapın.</p>
                <p>Fatura, girdiğiniz adrese gönderilecektir. Farklı bir adrese gelmesini istiyorsanız, sonraki aşama olan ödeme bildirimi formunda belirtin.</p>
            </div>

            <div class="card card-unstyled">
                <div class="card-content">
                    <p>
                        <label>
                            <input type="checkbox" name="tos" id="tos" />
                            <span>
                                <a href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a> ve <a href="{{ route('page.view', 'gizlilik-haklari') }}">Gizlilik Hakları</a>'nı okudum, anladım ve kabul ediyorum.
                            </span>
                        </label>
                    </p>
                </div>
                <div class="card-content center-align">
                    <button type="submit" class="btn teal waves-effect">Fatura Oluştur</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('external.include.footer')
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
    <script>
    $('input#postal_code').mask('99999')
    $('input#person_tckn').mask('99999999999')
    $('input#tax_number').mask('9999999999')
    </script>
@endpush
