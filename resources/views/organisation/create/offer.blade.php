@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Organizasyon Yükselt'
        ]
    ],
    'help' => 'helpStart.start()'
])

@section('content')
    @push('local.scripts')
        const helpStart = new Driver()

        helpStart.defineSteps([
            @if ($demo)
            {
                element: '[data-id=cancel]',
                popover: {
                    title: 'Dur!',
                    description: 'Deneme süren henüz bitmedi! Bu işleme devam edersen deneme modundan çıkarak tam sürüme geçeceksin!',
                    position: 'top'
                }
            },
            @endif
            {
                element: '[data-id=price]',
                popover: {
                    title: 'Hadi Başlayalım!',
                    description: 'Fiyat alanı özellik seçtikçe güncellenir. Unutmadan! "Organizasyon Yükseltme" işleminin sonunda deneme süren sona erecek.',
                    position: 'top'
                }
            },
            {
                element: '[data-id=module_real_time]',
                showButtons: true,
                popover: {
                    title: 'Canlı Akış',
                    description: 'Web ortamında paylaşılan ve sizi ilgilendiren her şey eş zamanlı olarak önünüze düşer. <br /><br /><a href="{{ asset('img/realtime.gif') }}" target="_blank"><img alt="Image" src="{{ asset('img/realtime.gif') }}" class="responsive-img" /></a>',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_crm]',
                showButtons: true,
                popover: {
                    title: 'CRM',
                    description: 'Sosyal mecralar için sistem üzerinden planlı veya plansız cevaplar verebilirsiniz.',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_search]',
                showButtons: true,
                popover: {
                    title: 'Arama Motoru',
                    description: 'Kitle ölçümlemeleri, web araştırmaları ve çeşitli analizler üretebileceğin Veri Zone ana modülü. <br /><br /><a href="{{ asset('img/search.jpg') }}" target="_blank"><img alt="Image" src="{{ asset('img/search.jpg') }}" class="responsive-img" /></a>',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_trend]',
                showButtons: true,
                popover: {
                    title: 'Trendler',
                    description: 'Türkiye\'de şu an neler oluyor? Eş zamanlı takibini yapabilirsin. <br /><br /><a href="{{ asset('img/trend.jpg') }}" target="_blank"><img alt="Image" src="{{ asset('img/trend.jpg') }}" class="responsive-img" /></a>',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_alarm]',
                showButtons: true,
                popover: {
                    title: 'Alarmlar',
                    description: 'Google veya Yandex gibi arama motorlarının bile günler sonra ulaştığı ve bazen ulaşamadığı verilerden eş zamanlı haberdar olun. <br /><br /><a href="{{ asset('img/alarm.jpg') }}" target="_blank"><img alt="Image" src="{{ asset('img/alarm.jpg') }}" class="responsive-img" /></a>',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_compare]',
                showButtons: true,
                popover: {
                    title: 'Veri Kıyaslama',
                    description: 'Rakiplerini veya ilgilendiğin konuları istatistiksel olarak karşılaştırabilirsin.',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_borsa]',
                showButtons: true,
                popover: {
                    title: 'Kalabalığın Düşüncesi',
                    description: 'Borsa verilerinden çıkarımlar gerçekleştirebilirsin.',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=module_report]',
                showButtons: true,
                popover: {
                    title: 'Raporlama',
                    description: 'İster otomatik, ister Veri Zone Rapor editörünü kullanarak hızlı, anlamlı ve iyi görünümlü raporlar oluşturabilirsin.',
                    position: 'bottom'
                }
            },
            {
                element: '[data-id=first-next]',
                showButtons: false,
                popover: {
                    title: 'Harika!',
                    description: 'Bir sonraki aşamaya geçebilirsin.',
                    position: 'left'
                }
            }
        ])

        helpStart.start()
    @endpush

    <div class="olive-alert success hide">
        <div class="anim"></div>
        <h4 class="mb-2">Hesap Yükseltildi!</h4>
        <p>Ödeme işlemi için Organizasyon sayfasını kullanabilirsiniz!</p>
        <p class="mb-2"> İyi araştırmalar dileriz!</p>
        <a href="{{ route('dashboard') }}" class="btn-flat waves-effect">Ana Sayfa</a>
        <a href="{{ route('search.dashboard') }}" class="btn green waves-effect">Hemen Başla!</a>
    </div>
    <form class="json" method="post" action="{{ route('organisation.create.offer') }}" data-callback="__create" id="offer">
        <div class="d-flex mx-auto" style="max-width: 600px;">
            <div class="flex-fill card card-unstyled" data-step="1">
                <div class="card-content card-step">
                    <span class="step">1/4</span>
                    <span class="title">Modül Seçimi</span>
                </div>
                <ul class="collection">
                    @foreach (config('system.static_modules') as $key => $module)
                        <li class="collection-item" data-id="{{ $key }}">
                            <label>
                                <input
                                    data-update
                                    data-option="module"
                                    data-unit-price="{{ $prices['unit_price.'.$key]['value'] }}"
                                    name="{{ $key }}"

                                    @if ($key == 'module_real_time' || $key == 'module_compare' || $key == 'module_alarm' || $key == 'module_crm')
                                        data-requirement="module_search"
                                    @endif

                                    id="{{ $key }}"
                                    value="on"
                                    checked
                                    type="checkbox" />
                                <span>{{ $module }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
                <div class="card-content d-flex justify-content-between">
                    <a class="btn red btn-large waves-effect" href="{{ route('dashboard') }}" data-id="cancel" data-tooltip="Ücretsiz özellikleri kullanmaya devam et!">Vazgeç</a>
                    <button type="button" class="btn blue-grey btn-large waves-effect" data-steps="2" data-id="first-next">
                        <i class="material-icons">arrow_forward</i>
                    </button>
                </div>
            </div>
            <div class="flex-fill card card-unstyled hide" data-step="2">
                <div class="card-content card-step">
                    <span class="step">2/4</span>
                    <span class="title">Veri Kaynakları</span>
                </div>
                <div class="card-content red-text text-darken-2 hide" data-alert="trend-info">
                    @component('components.alert')
                        @slot('icon', 'info')
                        @slot('text', 'Seçtiğin modül(ler) veri kaynağına ihtiyaç duymuyor.')
                    @endcomponent
                </div>
                <div class="card-content info-bg" data-id="data-sources">Veri Zone örümcekleri tıpkı Google'ın yaptığı gibi fakat Google'dan biraz daha hızlı bir şekilde web'de gezinerek çeşitli mecralardan veri elde eder. Bu veri türlerinden erişmek istediklerinizi seçin. <strong>Ayrıca Veri Zone'a sonradan eklenecek veri kaynaklarına da bir sonraki fatura döneminize kadar ücretsiz erişebilirsiniz.</strong></div>
                <ul class="collection collection-hoverable" data-tab="source">
                    @foreach (config('system.modules') as $key => $module)
                        <li class="collection-item">
                            <label>
                                <input
                                    data-update
                                    data-option="source"
                                    name="data_{{ $key }}"
                                    id="data_{{ $key }}"
                                    value="on"
                                    data-unit-price="{{ $prices['unit_price.data_'.$key]['value'] }}"
                                    checked
                                    type="checkbox" />
                                <span class="align-self-center">{{ $module }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
                <div class="card-content d-flex justify-content-between">
                    <button type="button" class="btn red btn-large waves-effect" data-steps="1" data-prev="true">
                        <i class="material-icons">arrow_back</i>
                    </button>
                    <button type="button" class="btn blue-grey btn-large waves-effect" data-steps="3">
                        <i class="material-icons">arrow_forward</i>
                    </button>
                </div>
            </div>
            <div class="flex-fill card card-unstyled hide" data-step="3">
                <div class="card-content card-step">
                    <span class="step">3/4</span>
                    <span class="title">Limitler</span>
                </div>
                <div class="card-content">
                    <div class="d-flex justify-content-between">
                        <small>Kullanıcı Kapasitesi</small>
                        <small data-name="value">1</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            name="user_capacity"
                            id="user_capacity"
                            max="12"
                            min="1"
                            value="1"
                            type="range" />
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="d-flex">
                            <span class="align-self-center mr-1">Arşiv</span>
                            <a
                                href="#"
                                class="btn-floating blue-grey pulse align-self-center"
                                data-tooltip="Bilgi"
                                data-position="right"
                                data-trigger="info"
                                data-title="Arşiv">
                                <i class="material-icons black-text" data-id="sample-info">info_outline</i>
                                <div class="hide" data-helper>
                                    <span style="font-size: 16px;">İlgilendiğin içerikleri arşivler halinde saklayabilir ve istediğin zaman çıktılarını alabilirsin. Bu alan zorunlu değildir.</span>
                                </div>
                            </a>
                        </small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-unit-price="{{ $prices['unit_price.archive_limit']['value'] }}"
                            name="archive_limit"
                            id="archive_limit"
                            max="12"
                            min="0"
                            value="0"
                            type="range" />
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="d-flex">
                            <span class="align-self-center mr-1">Geçmişe Yönelik Arama (Gün)</span>
                            <a
                                href="#"
                                class="btn-floating blue-grey pulse align-self-center"
                                data-tooltip="Bilgi"
                                data-position="right"
                                data-trigger="info"
                                data-title="Geçmişe Yönelik Arama">
                                <i class="material-icons black-text">info_outline</i>
                                <div class="hide" data-helper>
                                    <span style="font-size: 16px;">Arama, Alarm, Veri Kıyaslama ve Akış modülü kullanmak istiyorsan en az 1 gün "Geçmişe Yönelik Arama" yapabiliyor olman gerekiyor.</span>
                                </div>
                            </a>
                        </small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-unit-price="{{ $prices['unit_price.historical_days']['value'] }}"
                            name="historical_days"
                            id="historical_days"
                            max="90"
                            min="0"
                            value="30"
                            type="range" />
                    </div>

                    <div class="d-flex justify-content-between">
                        <small class="d-flex">
                            <span class="align-self-center mr-1">Arama Kaydetme</span>
                            <a
                                href="#"
                                class="btn-floating blue-grey pulse align-self-center"
                                data-tooltip="Bilgi"
                                data-position="right"
                                data-trigger="info"
                                data-title="Arama Kaydetme">
                                <i class="material-icons black-text">info_outline</i>
                                <div class="hide" data-helper>
                                    <span style="font-size: 16px;">Aramaları kaydederek "Alarm, Akış ve Veri Kıyaslama" modüllerinde kullanabilirsin. "Alarm, Akış ve Veri Kıyaslama" bölümünü kullanabilmek için en az 1 "Arama Kaydetme" seçimi yapmalısın.</span>
                                </div>
                            </a>
                        </small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-unit-price="{{ $prices['unit_price.saved_searches_limit']['value'] }}"
                            name="saved_searches_limit"
                            id="saved_searches_limit"
                            max="12"
                            min="0"
                            value="0"
                            type="range" />
                    </div>
                </div>
                <div class="card-content d-flex justify-content-between">
                    <button type="button" class="btn red btn-large waves-effect" data-steps="2" data-prev="true">
                        <i class="material-icons">arrow_back</i>
                    </button>
                    <button type="button" class="btn blue-grey btn-large waves-effect" data-steps="4">
                        <i class="material-icons">arrow_forward</i>
                    </button>
                </div>
            </div>
            <div class="flex-fill card card-unstyled hide" data-step="4">
                <div class="card-content card-step">
                    <span class="step">4/4</span>
                    <span class="title">Kaynak Takibi</span>
                </div>
                <div class="card-content info-bg" data-id="data-limits">Bazı içerikler Veri Zone örümceklerinin gözünden kaçabilir. Bu gibi durumlar için takip edilmesini istediğin özel kaynakları belirtebilirsin.</div>
                <div class="card-content">
                    <div class="d-flex justify-content-between">
                        <small>YouTube Kanal Takibi</small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-option="pool"
                            name="data_pool_youtube_channel_limit"
                            id="data_pool_youtube_channel_limit"
                            data-unit-price="{{ $prices['unit_price.data_pool_youtube_channel_limit']['value'] }}"
                            max="100"
                            min="0"
                            value="0"
                            type="range" />
                    </div>

                    <div class="d-flex justify-content-between">
                        <small>YouTube Video Takibi</small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-option="pool"
                            name="data_pool_youtube_video_limit"
                            id="data_pool_youtube_video_limit"
                            data-unit-price="{{ $prices['unit_price.data_pool_youtube_video_limit']['value'] }}"
                            max="100"
                            min="0"
                            value="0"
                            type="range" />
                    </div>

                    <div class="d-flex justify-content-between">
                        <small>YouTube Kelime Takibi</small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-option="pool"
                            name="data_pool_youtube_keyword_limit"
                            id="data_pool_youtube_keyword_limit"
                            data-unit-price="{{ $prices['unit_price.data_pool_youtube_keyword_limit']['value'] }}"
                            max="100"
                            min="0"
                            value="0"
                            type="range" />
                    </div>

                    <div class="d-flex justify-content-between">
                        <small>Twitter Kelime Takibi</small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-option="pool"
                            name="data_pool_twitter_keyword_limit"
                            id="data_pool_twitter_keyword_limit"
                            data-unit-price="{{ $prices['unit_price.data_pool_twitter_keyword_limit']['value'] }}"
                            max="100"
                            min="0"
                            value="0"
                            type="range" />
                    </div>

                    <div class="d-flex justify-content-between">
                        <small>Twitter Kullanıcı Takibi</small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-option="pool"
                            name="data_pool_twitter_user_limit"
                            id="data_pool_twitter_user_limit"
                            data-unit-price="{{ $prices['unit_price.data_pool_twitter_user_limit']['value'] }}"
                            max="100"
                            min="0"
                            value="0"
                            type="range" />
                    </div>

                    <div class="d-flex justify-content-between">
                        <small>Instagram Bağlantı Takibi</small>
                        <small data-name="value">0</small>
                    </div>
                    <div class="range-field">
                        <input
                            data-update
                            data-option="pool"
                            name="data_pool_instagram_follow_limit"
                            id="data_pool_instagram_follow_limit"
                            data-unit-price="{{ $prices['unit_price.data_pool_instagram_follow_limit']['value'] }}"
                            max="100"
                            min="0"
                            value="0"
                            type="range" />
                    </div>
                </div>
                <div class="card-content d-flex justify-content-between">
                    <button type="button" class="btn red btn-large waves-effect" data-steps="3" data-prev="true">
                        <i class="material-icons">arrow_back</i>
                    </button>
                    <button type="submit" class="btn cyan darken-2 btn-large waves-effect">Yükselt</button>
                </div>
            </div>
        </div>

        <div class="center-align red-text hide p-2" data-name="alert" style="font-size: 20px;"></div>

        <div class="d-table mx-auto" data-id="price">
            <div id="price">{{ config('formal.currency') }}<span data-name="price-total">0</span> +kdv <sub class="grey-text">/ ay</sub></div>
            <small class="grey-text d-table mx-auto">DENEME SONRASI ÖDEMENİZ GEREKECEK TUTAR</small>
        </div>
    </form>
@endsection

@push('local.styles')
    #price {
        font-size: 24px;
        padding: 1rem;
    }

    .thumb {
        line-height: 100%;
    }

    .btn-floating {
        width: 24px;
        height: 24px;
        line-height: 24px;
    }
    .btn-floating > i {
        line-height: 24px;
    }
@endpush

@push('local.scripts')
    $(document).on('change', 'input[type=range]', function() {
        range_function($(this))
    }).on('change', '[data-option]', function() {
        var __ = $(this);

        if (__.is(':checked') && __.data('requirement'))
        {
            $('input[name=' + __.data('requirement') + ']').prop('checked', true)
        }
        else if (!__.is(':checked') && __.attr('name') == 'module_search' && $('[data-requirement=module_search]:checked').length)
        {
            __.prop('checked', true)

             M.toast({ html: 'Arama modülü gerektiren modül veya modüller seçiliyken "Arama" modülü olmak zorundadır!', classes: 'orange' })
        }

        optimize()
    })

    optimize()

    function optimize()
    {
        var source_inputs = $('input[data-option=source]');
        var pool_inputs = $('input[data-option=pool]');
        var historical_days_input = $('input[name=historical_days]');
        var saved_searches_limit_input = $('input[name=saved_searches_limit]');
        var archive_limit_input = $('input[name=archive_limit]');

        var min_data_select = null;
        var min_pool_select = null;
        var min_historical_days = null;
        var min_saved_searches = null;
        var min_archive_groups = null;

        var module_real_time = $('input[name=module_real_time]:checked');
        var module_crm = $('input[name=module_crm]:checked');
        var module_compare = $('input[name=module_compare]:checked');
        var module_search = $('input[name=module_search]:checked');
        var module_alarm = $('input[name=module_alarm]:checked');

        if (module_real_time.length)
        {
            min_data_select = 1;
            min_pool_select = 1;
            min_historical_days = 1;
            min_saved_searches = 1;
            min_archive_groups = 0;
        }

        if (module_crm.length)
        {
            min_historical_days = 1;
            min_saved_searches = 1;
        }

        if (module_search.length)
        {
            min_data_select = 1;
            min_pool_select = 1;
            min_historical_days = 1;
            min_saved_searches = 0;
            min_archive_groups = 0;
        }

        if (module_alarm.length)
        {
            min_data_select = 1;
            min_pool_select = 1;
            min_historical_days = 1;
            min_saved_searches = 1;
            min_archive_groups = 0;
        }

        if (module_compare.length)
        {
            min_saved_searches = 2;
        }

        if (min_data_select)
        {
            source_inputs.removeClass('disabled').removeAttr('disabled')

            $('[data-alert=trend-info]').addClass('hide')
        }
        else
        {
            source_inputs.addClass('disabled').attr('disabled', 'disabled').prop('checked', false)

            $('[data-alert=trend-info]').removeClass('hide')
        }

        if (min_pool_select)
        {
            pool_inputs.removeClass('disabled').removeAttr('disabled')
        }
        else
        {
            pool_inputs.addClass('disabled').attr('disabled', 'disabled').val(0)
        }

        if (min_historical_days)
        {
            historical_days_input.attr('min', min_historical_days)
                                 .val(historical_days_input.val() < min_historical_days ? min_historical_days : historical_days_input.val())
                                 .removeAttr('disabled')
        }
        else
        {
            historical_days_input.attr('min', 0).val(0).attr('disabled', 'disabled')
        }

        if (min_saved_searches !== null)
        {
            saved_searches_limit_input.attr('min', min_saved_searches)
                                      .val(saved_searches_limit_input.val() < min_saved_searches ? min_saved_searches : saved_searches_limit_input.val())
                                      .removeAttr('disabled')
        }
        else
        {
            saved_searches_limit_input.attr('min', 0).val(0).attr('disabled', 'disabled')
        }

        if (min_archive_groups !== null)
        {
            archive_limit_input.attr('min', min_archive_groups)
                               .val(archive_limit_input.val() < min_archive_groups ? min_archive_groups : archive_limit_input.val())
                               .removeAttr('disabled')
        }
        else
        {
            archive_limit_input.attr('min', 0).val(0).attr('disabled', 'disabled')
        }

        $('input[type=range]').each(function() {
            range_function($(this))
        })
    }

    $('input[type=range]').each(function() {
        range_function($(this))
    })

    function range_function(__)
    {
        __.closest('.range-field').prev('.d-flex').find('[data-name=value]').html(__.val())
    }

    $(document).on('click', '[data-steps]', function() {
        var __ = $(this);
        var current = __.data('steps')-1;
        var next = __.data('steps');

        $('[data-step]').addClass('hide')

        $('[data-step=' + next + ']').removeClass('hide').hide().show('drop', {}, 600)

        $('[data-name=alert]').addClass('hide')

        _scrollTo({
            'target': '#offer',
            'tolerance': '-526px',
            'speed': 1
        })

        if (__.data('steps') == 1)
        {
            $('#help-button').removeClass('hide')
        }
        else if (__.data('steps') == 2)
        {
            helpStart.reset()

            setTimeout(function() {
                const driver = new Driver();
                      driver.highlight('[data-id=data-sources]');
            }, 1000)

            $('#help-button').addClass('hide')
        }
        else if (__.data('steps') == 4)
        {
            setTimeout(function() {
                const driver = new Driver();
                      driver.highlight('[data-id=data-limits]');
            }, 1000)
        }
    })

    function calculate()
    {
        var total_price = parseInt((math_prices() + single_prices()) + ($('input[name=user_capacity]').val() * {{ $prices['unit_price.user']['value'] }}));
            total_price = total_price - {{ $prices['unit_price.user']['value'] }};

        $('[data-name=price-total]').html((total_price).toFixed(2))
    }

    calculate()

    $(document).on('change keydown keyup', 'input[data-update]', calculate)

    @if (session('timeout'))
        M.toast({ html: 'İşlem zaman aşımına uğradı! Lütfen tekrar deneyin.', classes: 'red' })
    @endif

    $(document).on('click', '[data-trigger=info]', function() {
        var __ = $(this);

        modal({
            'id': 'info',
            'body': __.children('[data-helper]').html(),
            'size': 'modal-small',
            'title': __.data('title'),
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat',
                    'html': keywords.ok
                })
            ]
        });
    })

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.addClass('hide')

            flash_alert('Harika!', 'green white-text')

            $('.olive-alert').removeClass('hide')

            _scrollTo({
                'target': '.olive-alert',
                'tolerance': '-64px'
            })
        }
        else if (obj.status == 'step')
        {
            $('[data-name=alert]').removeClass('hide').hide().effect('highlight', { 'color': '#F44336' }, 1000).html(obj.message)

            $('[data-step]').addClass('hide')

            $('[data-step=' + obj.step + ']').removeClass('hide').hide().show('fade', {}, 600)

            _scrollTo({
                'target': '[data-name=alert]',
                'tolerance': '-256px',
                'speed': 1
            })
        }
    }
@endpush
