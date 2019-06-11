@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama Motoru'
        ]
    ],
    'wide' => true,
    'pin_group' => true,
    'dock' => true
])

@push('local.styles')
    #search-operators {
        display: none;

        padding: 1rem;
    }

    #search-area {
        border-width: 0 0 1px;
        border-style: solid;
        border-color: #e1e1e1;
    }
    #search-area [data-trigger] {
        padding: 0 1rem;

        border-width: 0 1px 0 0;
        border-style: solid;
        border-color: #e1e1e1;

        display: table;

        -webkit-transition: all 200ms cubic-bezier(.25, .46, .45, .94);
                transition: all 200ms cubic-bezier(.25, .46, .45, .94);
    }
    #search-area [data-trigger]:active {
        -webkit-box-shadow: inset 0 0 .4rem 0 rgba(0, 0, 0, .1);
                box-shadow: inset 0 0 .4rem 0 rgba(0, 0, 0, .1);
    }
    #search-area #string {
        margin: 0;
        padding: 1rem;
        border-width: 0;

        -webkit-box-shadow: none;
                box-shadow: none;
    }

    #date-area > .d-flex input[type=date] {
        border-width: 0 !important;

        margin: 0 12px !important;
        max-width: calc(50% - 56px);

        -webkit-box-shadow: none !important;
                box-shadow: none !important;
    }

    #date-area > .d-flex [data-target=date-menu] {
        width: 52px;

        margin: 0 12px 0 0;

        text-align: center;
    }

    @media (max-width: 700px) {
        #date-area > .d-flex {
            width: 100%;
        }
    }

    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }
@endpush

@push('local.scripts')
    $('.owl-chips').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide',
        navText: [
            '<div class="nav-btn prev-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_left</i></div>',
            '<div class="nav-btn next-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_right</i></div>'
        ],
        nav: true
    })

    $(document).on('click', '[data-trigger=clear]', function() {
        $('input[name=string]').val('').hide().show( 'highlight', { 'color': '#f0f4c3' }, 400 ).focus();
    }).on('click', '[data-search]', function() {
        var __ = $(this);
        var input = $('input[name=string]');
        var array = [];

        var string = __.data('search');

        if (__.data('validate'))
        {
            var promt_message = 'Değer girin:';

            if (__.data('validate') == 'number')
            {
                promt_message = promt_message + ' "< küçüktür" veya "> büyüktür" kullanabilirsiniz. ';
            }

            var prompt_value = prompt(promt_message);

            if (!prompt_value)
            {
                M.toast({
                    html: 'Değer girmediniz!',
                    classes: 'red darken-2'
                }, 200)

                $('input[name=string]').focus()

                return false;
            }

            if (__.data('validate') == 'number')
            {
                var _int = prompt_value ? prompt_value.replace('<', '').replace('>', '') : '';

                if (!Number.isInteger(+_int))
                {
                    M.toast({
                        html: 'Değer, nümerik olmalıdır!',
                        classes: 'red darken-2'
                    }, 200)

                    $('input[name=string]').focus()

                    return false;
                }

                string = string + ':' + prompt_value;
            }
            else if (__.data('validate') == 'string')
            {
                if (string == '+' || string == '-')
                {
                    string = string + '"' + prompt_value + '"';
                }
                else
                {
                    string = string + ':"' + prompt_value + '"';
                }
            }
        }

        array.push(input.val())
        array.push(string)

        input.val(input.val() ? array.join(' && ') : string).focus()

        setTimeout(function() {
            // trigger
        }, 400)
    }).on('keyup', 'input[name=string]', function(e) {
        var __ = $(this),
            keycode = (e.keyCode ? e.keyCode : e.which);

        if (keycode == '13')
        {
            //
        }

        operator('clear')
    }).on('focus click', 'input[name=string]', function() {
        operator('open')
    }).on('blur', 'input[name=string]', function() {
        operator('close')
    }).on('change', '[data-update]', function() {
        var search = $('ul#search');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    }).on('click', '[data-update-click]', function() {
        var search = $('ul#search');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })

    var operatorTimer;

    function operator(status)
    {
        var selector = $('#search-operators');

        window.clearTimeout(operatorTimer);

        if (status == 'open')
        {
            selector.slideDown(200);
        }
        else if (status == 'close')
        {
            operatorTimer = window.setTimeout(function() {
                selector.slideUp(200);
            }, 1000)
        }
        else if (status == 'fast-close')
        {
            selector.slideUp(200);
        }
    }

    function chip(__)
    {
        var id = hashCode(__.val());

        if (!$('.owl-chips').find('.chip[data-id=' + id + ']').length && __.val().length)
        {
            $('.owl-chips').trigger('add.owl.carousel', [ $('<a />', {
                'href': '#',
                'class': 'chip grey darken-2 waves-effect white-text mb-0',
                'data-search': __.val(),
                'html': __.val(),
                'data-id': id
            }), 0 ]).trigger('refresh.owl.carousel')
        }
    }

    $(window).on('load', function() {
        var input = $('input[name=string]');

        if (input.val().length)
        {
            vzAjax($('ul#search'))
            chip(input)
        }
    })
@endpush

@section('wildcard')
    <div class="d-flex" id="search-area">
        <a href="#" class="flex-fill d-flex" data-trigger="clear">
            <i class="material-icons align-self-center">clear</i>
        </a>
        <a
            href="#"
            class="flex-fill d-flex json"
            data-trigger="save"
            data-href="{{ route('search.save') }}">
            <i class="material-icons align-self-center">save</i>
        </a>
        <input
            type="text"
            name="string"
            id="string"
            placeholder="Arayın"
            class="json json-search"
            data-json-target="ul#search" />
    </div>
    <div id="search-operators">
        <div class="d-flex flex-wrap">
            <div class="p-1">
                Genel
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="+">+Olsun</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="-">-Olmasın</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="site.id">Site / Sözlük Id</button>
            </div>

            <div class="p-1">
                Sözlük Filtreleri
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="author">Yazar Adı</button>
            </div>

            <div class="p-1">
                Twitter Filtreleri (Kullanıcı)
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="user.screen_name">Kullanıcı Adı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.id">Kullanıcı Id</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="user.verified:true">Doğrulanmış Hesaplar</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.statuses">Tweet Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.favourites">Favori Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.listed">Liste Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.friends">Takip Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.followers">Takipçi Sayısı</button>
            </div>

            <div class="p-1">
                Twitter Filtreleri (Tweet)
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.hashtag">Hashtag Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.mention">Mention Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.url">Bağlantı Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.media">Medya Sayısı</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!_exists_:external.type">Sadece Tweetler</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="external.type:quote">Sadece Alıntılar</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="external.type:reply">Sadece Cevaplar</button>
            </div>

            <div class="p-1">
                YouTube Filtreleri
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="channel.title">Kanal Başlığı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="channel.id">Kanal Id</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="video_id">Video Id</button>
            </div>
        </div>
    </div>

    @if (@$trends)
        <div class="owl-chips owl-carousel grey lighten-4 z-depth-1">
            @foreach ($trends as $trend)
                <a data-update-click class="chip grey lighten-2 waves-effect mb-0" data-search="{{ $trend->data->key }}" href="#">{{ $trend->data->key }}</a>
            @endforeach
        </div>
    @endif
@endsection

@section('panel-icon', 'pie_chart')
@section('panel')
    <div class="collection collection-unstyled">
        <a href="#" class="collection-item">Saatlik Paylaşım</a>
        <a href="#" class="collection-item">Günlük Paylaşım</a>
        <a href="#" class="collection-item">Lokasyon</a>
        <a href="#" class="collection-item">Platform</a>
        <a href="#" class="collection-item">Duygu</a>
        <a href="#" class="collection-item">Soru, İstek, Şikayet ve Haber</a>
        <a href="#" class="collection-item">İllegal Grafik</a>
        <a href="#" class="collection-item">Cinsiyet Dağılımı</a>
        <a href="#" class="collection-item">@Bahsedenler</a>
        <a href="#" class="collection-item">#Hashtagler</a>
    </div>
@endsection

@push('wildcard-bottom')
    <div id="date-area" class="d-flex justify-content-end grey lighten-4">
        <div class="lighten-4 grey-text mr-auto pl-1 align-self-center  hide-on-med-and-down" data-name="stats"></div>
        <div class="d-flex align-self-center">
            <input data-update type="date" class="align-self-center" name="start_date" value="{{ $s ? $s : date('Y-m-d', strtotime('-1 day')) }}" placeholder="Başlangıç" />
            <input data-update type="date" class="align-self-center" name="end_date" value="{{ $e ? $e : date('Y-m-d') }}" placeholder="Bitiş" />

            <a href="#" class="btn-flat waves-effect dropdown-trigger align-self-center" data-target="date-menu" data-align="right">
                <i class="material-icons">date_range</i>
            </a>
        </div>
    </div>

    <ul id="date-menu" class="dropdown-content">
        <li>
            <a
                href="#"
                class="collection-item waves-effect"
                data-update-click
                data-input="input[name=end_date]"
                data-focus="input[name=start_date]"
                data-input-value="{{ date('Y-m-d') }}"
                data-value="{{ date('Y-m-d') }}">Bugün</a>
        </li>
        @if ($organisation->historical_days >= 1)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d', strtotime('-1 day')) }}"
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Dün</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 2)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Son 2 Gün</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 7)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-7 day')) }}">Son 7 Gün</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 30)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-30 day')) }}">Son 30 Gün</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 90)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-90 day')) }}">Son 90 Gün</a>
            </li>
        @endif
    </ul>
@endpush

@push('local.scripts')
    function __search_archive(__, obj)
    {
        var ul = $('ul#search');

        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            $('[data-name=stats]').html('Yaklaşık ' + obj.stats.hits + ' sonuç bulundu (' + obj.stats.took + ' saniye)').removeClass('hide');

            $('[data-name=twitter-tweet]').html(obj.stats.counts.twitter_tweet);
            $('[data-name=sozluk-entry]').html(obj.stats.counts.sozluk_entry);
            $('[data-name=youtube-video]').html(obj.stats.counts.youtube_video);
            $('[data-name=youtube-comment]').html(obj.stats.counts.youtube_comment);
            $('[data-name=media-article]').html(obj.stats.counts.media_article);
            $('[data-name=shopping-product]').html(obj.stats.counts.shopping_product);

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                    var model;

                        switch(o._type)
                        {
                            case 'tweet'  : model = _tweet_  (o); break;
                            case 'entry'  : model = _entry_  (o); break;
                            case 'article': model = _article_(o); break;
                            case 'product': model = _product_(o); break;
                            case 'comment': model = _comment_(o); break;
                            case 'video'  : model = _video_  (o); break;
                        }

                        model.find('.text-area').mark(obj.words, {
                            'element': 'span',
                            'className': 'marked yellow black-text',
                            'accuracy': 'complementary'
                        })

                        item.html(model).appendTo(ul)
                })
            }

            operator('fast-close')

            $('.tabs').tabs('select', 'search-tab');
        }
    }
@endpush

@section('content')
    <div class="row">
        <div class="col s12 m12 l12 xl8">
            <div class="card card-unstyled">
                <div class="card-tabs">
                    <ul class="tabs">
                        <li class="tab">
                            <a class="active" href="#search-tab">Arama Sonuçları</a>
                        </li>
                        <li class="tab">
                            <a href="#chart-tab">Grafikler</a>
                        </li>
                    </ul>
                </div>
                <div class="card-content">
                    <div id="search-tab">
                        <ul class="collection json-clear loading" 
                            id="search"
                            data-href="{{ route('search.dashboard') }}"
                            data-skip="0"
                            data-more-button="#search-more_button"
                            data-callback="__search_archive"
                            data-method="post"
                            data-include="start_date,end_date,modules,string,reverse,take,gender,sentiment_pos,sentiment_neu,sentiment_neg,sentiment_hte,consumer_que,consumer_req,consumer_cmp,consumer_nws,illegal"
                            data-nothing>
                            <li class="collection-item nothing">
                                @component('components.alert')
                                    @slot('icon', 'info')
                                    @slot('text', 'Hiç sonuç bulunamadı.')
                                @endcomponent
                            </li>
                            <li class="collection-item model hide"></li>
                        </ul>

                        <a href="#"
                           class="more hide json"
                           id="search-more_button"
                           data-json-target="ul#search">Daha Fazla</a>
                    </div>
                    <div id="chart-tab">
                        @component('components.alert')
                            @slot('icon', 'info')
                            @slot('text', 'Grafik oluşturmak için bir analiz sorgusu çalıştırın.')
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
        <div class="col hide-on-med-and-down hide-on-large-only show-on-extra-large xl4">
            <div class="banner mb-1 lighten-4 z-depth-1 hide" style="background-image: url('{{ asset('img/photo/city.jpg') }}');">
                <p class="white-text">Bu konu genellikle <span class="white cyan-text text-darken-2">Ankara</span> bölgesinden konuşuldu.</p>
                <div class="banner-overlay cyan"></div>
            </div>
            <div class="banner mb-1 lighten-4 z-depth-1 hide" style="background-image: url('{{ asset('img/photo/women.jpg') }}');">
                <p class="white-text">Görünen o ki, ilgilendiğiniz konu <span class="white pink-text">56%</span> oranla kadın kullanıcıları ilgilendiriyor.</p>
                <div class="banner-overlay pink"></div>
            </div>
            <div class="banner mb-1 lighten-4 z-depth-1 hide" style="background-image: url('{{ asset('img/photo/hate.jpg') }}');">
                <p class="white-text">Bu konu çok fazla nefret söylemi içeriyor.</p>
                <div class="banner-overlay black"></div>
            </div>

            <div class="banner-4 mb-1">
                <div class="banner-item tweet">
                    <strong data-name="twitter-tweet">0</strong> tweet
                </div>
                <div class="banner-item entry">
                    <strong data-name="sozluk-entry">0</strong> entry
                </div>
                <div class="banner-item article">
                    <strong data-name="media-article">0</strong> haber
                </div>
                <div class="banner-item video">
                    <strong data-name="youtube-video">0</strong> video
                </div>
                <div class="banner-item video-comment">
                    <strong data-name="youtube-comment">0</strong> video yorumu
                </div>
                <div class="banner-item product">
                    <strong data-name="shopping-product">0</strong> ilan
                </div>
            </div>

            <div class="grey-text mb-2">
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Yapay zeka motorumuz, belirli oranlarda tahminler gerçekleştirir. Aralık değerleri ile bu tahminleri istediğiniz değerlerde filtreleyebilirsiniz.')
                @endcomponent
            </div>

            <div class="d-flex">
                <p class="range-field">
                    Pozitif
                    <input data-update name="sentiment_pos" type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Nötr
                    <input data-update name="sentiment_neu" type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Negatif
                    <input data-update name="sentiment_neg" type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Nefret Söylemi
                    <input data-update name="sentiment_hte" type="range" min="0" max="9" value="0" />
                </p>
            </div>

            <div class="d-flex">
                <p class="range-field">
                    Soru
                    <input data-update name="consumer_que" type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    İstek
                    <input data-update name="consumer_req" type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Şikayet
                    <input data-update name="consumer_cmp" type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Haber
                    <input data-update name="consumer_nws" type="range" min="0" max="9" value="0" />
                </p>
            </div>

            <div class="d-flex">
                <label class="flex-fill">
                    <input name="gender" type="radio" data-update-click value="" checked />
                    <span>Hepsi</span>
                </label>
                <label class="flex-fill">
                    <input name="gender" type="radio" data-update-click value="female" />
                    <span>Kadın</span>
                </label>
                <label class="flex-fill">
                    <input name="gender" type="radio" data-update-click value="male" />
                    <span>Erkek</span>
                </label>
                <label class="flex-fill">
                    <input name="gender" type="radio" data-update-click value="unknown" />
                    <span>Bilinmeyen</span>
                </label>
            </div>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card card-unstyled mb-1">
        <div class="collection collection-unstyled">
            <label class="collection-item d-block">
                <input data-update name="illegal" value="on" type="checkbox" />
                <span>İllegal İçerikler Dahil</span>
            </label>
            <label class="collection-item d-block">
                <input data-update name="reverse" value="on" type="checkbox" />
                <span>İlk İçerikler</span>
            </label>
        </div>
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">device_hub</i>
                Kaynak
            </span>
        </div>
        <ul class="collection collection-unstyled collapsible">
            @foreach (config('system.modules') as $key => $module)
                <li class="collection-item">
                    <label>
                        <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                        <span>{{ $module }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">save</i>
                Kayıtlı Aramalar
            </span>
        </div>
        <ul class="collection collection-unstyled">
            <li class="collection-item d-flex justify-content-between">
                <a href="#" class="align-self-center">Örnek Arama</a>
                <a class="btn-floating btn-small waves-effect align-self-center white">
                    <i class="material-icons grey-text text-darken-2">delete_forever</i>        
                </a>
            </li>
        </ul>
    </div>
    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <div class="input-field">
                <select name="take" id="take">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label>Sayfalama</label>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $('select').formSelect()
    $('.tabs').tabs()
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush
