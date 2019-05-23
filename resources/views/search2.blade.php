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
    .qb-card select {
        width: auto;
    }
    .qb-card {
        margin: 1rem 0 !important;
        background-color: rgba(0, 0, 0, .02) !important;
    }
    .qb-card:hover {
        background-color: rgba(0, 0, 0, .04) !important;
    }
    .qb-card > .card-content {
    }
    .qb-card .qb-card {
        margin: -1px 0 0 0;
    }
    .qb-layout > .qb-rule .input-field {
        margin: 0;
    }
    .qb-layout:not(:empty) {
        padding: 1rem 0 0 0;
    }

    #search_builder {
        border-width: 1px 0 0;
        border-style: solid;
        border-color: #e1e1e1;
    }

    #search-area {}
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

            array.push(input.val())
            array.push(__.data('search'))

            input.val(input.val() ? array.join(' && ') : __.data('search')).focus()

        setTimeout(function() {
            // trigger
        }, 400)
    }).on('keyup', 'input[name=string]', function(e) {
        var __ = $(this),
            keycode = (e.keyCode ? e.keyCode : e.which);

        if (keycode == '13')
        {
            chip(__)
        }
    })

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

    _qb_group_create('#olive-query', { 'close': false })

    function _qb_group_create(layout, options)
    {
        var id = Math.floor(Math.random() * (99999 - 10000)) + 10000;
        var group = _qb_item('div', {
            'class': 'card card-unstyled qb-card',
            'id': 'group-' + id,
            'html': _qb_item('div', {
                'class': 'card-content',
                'html': [
                    _qb_item('div', {
                        'class': 'toolbar d-flex',
                        'html': [
                            _qb_item('span', {
                                'class': 'align-self-center mr-1',
                                'html': [
                                    _qb_item('radio', {
                                        'name': 'group-' + id,
                                        'value': 'and',
                                        'checked': true,
                                        'html': 'VE',
                                        'class': 'align-self-center mr-1'
                                    }),
                                    _qb_item('radio', {
                                        'name': 'group-' + id,
                                        'value': 'or',
                                        'html': 'VEYA',
                                        'class': 'align-self-center mr-1'
                                    })
                                ]
                            }),
                            _qb_item('span', {
                                'class': 'align-self-center d-flex ml-auto',
                                'html': [
                                    _qb_item('button', {
                                        'class': 'align-self-center btn-floating btn-flat btn-small waves-effect mr-1',
                                        'data-trigger': 'qb_group-create',
                                        'data-tooltip': 'Grup Oluştur',
                                        'data-position': 'left',
                                        'data-id': id,
                                        'data-layout': '#layout-' + id,
                                        'html': _qb_icon('group_work')
                                    }),
                                    _qb_item('button', {
                                        'class': 'align-self-center btn-floating btn-flat btn-small waves-effect mr-1',
                                        'data-trigger': 'qb_rule-create',
                                        'data-tooltip': 'Kural Oluştur',
                                        'data-position': 'left',
                                        'data-id': id,
                                        'html': _qb_icon('linear_scale')
                                    }),
                                    _qb_item('button', {
                                        'class': 'align-self-center btn-floating btn-flat btn-small waves-effect red-text hide',
                                        'data-trigger': 'qb_group-delete',
                                        'data-id': id,
                                        'html': _qb_icon('close')
                                    })
                                ]
                            })
                        ]
                    }),
                    _qb_item('div', {
                        'id': 'layout-' + id,
                        'class': 'qb-layout'
                    })
                ]
            })
        })

        if (options.close)
        {
            group.find('[data-trigger=qb_group-delete]').removeClass('hide')
        }

        $(layout).prepend(group)
    }

    $(document).on('click', '[data-trigger=qb_group-create]', function() {
        _qb_group_create($(this).data('layout'), { 'close': true })
    }).on('click', '[data-trigger=qb_group-delete]', function() {
        $('#group-' + $(this).data('id')).remove()
    }).on('click', '[data-trigger=qb_rule-create]', function() {
        var id = Math.floor(Math.random() * (99999 - 10000)) + 10000;

        var rule = _qb_item('div', {
            'id': 'rule-' + id,
            'class': 'qb-rule d-flex',
            'html': [
                _qb_item('select', {
                    'class': 'align-self-center browser-default mr-1',
                    'html': [
                        _qb_item('option', { 'html': 'Seçin', 'value': 'select', 'selected': true }),
                        _qb_item('option', { 'html': 'Kelime', 'value': 'keyword' }),
                        _qb_item('option', { 'html': 'Cümle', 'value': 'sentence' }),
                        _qb_item('option', { 'html': 'Sözlük', 'value': 'sozluk' }),
                        _qb_item('option', { 'html': 'Twitter', 'value': 'twitter' }),
                        _qb_item('option', { 'html': 'YouTube', 'value': 'youtube' }),
                        _qb_item('option', { 'html': 'E-ticaret', 'value': 'shopping' })
                    ]
                }),
                _qb_item('select', {
                    'name': 'keyword',
                    'class': 'align-self-center browser-default mr-1 hide',
                    'html': [
                        _qb_item('option', { 'html': 'Seçin', 'value': 'select', 'selected': true }),
                        _qb_item('option', { 'html': 'İçersin', 'value': true }),
                        _qb_item('option', { 'html': 'İçermesin', 'value': false })
                    ]
                }),
                _qb_item('select', {
                    'name': 'youtube',
                    'class': 'align-self-center browser-default mr-1 hide',
                    'html': [
                        _qb_item('option', { 'html': 'Seçin', 'value': 'select', 'selected': true }),
                        _qb_item('option', { 'html': 'Kanal Adı', 'value': 'channel.name' }),
                        _qb_item('option', { 'html': 'Kanal Id', 'value': 'channel.id' }),
                        _qb_item('option', { 'html': 'Video Id', 'value': 'video_id' })
                    ]
                }),
                _qb_item('select', {
                    'name': 'shopping',
                    'class': 'align-self-center browser-default mr-1 hide',
                    'html': [
                        _qb_item('option', { 'html': 'Seçin', 'value': 'select', 'selected': true }),
                        _qb_item('option', { 'html': 'Fiyat', 'value': 'price.amount' }),
                        _qb_item('option', { 'html': 'Para Birimi', 'value': 'price.currency' }),
                        _qb_item('option', { 'html': 'Site Id', 'value': 'site_id' }),
                    ]
                }),
                _qb_item('select', {
                    'name': 'sozluk',
                    'class': 'align-self-center browser-default mr-1 hide',
                    'html': [
                        _qb_item('option', { 'html': 'Seçin', 'value': 'select', 'selected': true }),
                        _qb_item('option', { 'html': 'Sözlük Id', 'value': 'site_id' }),
                        _qb_item('option', { 'html': 'Yazar Adı', 'value': 'author' }),
                        _qb_item('option', { 'html': 'Konu Başlığı', 'value': 'subject' })
                    ]
                }),
                _qb_item('select', {
                    'name': 'twitter',
                    'class': 'align-self-center browser-default mr-1 hide',
                    'html': [
                        _qb_item('option', { 'html': 'Seçin', 'value': 'select', 'selected': true }),
                        _qb_item('option', { 'html': 'Kullanıcı Adı', 'value': 'user.screen_name' }),
                        _qb_item('option', { 'html': 'Kullanıcı Id', 'value': 'user.id' }),
                        _qb_item('option', { 'html': 'Doğrulanmış Hesap', 'value': 'user.verified' }),
                        _qb_item('option', { 'html': '---', 'disabled': true }),
                        _qb_item('option', { 'html': 'ReTweet', 'value': 'external.retweet' }),
                        _qb_item('option', { 'html': 'Alıntı', 'value': 'external.quote' }),
                        _qb_item('option', { 'html': 'Cevap', 'value': 'external.reply' }),
                        _qb_item('option', { 'html': '---', 'disabled': true }),
                        _qb_item('option', { 'html': 'Tweet Hashtag Sayısı', 'value': 'counts.hashtag' }),
                        _qb_item('option', { 'html': 'Tweet Mention Sayısı', 'value': 'counts.mention' }),
                        _qb_item('option', { 'html': 'Tweet Bağlantı Sayısı', 'value': 'counts.link' }),
                        _qb_item('option', { 'html': 'Tweet Medya Sayısı', 'value': 'counts.media' }),
                        _qb_item('option', { 'html': '---', 'disabled': true }),
                        _qb_item('option', { 'html': 'Kullanıcı Tweet Sayısı', 'value': 'user.counts.statuses' }),
                        _qb_item('option', { 'html': 'Kullanıcı Favori Sayısı', 'value': 'user.counts.favourites' }),
                        _qb_item('option', { 'html': 'Kullanıcı Liste Sayısı', 'value': 'user.counts.listed' }),
                        _qb_item('option', { 'html': 'Kullanıcı Takipçi Sayısı', 'value': 'user.counts.friends' }),
                        _qb_item('option', { 'html': 'Kullanıcı Takip Sayısı', 'value': 'user.counts.followers' }),
                    ]
                }),
                _qb_item('text', {
                    'name': 'text',
                    'class': 'align-self-center mr-1 hide',
                }),
                _qb_item('number', {
                    'name': 'number',
                    'class': 'align-self-center mr-1 hide',
                    'max': 9,
                    'min': 1
                }),
                _qb_item('button', {
                    'class': 'align-self-center btn-floating btn-flat btn-small waves-effect red-text ml-auto',
                    'data-id': id,
                    'data-trigger': 'qb_rule-delete',
                    'html': _qb_icon('close')
                })
            ]
        })

        $('#layout-' + $(this).data('id')).prepend(rule)
    }).on('click', '[data-trigger=qb_rule-delete]', function() {
        $('#rule-' + $(this).data('id')).remove()
    })

    function _qb_item(type, options)
    {
        switch (type)
        {
            case 'button':
                options.type = 'button';
            break;
            case 'radio':
                type = 'label';

                options.html = [
                    _qb_item('input', {
                        'type': 'radio',
                        'name': options.name,
                        'value': options.value
                    }).prop('checked', options.checked ? true : false),
                    _qb_item('span', {
                        'html': options.html
                    }),
                ];

                delete options.name;
                delete options.value;
                delete options.checked;
            break;
            case 'text':
                type = 'div';

                options.class = options.class + ' input-field';

                options.html = [
                    _qb_item('input', {
                        'type': 'text',
                        'name': options.name
                    })
                ];

                delete options.name;
            break;
            case 'number':
                type = 'div';

                options.class = options.class + ' input-field';

                options.html = [
                    _qb_item('input', {
                        'type': 'number',
                        'name': options.name,
                        'max': options.max,
                        'min': options.min,
                        'value': 0
                    })
                ];

                delete options.name;
            break;
        }

        return $('<' + type + ' />', options);
    }

    function _qb_icon(icon)
    {
        return _qb_item('i', {
            'class': 'material-icons',
            'html': icon
        })
    }
@endpush

@section('wildcard')
    <div class="d-flex" id="search-area">
        <a href="#" class="flex-fill d-flex" data-trigger="clear">
            <i class="material-icons align-self-center">clear</i>
        </a>
        <a href="#" class="flex-fill d-flex" data-trigger="search_builder" data-class="#search_builder" data-class-remove="hide">
            <i class="material-icons align-self-center">filter_list</i>
        </a>
        <a href="#" class="flex-fill d-flex" data-trigger="save">
            <i class="material-icons align-self-center">save</i>
        </a>
        <input type="text" name="string" id="string" placeholder="Arayın" />
    </div>

    <div id="search_builder" class="">
        <div id="olive-query"></div>
        <div class="right-align mb-1 pr-1">
            <button type="button" class="btn-flat waves-effect red-text" data-class="#search_builder" data-class-add="hide">Vazgeç</button>
            <button type="button" class="btn-flat waves-effect" data-trigger="search_builder-apply">Uygula</button>
        </div>
    </div>

    @if (@$trends)
        <div class="owl-chips owl-carousel grey lighten-4 z-depth-1">
            @foreach ($trends as $trend)
                <a class="chip grey lighten-2 waves-effect mb-0" data-search="{{ $trend->data->key }}" href="#">{{ $trend->data->key }}</a>
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
        <div class="d-flex">
            <input type="date" class="align-self-center" name="start_date" value="{{ $s ? $s : date('Y-m-d', strtotime('-1 day')) }}" placeholder="Başlangıç" />
            <input type="date" class="align-self-center" name="end_date" value="{{ $e ? $e : date('Y-m-d') }}" placeholder="Bitiş" />

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
                data-input="input[name=end_date]"
                data-focus="input[name=start_date]"
                data-input-value="{{ date('Y-m-d') }}"
                data-value="{{ date('Y-m-d') }}">Bugün</a>
        </li>
        <li class="divider" tabindex="-1"></li>
        @if ($organisation->historical_days >= 1)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
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
                   -click
                    class="collection-item waves-effect"
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
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-90 day')) }}">Son 90 Gün</a>
            </li>
        @endif
    </ul>
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
                        @component('components.alert')
                            @slot('icon', 'info')
                            @slot('text', 'Hiç sonuç bulunamadı.')
                        @endcomponent
                    </div>
                    <div id="chart-tab">
                        @component('components.alert')
                            @slot('icon', 'info')
                            @slot('text', 'Hiç grafik sorgusu gerçekleştirmediniz.')
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
            <div class="banner mb-1 lighten-4 z-depth-1" style="background-image: url('{{ asset('img/photo/women.jpg') }}');">
                <p class="white-text">Görünen o ki, ilgilendiğiniz konu <span class="white pink-text">56%</span> oranla kadın kullanıcıları ilgilendiriyor.</p>
                <div class="banner-overlay pink"></div>
            </div>
            <div class="banner mb-1 lighten-4 z-depth-1 hide" style="background-image: url('{{ asset('img/photo/hate.jpg') }}');">
                <p class="white-text">Bu konu çok fazla nefret söylemi içeriyor.</p>
                <div class="banner-overlay black"></div>
            </div>

            <div class="banner-4 mb-1">
                <div class="banner-item tweet">
                    <strong>0</strong> tweet
                </div>
                <div class="banner-item entry">
                    <strong>0</strong> entry
                </div>
                <div class="banner-item article">
                    <strong>0</strong> haber
                </div>
                <div class="banner-item video">
                    <strong>0</strong> video
                </div>
                <div class="banner-item video-comment">
                    <strong>0</strong> video yorumu</div>
                <div class="banner-item product">
                    <strong>0</strong> ilan
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
                    <input type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Nötr
                    <input type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Negatif
                    <input type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Nefret Söylemi
                    <input type="range" min="0" max="9" value="0" />
                </p>
            </div>

            <div class="d-flex">
                <p class="range-field">
                    Soru
                    <input type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    İstek
                    <input type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Şikayet
                    <input type="range" min="0" max="9" value="0" />
                </p>
                <p class="range-field">
                    Haber
                    <input type="range" min="0" max="9" value="0" />
                </p>
            </div>

            <div class="d-flex">
                <label class="flex-fill">
                    <input name="gender" type="radio" />
                    <span>Hepsi</span>
                </label>
                <label class="flex-fill">
                    <input name="gender" type="radio" />
                    <span>Kadın</span>
                </label>
                <label class="flex-fill">
                    <input name="gender" type="radio" />
                    <span>Erkek</span>
                </label>
                <label class="flex-fill">
                    <input name="gender" type="radio" />
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
                <input name="illegal" value="illegal" type="checkbox" />
                <span>İllegal İçerikler Dahil</span>
            </label>
            <label class="collection-item d-block">
                <input name="sort" value="asc" type="checkbox" />
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
                        <input name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
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
            <li class="collection-item d-flex">
                <a class="btn-floating btn-small waves-effect align-self-center white mr-1">
                    <i class="material-icons grey-text">create</i>        
                </a>
                <a href="#" class="align-self-center">Örnek Arama</a>
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
@endpush
