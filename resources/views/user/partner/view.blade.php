@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Partner'
        ],
        [
            'text' => 'Kullanıcılar',
            'link' => route('partner.user.list')
        ],
        [
            'text' => $user ? $user->name : 'Kullanıcı Oluştur'
        ]
    ],
    'footer_hide' => true
])

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">{{ $user ? $user->name : 'Kullanıcı Oluştur' }}</span>
        </div>
    </div>
@endsection

@push('local.scripts')
    function __action(__, obj)
    {
        if (obj.status == 'ok')
        {
            switch (obj.data.status)
            {
                case 'created':
                    location.href = '{{ route('partner.user') }}/' + obj.data.id;
                break;
                case 'updated':
                    return modal({
                        'id': 'info',
                        'body': 'Bilgiler güncellendi. Organizasyon devre dışı kaldı. Yönetim onayından sonra organizasyon tekrar aktif edilecektir.',
                        'title': keywords.info,
                        'size': 'modal-small',
                        'options': {},
                        'footer': [
                            $('<a />', {
                                'href': '#',
                                'class': 'modal-close waves-effect btn-flat',
                                'html': buttons.ok
                            })
                        ]
                    })
                break;
            }
        }
    }
@endpush

@section('content')
    <form
        id="details-form"
        method="post"
        action="{{ route(@$user ? 'partner.user.update' : 'partner.user.create') }}"
        class="json"
        data-callback="__action">
        @if (@$user)
            <input type="hidden" value="{{ $user->id }}" name="user_id" />
        @endif

        <div class="card">
            <div class="collection collection-unstyled">
                <div class="collection-item">
                    @if (@$user)
                        <small class="teal-text">Kullanıcı Adı</small>
                        <p class="mb-0">{{ $user->name }}</p>
                    @else
                        <div class="p-1">
                            <div class="input-field" style="max-width: 240px;">
                                <input name="name" id="name" type="text" class="validate" />
                                <label for="name">Ad</label>
                                <small class="helper-text">Kullanıcının sistemdeki kullanıcı adı.</small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="collection-item">
                    @if (@$user)
                        <small class="teal-text">E-posta</small>
                        <p class="mb-0">{{ $user->email }}</p>
                    @else
                        <div class="d-flex flex-wrap">
                            <div class="p-1" style="max-width: 240px;">
                                <div class="input-field">
                                    <input name="email" id="email" type="email" class="validate" />
                                    <label for="email">E-posta</label>
                                    <small class="helper-text">Kullanıcının sistemdeki e-posta adresi.</small>
                                </div>
                            </div>
                            <div class="p-1" style="max-width: 240px;">
                                <div class="input-field">
                                    <input name="email_confirmation" id="email_confirmation" type="email" class="validate" />
                                    <label for="email_confirmation">E-posta (Tekrar)</label>
                                    <small class="helper-text">E-posta adresini doğrulayın.</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <ul class="collection">
            @if ($user)
                <li class="collection-item">
                    <small class="grey-text">Oluşturuldu</small>
                    <p class="d-block">{{ date('d.m.Y H:i', strtotime($user->created_at)) }}</p>
                </li>
            @else
                <li class="collection-item">
                    <span class="grey-text">
                        @component('components.alert')
                            @slot('icon', 'info')
                            @slot('text', 'Gireceğiniz e-posta adresine, şifre vb. gerekli bilgiler gönderilecektir.')
                        @endcomponent
                    </span>
                </li>
            @endif
            <li class="collection-item">
                <span class="red-text">
                    @component('components.alert')
                        @slot('icon', 'warning')
                        @slot('text', 'Kullanıcılar silinemez! Sadece yöneticiler tarafından devre dışı bırakılabilir.')
                    @endcomponent
                </span>
            </li>
            <li class="collection-item">
                <span class="red-text">
                    @component('components.alert')
                        @slot('icon', 'warning')
                        @slot('text', 'Organizasyonlar sadece müşteri tarafından silinebilir. Yöneticiler tarafından devre dışı bırakılabilir.')
                    @endcomponent
                </span>
            </li>
            <li class="collection-item">
                <span class="teal-text">
                    @component('components.alert')
                        @slot('icon', 'info')
                        @slot('text', 'Yapacağınız her türlü güncelleme sonucu organizasyon, incelenmek üzere devre dışı kalır.')
                    @endcomponent
                </span>
            </li>
        </ul>

        @if (@$user->organisation)
            <div class="card mb-1">
                <div class="card-content">
                    <div class="collection">
                        <div class="collection-item">
                            <div class="d-flex">
                                <div class="input-field">
                                    <input name="end_date" id="end_date" value="{{ date('Y-m-d', strtotime($user->organisation->end_date)) }}" type="text" class="validate datepicker" />
                                    <label for="end_date">Bitiş Tarihi</label>
                                    <small class="helper-text">Organizasyonun bitiş tarihi.</small>
                                </div>
                                <div class="input-field">
                                    <input name="end_time" id="end_time" value="{{ date('H:i', strtotime($user->organisation->end_date)) }}" type="text" class="validate timepicker" />
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
                                            {{ $user->organisation->{$key} ? 'checked' : '' }} />
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
                                            {{ $user->organisation->{'data_'.$key} ? 'checked' : '' }} />
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
                                        value="{{ $user->organisation->user_capacity }}"
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
                                        value="{{ $user->organisation->real_time_group_limit }}"
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
                                         value="{{ $user->organisation->alarm_limit }}"
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
                                        value="{{ $user->organisation->pin_group_limit }}"
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
                                        value="{{ $user->organisation->historical_days }}"
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
                                        value="{{ $user->organisation->saved_searches_limit }}"
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
                                        value="{{ $user->organisation->data_pool_youtube_channel_limit }}"
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
                                        value="{{ $user->organisation->data_pool_youtube_video_limit }}"
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
                                        value="{{ $user->organisation->data_pool_youtube_keyword_limit }}"
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
                                        value="{{ $user->organisation->data_pool_twitter_keyword_limit }}"
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
                                        value="{{ $user->organisation->data_pool_twitter_user_limit }}"
                                        type="number"
                                        data-unit-price="{{ $prices['unit_price.data_pool_twitter_user_limit']['value'] }}"
                                        class="validate" />
                                    <small class="helper-text">Twitter Kullanıcı Takibi</small>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <table class="highlight grey lighten-2">
                    <tbody>
                        <tr>
                            <th class="p-1">Organizasyon maliyeti</th>
                            <th class="p-1 right-align">{{ config('formal.currency') }} <span data-name="price_cost">0</span></th>
                        </tr>
                        <tr>
                            <th class="p-1">Tavsiye edilen {{ config('formal.tax_name') }} hariç organizasyon fiyatı</th>
                            <th class="p-1 right-align">{{ config('formal.currency') }} <span data-name="price_advice">0</span></th>
                        </tr>
                        <tr>
                            <th class="p-1">Partnerin kazancı</th>
                            <th class="p-1 right-align">{{ config('formal.currency') }} <span data-name="price_profit">0</span></th>
                        </tr>
                    </tbody>
                </table>
                <div class="card-content lighten-2">
                    <div class="input-field">
                        <span class="prefix">{{ config('formal.currency') }}</span>
                        <input name="unit_price" id="unit_price" value="{{ $user->organisation->unit_price }}" type="number" class="validate" />
                        <label for="unit_price">Birim Fiyat</label>
                        <small class="helper-text">{{ config('formal.tax_name') }} hariç aylık organizasyon fiyatı.</small>
                    </div>
                </div>
            </div>
        @endif

        <div class="right-align">
            <a href="{{ route('partner.user.list') }}" class="btn-flat waves-effect red-text">Vazgeç</a>
            <button type="submit" class="btn-flat waves-effect">{{ $user ? (@$user->organisation ? 'Güncelle' : 'Organizasyon Oluştur') : 'Kullanıcı Oluştur' }}</button>
        </div>
    </form>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=save]', function() {
        modal({
            'id': 'alert',
            'body': 'Test',
            'title': 'Uyarı',
            'size': 'modal-small',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat',
                    'html': buttons.cancel
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'data-json-target': '#details-form',
                    'html': buttons.ok
                })
            ]
        })
    })

    calculate()

    $(document).on('change keydown keyup', 'input[data-update]', calculate)

    function calculate()
    {
        var unit_price = parseInt((math_prices() + single_prices()) * $('input[name=user_capacity]').val());
            unit_price = parseInt(unit_price / 100 * {{ $partner_percent }}) + unit_price;

        var advice_price = parseInt(unit_price / 100 * {{ $partner_percent }}) + unit_price;

        var price_profit = parseInt($('input[name=unit_price]').val() - unit_price);

        $('[data-name=price_advice]').html(advice_price)
        $('[data-name=price_cost]').html(unit_price)
        $('[data-name=price_profit]').html(price_profit)

        $('[name=unit_price]').attr('min', advice_price)
    }

    $(document).on('change keydown keyup', 'input[name=unit_price]', function() {
        calculate()
    })

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
