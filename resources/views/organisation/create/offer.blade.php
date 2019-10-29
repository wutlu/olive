@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Organizasyon Oluştur'
        ]
    ]
])

@push('local.scripts')
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

@section('content')
    @if ($user->gsm_verified_at)
        <div class="olive-alert success hide">
            <div class="anim"></div>
            <h4 class="mb-2">Organizasyon Oluşturuldu!</h4>
            <p class="mb-2">Organizasyonunuz aktif edildi. İyi araştırmalar dileriz...</p>
            <a href="{{ route('dashboard') }}" class="btn green waves-effect">Ana Sayfa</a>
            <a href="{{ route('settings.organisation') }}" class="btn green waves-effect">Organizasyon</a>
        </div>
        <form class="json" method="post" action="{{ route('organisation.create.offer') }}" data-callback="__create" id="offer">
            <div class="d-table mx-auto">
                <div class="d-flex" style="min-width: 300px;">
                    <div class="card card-unstyled flex-fill" data-step="1">
                        <div class="card-content card-step">
                            <span class="step">1/4</span>
                            <span class="title">Modül Seçimi</span>
                        </div>
                        <div class="card-content">
                            @foreach (config('system.static_modules') as $key => $module)
                                <label class="d-flex">
                                    <a
                                        href="#"
                                        class="btn-floating btn-flat mr-1 waves-effect waves-teal align-self-center"
                                        data-trigger="info"
                                        data-title="{{ $module }}">
                                        <i class="material-icons teal-text">info</i>
                                        <div class="hide" data-helper>
                                            @if ($key == 'module_real_time')
                                                <span>Geniş çaplı filtreleme özellikleri ve kullanıcı dostu arayüzü ile gündemdeki paylaşımları eş zamanlı takip etmenizi sağlar.</span>
                                            @elseif ($key == 'module_search')
                                                <span>Geniş çaplı filtreleme özellikleri ile gerçek zamanlı veya geçmişe yönelik aramalar yapmanızı sağlar. Ayrıca kolay ve hızlı grafikler ile kitlenizi ölçümleyebilirsiniz.</span>
                                            @elseif ($key == 'module_trend')
                                                <span>Eş zamanlı veya geçmişe yönelik trend takibi, genel veya sektörel kullanıcı listeleri. (Veri kaynağı gerektirmez!)</span>
                                            @elseif ($key == 'module_alarm')
                                                <span>Bilgisayar başında harcayacak vaktiniz yoksa, konuşulanlardan haberdar olmak için eş zamanlı alarmlar oluşturabilirsiniz.</span>
                                            @endif
                                        </div>
                                    </a>
                                    <input
                                        data-update
                                        name="{{ $key }}"
                                        id="{{ $key }}"
                                        value="on"
                                        data-unit-price="{{ $prices['unit_price.'.$key]['value'] }}"
                                        type="checkbox" />
                                    <span class="align-self-center">{{ $module }} {!! $prices['unit_price.'.$key]['value'] ? '' : '<sup class="red-text">Ücretsiz</sup>' !!}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="card-content d-flex justify-content-end">
                            <a href="#" class="btn-flat btn-large waves-effect" data-steps="2">
                                <i class="material-icons">arrow_forward</i>
                            </a>
                        </div>
                    </div>
                    <div class="card card-unstyled flex-fill hide" data-step="2">
                        <div class="card-content card-step">
                            <span class="step">2/4</span>
                            <span class="title">Veri Kaynakları</span>
                            <a
                                href="#"
                                class="btn-floating btn-flat mr-1 waves-effect align-self-center"
                                data-trigger="info"
                                data-title="Veri Kaynakları">
                                <i class="material-icons grey-text text-darken-2">info</i>
                                <div class="hide" data-helper>Canlı Akış, Arama ve Alarm modülleri için en az 1 kaynak seçilmelidir.</div>
                            </a>
                        </div>
                        <div class="card-content">
                            @foreach (config('system.modules') as $key => $module)
                                <label class="d-flex">
                                    <a
                                        href="#"
                                        class="btn-floating btn-flat mr-1 waves-effect waves-teal align-self-center"
                                        data-trigger="info"
                                        data-title="{{ $module }}">
                                        <i class="material-icons teal-text">info</i>
                                        <div class="hide" data-helper>
                                            @if ($key == 'twitter')
                                                <span>Twitter'ın açık kaynak Türkçe paylaşımlarına erişim.</span>
                                            @elseif ($key == 'sozluk')
                                                <span>Türkçe sözlük siteleri paylaşımlarına erişim.</span>
                                            @elseif ($key == 'news')
                                                <span>Yerel ve genel Türkçe haber siteleri paylaşımlarına erişim.</span>
                                            @elseif ($key == 'blog')
                                                <span>Türkçe blog siteleri paylaşımlarına erişim.</span>
                                            @elseif ($key == 'instagram')
                                                <span>Instagram'ın açık kaynak Türkçe paylaşımlarına erişim.</span>
                                            @elseif ($key == 'youtube_video')
                                                <span>YouTube'un açık kaynak Türkçe paylaşımlarına erişim.</span>
                                            @elseif ($key == 'youtube_comment')
                                                <span>YouTube'un açık kaynak Türkçe video yorumlarına erişim.</span>
                                            @elseif ($key == 'shopping')
                                                <span>Belirlenmiş ikinci el e-ticaret sitelerinin paylaşımlarına erişim.</span>
                                            @endif
                                        </div>
                                    </a>
                                    <input
                                        data-update
                                        name="data_{{ $key }}"
                                        id="data_{{ $key }}"
                                        value="on"
                                        data-unit-price="{{ $prices['unit_price.data_'.$key]['value'] }}"
                                        type="checkbox" />
                                    <span class="align-self-center">{{ $module }} {!! $prices['unit_price.data_'.$key]['value'] ? '' : '<sup class="red-text">Ücretsiz</sup>' !!}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="card-content d-flex justify-content-between">
                            <a href="#" class="btn-flat btn-large waves-effect" data-steps="1" data-prev="true">
                                <i class="material-icons">arrow_back</i>
                            </a>
                            <a href="#" class="btn-flat btn-large waves-effect" data-steps="3">
                                <i class="material-icons">arrow_forward</i>
                            </a>
                        </div>
                    </div>
                    <div class="card card-unstyled flex-fill hide" data-step="3">
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
                                        data-trigger="info"
                                        data-title="Pin Grubu">
                                        <i class="material-icons tiny grey-text text-darken-2">info</i>
                                        <div class="hide" data-helper>İlgilendiğiniz içerikleri gruplar halinde saklayabilir ve istediğiniz zaman çıktılarını alabilirsiniz.</div>
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
                            <!--
                            <div class="d-flex justify-content-between">
                                <small class="d-flex">
                                    <span class="align-self-center mr-1">Analiz Araçları</span>
                                    <a
                                        href="#"
                                        class="align-self-center"
                                        data-trigger="info"
                                        data-title="Analiz Araçları">
                                        <i class="material-icons tiny grey-text text-darken-2">info</i>
                                        <div class="hide" data-helper>İlgilendiğiniz sosyal medya kullanıcılarını takibe alarak, hesap aktivitelerini inceleyebilirsiniz.</div>
                                    </a>
                                </small>
                                <small>100</small>
                            </div>
                            <div class="range-field">
                                <input
                                    data-update
                                    data-unit-price="{{ $prices['unit_price.analysis_tools_limit']['value'] }}"
                                    name="analysis_tools_limit"
                                    id="analysis_tools_limit"
                                    max="100"
                                    min="0"
                                    value="0"
                                    type="range" />
                            </div>
                            -->
                            <div class="d-flex justify-content-between">
                                <small class="d-flex">
                                    <span class="align-self-center mr-1">Geçmişe Yönelik Arama (Gün)</span>
                                    <a
                                        href="#"
                                        class="align-self-center"
                                        data-trigger="info"
                                        data-title="Geçmişe Yönelik Arama">
                                        <i class="material-icons tiny grey-text text-darken-2">info</i>
                                        <div class="hide" data-helper>Arama modülü kullanmak istiyorsanız en az 1 gün "Geçmişe Yönelik Arama" seçimi yapmalısınız.</div>
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
                                        data-trigger="info"
                                        data-title="Arama Kaydetme">
                                        <i class="material-icons tiny grey-text text-darken-2">info</i>
                                        <div class="hide" data-helper>Aramalarınızı kaydederek diğer modüllerde tekrar kullanabilirsiniz. Canlı Akış ve Alarm bölümünü kullanabilmek için en az 1 "Arama Kaydetme" seçimi yapmalısınız.</div>
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
                    <div class="card card-unstyled flex-fill hide" data-step="4">
                        <div class="card-content card-step">
                            <span class="step">4/4</span>
                            <span class="title">Ekstra Kaynak Takibi</span>
                            <a
                                href="#"
                                class="btn-floating btn-flat mr-1 waves-effect align-self-center"
                                data-trigger="info"
                                data-title="Ekstra Kaynak Takibi">
                                <i class="material-icons grey-text text-darken-2">info</i>
                                <div class="hide" data-helper>Olive tüm aktif gündemi takip eder. Olive'in gündem dışı takip etmesini istediğiniz kaynakları Olive'e tanımlayabilirsiniz.</div>
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
            </div>

            <div id="price" class="center-align">{{ config('formal.currency') }}<span data-name="price-total">0</span> +kdv <sub class="grey-text">/ ay</sub></div>
            <div class="grey-text text-darken-2 p-2">
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Organizasyon oluşturulduktan bir kaç dakika sonra aktif edilir. Ödeme en geç 1 gün sonra gerçekleştirilmezse, hizmet sonlandırılır.')
                @endcomponent
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Hizmet sonlandırıldıktan sonra, ücretsiz özellikleri kullanmaya devam edebilirsiniz.')
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
@endpush

@push('local.scripts')
    $(document).on('change', 'input[type=range]', function() {
        range_function($(this))
    })

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

        $('[data-step]').hide(__.data('prev') ? 'fade' : 'slide', {}, 200, function() {
            $(this).addClass('hide')
        })

        $('[data-step=' + next + ']').removeClass('hide').hide().show('slide', {}, 200)
    })

    calculate()

    $(document).on('change keydown keyup', 'input[data-update]', calculate)

    function calculate()
    {
        var total_price = parseInt((math_prices() + single_prices()) + ($('input[name=user_capacity]').val() * {{ $prices['unit_price.user']['value'] }}));
            total_price = total_price - {{ $prices['unit_price.user']['value'] }};

        $('[data-name=price-total]').html((total_price).toFixed(2))
    }
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush
