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
            <p class="mb-2">Organizasyonunuz aktif edildi. İyi araştırmalar dileriz...</p>
            <a href="{{ route('dashboard') }}" class="btn-flat waves-effect">Ana Sayfa</a>
            <a href="{{ route('settings.organisation') }}" class="btn green waves-effect">Organizasyon</a>
        </div>
        <form class="json" method="post" action="{{ route('organisation.create.offer') }}" data-callback="__create" id="offer">
            <div class="d-flex mx-auto" style="max-width: 400px;">
                <div class="flex-fill card card-unstyled" data-step="1">
                    <div class="card-content card-step">
                        <span class="step">1/4</span>
                        <span class="title">Modül Seçimi</span>
                        <a
                            href="#"
                            data-tooltip="Bilgi"
                            data-position="right"
                            data-trigger="info"
                            data-title="Modüller">
                            <i class="material-icons">info</i>
                            <div class="hide" data-helper>
                                <span style="font-size: 16px;">Olive 4 ana modülden oluşur.</span>
                            </div>
                        </a>
                    </div>
                    <ul class="collection collection-hoverable">
                        @foreach (config('system.static_modules') as $key => $module)
                            <li class="collection-item d-flex justify-content-between">
                                <label class="align-self-center">
                                    <input
                                        data-update
                                        data-option="module"
                                        data-unit-price="{{ $prices['unit_price.'.$key]['value'] }}"
                                        name="{{ $key }}"
                                        id="{{ $key }}"
                                        value="on"
                                        type="checkbox" />
                                    <span>{{ $module }} {!! $prices['unit_price.'.$key]['value'] ? '' : '<sup class="red-text">Ücretsiz</sup>' !!}</span>
                                </label>
                                <a
                                    href="#"
                                    class="align-self-center"
                                    data-tooltip="Bilgi"
                                    data-position="right"
                                    data-trigger="info"
                                    data-title="{{ $module }}">
                                    <i class="material-icons">info</i>
                                    <div class="hide" data-helper>
                                        @if ($key == 'module_real_time')
                                            <span style="font-size: 16px;">Geniş çaplı filtreleme özellikleri ve kullanıcı dostu arayüzü ile gündemdeki paylaşımları eş zamanlı bir akışa alarak takip etmenizi sağlar.</span>
                                        @elseif ($key == 'module_search')
                                            <span style="font-size: 16px;">Geniş çaplı filtreleme özellikleri ile gerçek zamanlı veya geçmişe yönelik aramalar yapabilmenizi sağlar. Ayrıca kolay ve hızlı bir şekilde grafikler oluşturabilir, kitlenizi ölçümleyebilirsiniz.</span>
                                        @elseif ($key == 'module_trend')
                                            <span style="font-size: 16px;">Eş zamanlı veya geçmişe yönelik trend kelime, kullanıcı veya başlık takibi, genel veya sektörel kullanıcı listeleri.</span>
                                        @elseif ($key == 'module_alarm')
                                            <span style="font-size: 16px;">Bilgisayar başında harcayacak vaktiniz yoksa ve konuşulanlardan eş zamanlı haberdar olmak istiyorsanız alarm modülünü kullanabilirsiniz.</span>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="card-content d-flex justify-content-end">
                        <a href="#" class="btn-flat btn-large waves-effect" data-steps="2">
                            <i class="material-icons">arrow_forward</i>
                        </a>
                    </div>
                </div>
                <div class="flex-fill card card-unstyled hide" data-step="2">
                    <div class="card-content card-step">
                        <span class="step">2/4</span>
                        <span class="title">Veri Kaynakları</span>
                        <a
                            href="#"
                            data-tooltip="Bilgi"
                            data-position="right"
                            data-trigger="info"
                            data-title="Veri Kaynakları">
                            <i class="material-icons">info</i>
                            <div class="hide" data-helper>
                                <span class="font-size: 16px;">Canlı Akış, Arama ve Alarm modülleri için en az 1 kaynak seçilmelidir.</span>
                            </div>
                        </a>
                    </div>
                    <ul class="collection collection-hoverable" data-tab="source">
                        @foreach (config('system.modules') as $key => $module)
                            <li class="collection-item d-flex justify-content-between">
                                <label class="align-self-center">
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
                                <a
                                    href="#"
                                    class="align-self-center"
                                    data-tooltip="Bilgi"
                                    data-position="right"
                                    data-trigger="info"
                                    data-title="{{ $module }}">
                                    <i class="material-icons">info</i>
                                    <div class="hide" data-helper>
                                        @if ($key == 'twitter')
                                            <span style="font-size: 16px;">Twitter'ın açık kaynak Türkçe paylaşımlarına erişim.</span>
                                        @elseif ($key == 'sozluk')
                                            <span style="font-size: 16px;">Türkçe sözlük siteleri paylaşımlarına erişim.</span>
                                        @elseif ($key == 'news')
                                            <span style="font-size: 16px;">Yerel ve genel Türkçe haber siteleri paylaşımlarına erişim.</span>
                                        @elseif ($key == 'blog')
                                            <span style="font-size: 16px;">Türkçe blog siteleri paylaşımlarına erişim.</span>
                                        @elseif ($key == 'instagram')
                                            <span style="font-size: 16px;">Instagram'ın açık kaynak Türkçe paylaşımlarına erişim.</span>
                                        @elseif ($key == 'youtube_video')
                                            <span style="font-size: 16px;">YouTube'un açık kaynak Türkçe paylaşımlarına erişim.</span>
                                        @elseif ($key == 'youtube_comment')
                                            <span style="font-size: 16px;">YouTube'un açık kaynak Türkçe video yorumlarına erişim.</span>
                                        @elseif ($key == 'shopping')
                                            <span style="font-size: 16px;">Belirli ikinci el e-ticaret sitelerinin paylaşımlarına erişim.</span>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="card-content d-flex justify-content-between">
                        <a href="#" class="btn-flat btn-large waves-effect" data-steps="1" data-prev="true">
                            <i class="material-icons">arrow_back</i>
                        </a>
                        <a href="#" class="btn-flat btn-large waves-effect" data-steps="3">
                            <i class="material-icons">arrow_forward</i>
                        </a>
                    </div>
                </div>
                <div class="flex-fill card card-unstyled hide" data-step="3">
                    <div class="card-content card-step">
                        <span class="step">3/4</span>
                        <span class="title">Limitler</span>
                    </div>
                    <div class="card-content">
                        <div class="d-flex justify-content-between">
                            <small class="d-flex">
                                <span class="align-self-center mr-1">Kullanıcı Kapasitesi</span>
                                <a
                                    href="#"
                                    class="align-self-center"
                                    data-tooltip="Bilgi"
                                    data-position="right"
                                    data-trigger="info"
                                    data-title="Pin Grubu">
                                    <i class="material-icons tiny">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">Olive ekip (organizasyon) yöntemi ile kullanılır. En az 1 kişilik bir organizasyona sahip olmalısınız.</span>
                                    </div>
                                </a>
                            </small>
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
                                    <i class="material-icons tiny">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">İlgilendiğiniz içerikleri gruplar halinde saklayabilir ve istediğiniz zaman çıktılarını alabilirsiniz.</span>
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
                                    <i class="material-icons tiny">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">Arama, Alarm ve Akış modülü kullanmak istiyorsanız en az 1 gün "Geçmişe Yönelik Arama" seçimi yapmalısınız.</span>
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
                                    <i class="material-icons tiny">info</i>
                                    <div class="hide" data-helper>
                                        <span style="font-size: 16px;">Aramalarınızı kaydederek diğer modüllerde tekrar kullanabilirsiniz. Canlı Akış ve Alarm bölümünü kullanabilmek için en az 1 "Arama Kaydetme" seçimi yapmalısınız.</span>
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
                        <a href="#" class="btn-flat btn-large waves-effect" data-steps="2" data-prev="true">
                            <i class="material-icons">arrow_back</i>
                        </a>
                        <a href="#" class="btn-flat btn-large waves-effect" data-steps="4">
                            <i class="material-icons">arrow_forward</i>
                        </a>
                    </div>
                </div>
                <div class="flex-fill card card-unstyled hide" data-step="4">
                    <div class="card-content card-step">
                        <span class="step">4/4</span>
                        <span class="title">Kaynak Takibi</span>
                        <a
                            href="#"
                            class="align-self-center"
                            data-tooltip="Bilgi"
                            data-position="left"
                            data-trigger="info"
                            data-title="Kaynak Takibi">
                            <i class="material-icons">info</i>
                            <div class="hide" data-helper>
                                <span style="font-size: 16px;">Olive tüm aktif veri kaynaklarını takip eder. Gündem dışı kalan takip edilmesini istediğiniz kaynakları Olive'e tanımlayabilirsiniz.</span>
                            </div>
                        </a>
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
                    <div class="card-content d-flex justify-content-between">
                        <a href="#" class="btn-flat btn-large waves-effect" data-steps="3" data-prev="true">
                            <i class="material-icons">arrow_back</i>
                        </a>
                        <button type="submit" class="btn-flat btn-large waves-effect">Oluştur</button>
                    </div>
                </div>
            </div>

            <div id="price" class="center-align">{{ config('formal.currency') }}<span data-name="price-total">0</span> +kdv <sub class="grey-text">/ ay</sub></div>
            <div class="grey-text text-darken-2 p-2">
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Organizasyon deneme süresi 1 gündür. Hizmetlerin kesintisizce devam edebilmesi için en geç 1 gün sonra paketinizi yenilemeniz gerekmektedir.')
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
        }
        else
        {
            source_inputs.addClass('disabled').attr('disabled', 'disabled').prop('checked', false)
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
                    'html': buttons.ok
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
        }
    }
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush
