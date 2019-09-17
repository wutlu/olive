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
            'text' => '🐞 Fiyatlandırma Ayarları'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('organisation._inc._menu', [ 'active' => 'price_settings' ])
@endsection

@push('local.scripts')
    function __save(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Değerler Güncellendi',
                classes: 'green darken-2'
            }, 200)

            $('input[name=root_password]').val('')
        }
    }
@endpush

@section('content')
    <form method="post" action="{{ route('admin.organisation.price.settings') }}" class="json" id="details-form" data-callback="__save" autocomplete="off">
        <div class="card with-bg">
            <div class="card-content">
                <span class="card-title">Fiyatlandırma Ayarları</span>
            </div>
            <div class="card-content">
                <ul class="item-group mt-0">
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Modüller</h6>
                            </div>

                            @foreach (config('system.static_modules') as $key => $module)
                                <div class="collection-item input-field">
                                    <input required name="{{ $key }}" id="{{ $key }}" min="0" value="{{ $settings['unit_price.'.$key]['value'] }}" type="number" />
                                    <span class="helper-text">{{ $module }} Modülü</span>
                                </div>
                            @endforeach
                        </div>
                    </li>
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Veri Kaynakları</h6>
                            </div>

                            @foreach (config('system.modules') as $key => $module)
                                <div class="collection-item input-field">
                                    <input required name="data_{{ $key }}" id="data_{{ $key }}" min="0" value="{{ $settings['unit_price.data_'.$key]['value'] }}" type="number" />
                                    <span class="helper-text">{{ $module }} Veri Erişimi</span>
                                </div>
                            @endforeach
                        </div>
                    </li>
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Limitler</h6>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="real_time_group_limit" id="real_time_group_limit" min="0" value="{{ $settings['unit_price.real_time_group_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Gerçek Zamanlı Kelime Grubu</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="alarm_limit" id="alarm_limit" min="0" value="{{ $settings['unit_price.alarm_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Alarm</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="pin_group_limit" id="pin_group_limit" min="0" value="{{ $settings['unit_price.pin_group_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Pin Grubu</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="analysis_tools_limit" id="analysis_tools_limit" min="0" value="{{ $settings['unit_price.analysis_tools_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Analiz Araçları Limiti</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="historical_days" id="historical_days" min="0" value="{{ $settings['unit_price.historical_days']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Geriye Dönük Arama (Gün)</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="saved_searches_limit" id="saved_searches_limit" min="0" value="{{ $settings['unit_price.saved_searches_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Arama Kaydetme Limiti</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="source_limit" id="source_limit" min="0" value="{{ $settings['unit_price.source_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Kaynak Tercih Limiti</small>
                            </div>
                        </div>
                    </li>
                    <li class="item">
                        <div class="collection collection-unstyled d-flex flex-column">
                            <div class="collection-header">
                                <h6>Veri Havuzu</h6>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="data_pool_youtube_channel_limit" id="data_pool_youtube_channel_limit" min="0" value="{{ $settings['unit_price.data_pool_youtube_channel_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">YouTube Kanal Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="data_pool_youtube_video_limit" id="data_pool_youtube_video_limit" min="0" value="{{ $settings['unit_price.data_pool_youtube_video_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">YouTube Video Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="data_pool_youtube_keyword_limit" id="data_pool_youtube_keyword_limit" min="0" value="{{ $settings['unit_price.data_pool_youtube_keyword_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">YouTube Kelime Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="data_pool_twitter_keyword_limit" id="data_pool_twitter_keyword_limit" min="0" value="{{ $settings['unit_price.data_pool_twitter_keyword_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Twitter Kelime Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="data_pool_twitter_user_limit" id="data_pool_twitter_user_limit" min="0" value="{{ $settings['unit_price.data_pool_twitter_user_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Twitter Kullanıcı Takibi</small>
                            </div>
                            <div class="collection-item input-field">
                                <input required name="data_pool_instagram_follow_limit" id="data_pool_instagram_follow_limit" min="0" value="{{ $settings['unit_price.data_pool_instagram_follow_limit']['value'] }}" type="number" class="validate" />
                                <small class="helper-text">Instagram Bağlantı Takibi</small>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="collection d-flex flex-wrap">
                    <div class="collection-item input-field flex-fill">
                        <input required name="user_price" id="user_price" min="0" value="{{ $settings['unit_price.user']['value'] }}" type="number" />
                        <span class="helper-text">Kullanıcı Fiyatı</span>
                    </div>
                    <div class="collection-item input-field flex-fill">
                        <input required name="discount_with_year" id="discount_with_year" min="0" value="{{ $settings['formal.discount_with_year']['value'] }}" type="number" class="validate" />
                        <small class="helper-text">Yıllık Ödemeler için İndirim Oranı</small>
                    </div>
                </div>

                <div class="yellow-text text-darken-2">
                    @component('components.alert')
                        @slot('icon', 'info')
                        @slot('text', 'Tüm alanlar '.config('formal.currency_text').' değerinde, 1 kullanıcı için geçerli özellik birim fiyatı olarak girilmelidir.')
                    @endcomponent
                    @component('components.alert')
                        @slot('icon', 'info')
                        @slot('text', 'Bu değerler özelliklerin maliyet değerleridir. Bu değerlerin altında ürün oluşturulmasına sistem müsade etmeyecektir.')
                    @endcomponent
                </div>

                <ul class="item-group">
                    @foreach ([
                        'eagle' => 'Eagle',
                        'phoenix' => 'Phoenix',
                        'gryphon' => 'Gryphon',
                        'dragon' => 'Dragon'
                    ] as $key => $name)
                        <li class="item">
                            <h5 class="orange-text center-align">{{ $name }} Partner</h5>
                            <div class="p-1">
                                <img alt="Emblem" src="{{ asset('img/partner-'.$key.'.png') }}" class="responsive-img" />
                            </div>
                            <div class="collection">
                                <div class="collection-item input-field">
                                    <input required name="{{ $key }}_percent" id="{{ $key }}_percent" min="0" value="{{ $settings['formal.partner.'.$key.'.percent']['value'] }}" type="number" class="validate" />
                                    <small class="helper-text">{{ $name }} partnerin kâr yüzdesi</small>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="input-field">
                    <input name="root_password" id="root_password" type="password" class="validate" />
                    <label for="root_password">Root Şifresi</label>
                    <span class="helper-text">Güvenlik için root şifresi girmeniz gerekiyor.</span>
                </div>
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">Güncelle</button>
            </div>
        </div>
    </form>
@endsection
