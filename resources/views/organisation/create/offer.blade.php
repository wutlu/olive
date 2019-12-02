@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Organizasyon Oluştur'
        ]
    ]
])

@section('content')
    @if ($user->gsm_verified_at)
        <div class="olive-alert success hide">
            <div class="anim"></div>
            <h4 class="mb-2">Organizasyon Oluşturuldu!</h4>
            <p>Organizasyonunuz aktif! Seçtiğiniz tüm özellikleri 1 gün boyunca ücretsiz olarak kullanabilirsiniz.</p>
            <p class="mb-2"> İyi araştırmalar dileriz!</p>
            <a href="{{ route('dashboard') }}" class="btn-flat waves-effect">Ana Sayfa</a>
            <a href="{{ route('settings.organisation') }}" class="btn green waves-effect">Organizasyon</a>
        </div>
        <form class="json" method="post" action="{{ route('organisation.create.offer') }}" data-callback="__create" id="offer">
            <div class="d-flex mx-auto" style="max-width: 600px;">
                <div class="flex-fill card card-unstyled" data-step="1">
                    <div class="card-content card-step">
                        <span class="step">1/4</span>
                        <span class="title">Modül Seçimi</span>
                    </div>
                    <ul class="collection collection-hoverable">
                        @foreach (config('system.static_modules') as $key => $module)
                            <li class="collection-item info-bg">
                                <label>
                                    <input
                                        data-update
                                        data-option="module"
                                        data-unit-price="{{ $prices['unit_price.'.$key]['value'] }}"
                                        name="{{ $key }}"

                                        @if ($key == 'module_real_time' || $key == 'module_compare' || $key == 'module_alarm')
                                            data-requirement="module_search"
                                        @endif

                                        id="{{ $key }}"
                                        value="on"
                                        type="checkbox" />
                                    <span>{{ $module }} {!! $prices['unit_price.'.$key]['value'] ? '' : '<sup class="red-text">Ücretsiz</sup>' !!}</span>
                                </label>

                                @if ($key == 'module_real_time')
                                    <p class="mb-0">Geniş çaplı filtreleme özellikleri ve kullanıcı dostu arayüzü ile gündemdeki paylaşımları eş zamanlı izlemenizi sağlar.</p>
                                @elseif ($key == 'module_search')
                                    <p class="mb-0">Eş zamanlı veya geçmişe yönelik sosyal medya ve web verileri içerisinden geniş çaplı filtreler ile aramalar gerçekleştirebilirsiniz. Ayrıca elde edeceğiniz sonuçları görselleştirerek kitle ölçümleri, rakip analizleri ve çeşitli görüler elde edebilmenize olanak tanır.</p>
                                @elseif ($key == 'module_trend')
                                    <p class="mb-0">Eş zamanlı veya geçmişe yönelik trend olmuş; kelime, kullanıcı veya başlık takibi, genel veya sektörel popüler kullanıcı listeleri sağlar.</p>
                                @elseif ($key == 'module_compare')
                                    <p class="mb-0">Arama ile belirleyeceğiniz kriterleri sorgu sınırı olmadan kıyaslayabilirsiniz.</p>
                                @elseif ($key == 'module_borsa')
                                    <p class="mb-0">Kalabalığın düşüncesini dinleyerek hisselerinize yön verebilirsiniz.</p>
                                @elseif ($key == 'module_report')
                                    <p class="mb-0">Araştırmalarınızı yaparken raporlama editörünü kullanarak eş zamanlı raporlar oluşturabilirsiniz.</p>
                                @elseif ($key == 'module_alarm')
                                    <p class="mb-0">Bilgisayar başında gerçirecek vaktiniz yoksa "Alarmlar" sayesinde konuşulanlardan eş zamanlı haberdar olabilirsiniz.</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="card-content d-flex justify-content-between">
                        <a class="btn red btn-large waves-effect" href="{{ route('dashboard') }}" data-tooltip="Ücretsiz özellikleri kullanmaya devam et!">Vazgeç</a>
                        <button type="button" class="btn blue-grey btn-large waves-effect" data-steps="2">
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
                            @slot('text', 'Seçtiğiniz modül(ler) veri kaynağına ihtiyaç duymuyor.')
                        @endcomponent
                    </div>
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
                                        type="checkbox" />
                                    <span class="align-self-center">{{ $module }} {!! $prices['unit_price.data_'.$key]['value'] ? '' : '<sup class="red-text">Ücretsiz</sup>' !!}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <div class="card-content info-bg">Olive örümcekleri tıpkı Google'ın yaptığı gibi fakat Google'dan biraz daha hızlı bir şekilde web'de gezinerek çeşitli mecralardan veri elde eder. Bu veri türlerinden hangilerine erişmek istiyorsunuz?</div>
                    <div class="card-content d-flex justify-content-between">
                        <button type="button" class="btn red lighten-2 btn-large waves-effect" data-steps="1" data-prev="true">
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
                                <span class="align-self-center mr-1">Pin Grubu</span>
                                <a
                                    href="#"
                                    class="align-self-center"
                                    data-tooltip="Bilgi"
                                    data-position="right"
                                    data-trigger="info"
                                    data-title="Pin Grubu">
                                    <i class="material-icons tiny teal-text">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">İlgilendiğiniz içerikleri gruplar halinde saklayabilir ve istediğiniz zaman çıktılarını alabilirsiniz. Bu alan zorunlu değildir.</span>
                                    </div>
                                </a>
                            </small>
                            <small data-name="value">0</small>
                        </div>
                        <div class="range-field">
                            <input
                                data-update
                                data-unit-price="{{ $prices['unit_price.pin_group_limit']['value'] }}"
                                name="pin_group_limit"
                                id="pin_group_limit"
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
                                    class="align-self-center"
                                    data-tooltip="Bilgi"
                                    data-position="right"
                                    data-trigger="info"
                                    data-title="Geçmişe Yönelik Arama">
                                    <i class="material-icons tiny teal-text">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">Arama, Alarm, Veri Kıyaslama ve Akış modülü kullanmak istiyorsanız en az 1 gün "Geçmişe Yönelik Arama" yapabiliyor olmanız gerekiyor.</span>
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
                                value="0"
                                type="range" />
                        </div>

                        <div class="d-flex justify-content-between">
                            <small class="d-flex">
                                <span class="align-self-center mr-1">Arama Kaydetme</span>
                                <a
                                    href="#"
                                    class="align-self-center"
                                    data-tooltip="Bilgi"
                                    data-position="right"
                                    data-trigger="info"
                                    data-title="Arama Kaydetme">
                                    <i class="material-icons tiny teal-text">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">Aramalarınızı kaydederek "Alarm, Akış ve Veri Kıyaslama" modüllerinde kullanabilirsiniz. "Alarm, Akış ve Veri Kıyaslama" bölümünü kullanabilmek için en az 1 "Arama Kaydetme" seçimi yapmalısınız.</span>
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
                        <button type="button" class="btn red lighten-2 btn-large waves-effect" data-steps="2" data-prev="true">
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
                    <div class="card-content info-bg">Bazı içerikler Olive örümceklerinin gözünden kaçabilir. Bu gibi durumlarda takip edilmesini istediğiniz özel kaynakları belirtebilirsiniz.</div>
                    <div class="card-content d-flex justify-content-between">
                        <button type="button" class="btn red lighten-2 btn-large waves-effect" data-steps="3" data-prev="true">
                            <i class="material-icons">arrow_back</i>
                        </button>
                        <button type="submit" class="btn cyan darken-2 btn-large waves-effect">Oluştur</button>
                    </div>
                </div>
            </div>

            <div class="center-align red-text hide p-2" data-name="alert" style="font-size: 20px;"></div>

            <div id="price" class="center-align">{{ config('formal.currency') }}<span data-name="price-total">0</span> +kdv <sub class="grey-text">/ ay</sub></div>
            <small class="grey-text d-table mx-auto">DENEME SONRASI ÖDEMENİZ GEREKEN FİYAT</small>
            <div class="grey-text text-darken-2 p-2">
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Organizasyon deneme süresi 1 gündür. Hizmetlerin kesintisizce devam edebilmesi için en geç 1 gün sonra paketinizi yenilemeniz gerekmektedir.')
                @endcomponent
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Ücretsiz ibaresi olan özelliklerin kullanımında organizasyon oluşturma zorunluluğu yoktur.')
                @endcomponent
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Hizmet sonlandırıldıktan sonra, ücretsiz özellikleri kesintisiz olarak kullanmaya devam edebilirsiniz.')
                @endcomponent
            </div>
        </form>
    @else
        <div class="olive-alert warning">
            <div class="anim"></div>
            <h4 class="mb-2">GSM Ekleyin</h4>
            <p>Bir çok ücretsiz özelliği organizasyon oluşturmadan da kullanabilirsiniz. Ancak tüm özelliklerden faydalanmak için bir organizasyon oluşturmanız gerekiyor.</p>
            <p class="mb-2">Organizasyon oluşturabilmek için öncelikle bir GSM numarası eklemeniz gerekiyor.</p>
            <a href="{{ route('settings.mobile') }}" class="btn-flat waves-effect">GSM Ekle</a>
        </div>
    @endif
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
        var pin_group_limit_input = $('input[name=pin_group_limit]');

        var min_data_select = null;
        var min_pool_select = null;
        var min_historical_days = null;
        var min_saved_searches = null;
        var min_pin_groups = null;

        var module_real_time = $('input[name=module_real_time]:checked');
        var module_search = $('input[name=module_search]:checked');
        var module_alarm = $('input[name=module_alarm]:checked');

        if (module_real_time.length)
        {
            min_data_select = 1;
            min_pool_select = 1;
            min_historical_days = 1;
            min_saved_searches = 1;
            min_pin_groups = 0;
        }

        if (module_search.length)
        {
            min_data_select = 1;
            min_pool_select = 1;
            min_historical_days = 1;
            min_saved_searches = 0;
            min_pin_groups = 0;
        }

        if (module_alarm.length)
        {
            min_data_select = 1;
            min_pool_select = 1;
            min_historical_days = 1;
            min_saved_searches = 1;
            min_pin_groups = 0;
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

        if (min_pin_groups !== null)
        {
            pin_group_limit_input.attr('min', min_pin_groups)
                                 .val(pin_group_limit_input.val() < min_pin_groups ? min_pin_groups : pin_group_limit_input.val())
                                 .removeAttr('disabled')
        }
        else
        {
            pin_group_limit_input.attr('min', 0).val(0).attr('disabled', 'disabled')
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
