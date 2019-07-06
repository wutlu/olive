@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Organizasyonlar',
            'link' => route('admin.organisation.list')
        ],
        [
            'text' => '🐞 '.$organisation->name
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@push('local.scripts')
    function __account(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Organizasyon Güncellendi',
                classes: 'green darken-2'
            })

            $('#modal-alert').modal('close')
        }
    }
@endpush

@section('content')
    <form method="post" action="{{ route('admin.organisation', $organisation->id) }}" id="details-form" class="json" data-callback="__account">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Organizasyon Bilgileri</span>
            </div>
            <ul class="item-group grey lighten-4 mt-0 mb-0">
                <li class="item p-1 align-self-center">
                    <small class="grey-text">Oluşturuldu</small>
                    <p>{{ date('d.m.Y H:i', strtotime($organisation->created_at)) }}</p>
                </li>
                <li class="item p-1 align-self-center">
                    <small class="grey-text">Güncellendi</small>
                    <p>{{ date('d.m.Y H:i', strtotime($organisation->created_at)) }}</p>
                </li>
                <li class="item p-1 align-self-center">
                    <small class="grey-text">Başlangıç Tarihi</small>
                    <p>{{ date('d.m.Y H:i', strtotime($organisation->start_date)) }}</p>
                </li>
                <li class="item p-1 align-self-center grey lighten-2">
                    <label>
                        <input name="status" id="status" value="on" type="checkbox" {{ $organisation->status ? 'checked' : '' }} />
                        <span>Aktif</span>
                    </label>
                </li>
            </ul>
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="name" id="name" value="{{ $organisation->name }}" type="text" class="validate" />
                            <label for="name">Ad</label>
                            <small class="helper-text">Organizasyon adı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="d-flex">
                            <div class="input-field">
                                <input name="end_date" id="end_date" value="{{ date('Y-m-d', strtotime($organisation->end_date)) }}" type="text" class="validate datepicker" />
                                <label for="end_date">Bitiş Tarihi</label>
                                <small class="helper-text">Organizasyonun bitiş tarihi.</small>
                            </div>
                            <div class="input-field">
                                <input name="end_time" id="end_time" value="{{ date('H:i', strtotime($organisation->end_date)) }}" type="text" class="validate timepicker" />
                                <label for="end_time">Bitiş Saati</label>
                                <small class="helper-text">Organizasyonun bitiş saati.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="item-group">
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Modüller</h6>
                            </div>

                            @foreach (config('system.static_modules') as $key => $module)
                                <label class="collection-item">
                                    <input
                                        data-update
                                        name="{{ $key }}"
                                        id="{{ $key }}"
                                        value="on"
                                        data-unit-price="{{ $prices['unit_price.'.$key]['value'] }}"
                                        type="checkbox"
                                        {{ $organisation->{$key} ? 'checked' : '' }} />
                                    <span>{{ $module }}</span>
                                </label>
                            @endforeach
                        </div>
                    </li>
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Veri Kaynakları</h6>
                            </div>

                            @foreach (config('system.modules') as $key => $module)
                                <label class="collection-item">
                                    <input
                                        data-update
                                        name="data_{{ $key }}"
                                        id="data_{{ $key }}"
                                        value="on"
                                        data-unit-price="{{ $prices['unit_price.data_'.$key]['value'] }}"
                                        type="checkbox"
                                        {{ $organisation->{'data_'.$key} ? 'checked' : '' }} />
                                    <span>{{ $module }}</span>
                                </label>
                            @endforeach
                        </div>
                    </li>
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Limitler</h6>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="user_capacity"
                                    id="user_capacity"
                                    max="12"
                                    min="1"
                                    value="{{ $organisation->user_capacity }}"
                                    type="number"
                                    class="validate" />
                                <small class="helper-text">Kullanıcı Kapasitesi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="real_time_group_limit"
                                    id="real_time_group_limit"
                                    max="12"
                                    min="0"
                                    value="{{ $organisation->real_time_group_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.real_time_group_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">Gerçek Zamanlı Kelime Grubu</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                     name="alarm_limit"
                                     id="alarm_limit"
                                     max="12"
                                     min="0"
                                     value="{{ $organisation->alarm_limit }}"
                                     type="number"
                                     data-unit-price="{{ $prices['unit_price.alarm_limit']['value'] }}"
                                     class="validate" />
                                <small class="helper-text">Alarm</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="pin_group_limit"
                                    id="pin_group_limit"
                                    max="12"
                                    min="0"
                                    value="{{ $organisation->pin_group_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.pin_group_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">Pin Grubu</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="historical_days"
                                    id="historical_days"
                                    max="90"
                                    min="0"
                                    value="{{ $organisation->historical_days }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.historical_days']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">Geriye Dönük Arama (Gün)</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="saved_searches_limit"
                                    id="saved_searches_limit"
                                    max="12"
                                    min="0"
                                    value="{{ $organisation->saved_searches_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.saved_searches_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">Arama Kaydetme Limiti</small>
                            </div>
                        </div>
                    </li>
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Veri Havuzu</h6>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="data_pool_youtube_channel_limit"
                                    id="data_pool_youtube_channel_limit"
                                    max="100"
                                    min="0"
                                    value="{{ $organisation->data_pool_youtube_channel_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.data_pool_youtube_channel_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">YouTube Kanal Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="data_pool_youtube_video_limit"
                                    id="data_pool_youtube_video_limit"
                                    max="100"
                                    min="0"
                                    value="{{ $organisation->data_pool_youtube_video_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.data_pool_youtube_video_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">YouTube Video Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="data_pool_youtube_keyword_limit"
                                    id="data_pool_youtube_keyword_limit"
                                    max="100"
                                    min="0"
                                    value="{{ $organisation->data_pool_youtube_keyword_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.data_pool_youtube_keyword_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">YouTube Kelime Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="data_pool_twitter_keyword_limit"
                                    id="data_pool_twitter_keyword_limit"
                                    max="400"
                                    min="0"
                                    value="{{ $organisation->data_pool_twitter_keyword_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.data_pool_twitter_keyword_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">Twitter Kelime Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input
                                    data-update
                                    name="data_pool_twitter_user_limit"
                                    id="data_pool_twitter_user_limit"
                                    max="1000000"
                                    min="0"
                                    value="{{ $organisation->data_pool_twitter_user_limit }}"
                                    type="number"
                                    data-unit-price="{{ $prices['unit_price.data_pool_twitter_user_limit']['value'] }}"
                                    class="validate" />
                                <small class="helper-text">Twitter Kullanıcı Takibi</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="card-content red">
                @if ($partner_percent)
                    <p class="white-text mb-0">Bu organizasyon, bir partner tarafından yönetiliyor.</p>
                    <p class="white-text mb-0">Bu nedenle paket fiyatı en az {{ config('formal.currency') }} <span data-name="price_advice">0</span> olabilir.</p>
                @else
                    <p class="white-text mb-0">Tavsiye edilen fiyat {{ config('formal.currency') }} <span data-name="price_advice">0</span></p>
                @endif
            </div>
            <div class="card-content lighten-2">
                <div class="input-field">
                    <span class="prefix">{{ config('formal.currency') }}</span>
                    <input name="unit_price" id="unit_price" value="{{ $organisation->unit_price }}" type="number" class="validate" />
                    <label for="unit_price">Birim Fiyat</label>
                    <small class="helper-text">KDV hariç aylık organizasyon ücreti.</small>
                </div>
            </div>
            <div class="card-action right-align">
                <button type="submit" data-trigger="save" class="btn-flat waves-effect">Güncelle</button>
            </div>
        </div>
    </form>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'organisation', 'id' => $organisation->id ])
@endsection

@push('local.scripts')
    calculate()

    $(document).on('change keydown keyup', 'input[data-update]', calculate)

    function calculate()
    {
        var unit_price = parseInt((math_prices() + single_prices()) * $('input[name=user_capacity]').val());
        var advice_price = (unit_price / 100 * {{ $partner_percent }}) + unit_price;

        $('[data-name=price_advice]').html(advice_price)
    }

    function math_prices()
    {
        var price = 0;

        $.each($('input[type=number][data-unit-price]'), function(key, item) {
            var __ = $(this);
            var up = __.data('unit-price') * __.val();

            price = price + up;
        })

        return price;
    }

    function single_prices()
    {
        var price = 0;

        $.each($('input[type=checkbox][data-unit-price]:checked'), function(key, item) {
            var __ = $(this);
            var up = __.data('unit-price');

            price = price + up;
        })

        return price;
    }

    $('select').formSelect()

    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'yyyy-mm-dd',
        i18n: date.i18n
    })

    $('.timepicker').timepicker({
        format: 'hh:MM',
        twelveHour: false,
        i18n: date.i18n
    })
@endpush
