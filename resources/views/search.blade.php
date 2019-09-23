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

@php
$elements = 'start_date,end_date,modules,string,reverse,take,gender,sentiment_pos,sentiment_neu,sentiment_neg,sentiment_hte,consumer_que,consumer_req,consumer_cmp,consumer_nws,sharp,categories';
@endphp

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

    .chart {
        margin: 0 0 2rem;
        position: relative;
    }
    .chart svg {
        height: 360px;
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
        $('input[name=string]').val('').hide().show( 'highlight', { 'color': '#f0f4c3' }, 400 ).focus()
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

        if (__.data('search-new'))
        {
            input.val(string)
        }
        else
        {
            input.val(input.val() ? array.join(__.data('operator') ? __.data('operator') : ' && ') : string).focus()
        }

        setTimeout(function() {
            // trigger
        }, 400)

        _scrollTo({
            'target': 'input#string',
            'tolerance': '-64px'
        })
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

    $(document).on('click', '[data-trigger=save]', function() {
        var mdl = modal({
            'id': 'save',
            'body': $('<form />', {
                'method': 'post',
                'action': '{{ route('search.save') }}',
                'id': 'form',
                'class': 'json',
                'data-callback': '__search_save',
                'data-include': '{{ $elements }}',
                'html': $('<div />', {
                    'class': 'input-field',
                    'html': [
                        $('<input />', {
                            'id': 'name',
                            'name': 'name',
                            'type': 'text',
                            'class': 'validate'
                        }),
                        $('<label />', {
                            'for': 'name',
                            'html': 'Arama Adı'
                        }),
                        $('<span />', {
                            'class': 'helper-text'
                        })
                    ]
                })
            }),
            'size': 'modal-small',
            'title': 'Aramayı Kaydet',
            'options': {
                dismissible: false
            },
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#form',
                    'html': buttons.save
                })
            ]
        })

        M.updateTextFields()

        $('input[name=name]').focus()
    }).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'alert',
            'body': 'Silmek istediğinizden emin misiniz?',
            'size': 'modal-small',
            'title': 'Sil',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('search.delete') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__search_delete'
                })
            ],
            'options': {}
        })
    })

    function __search_save(__, obj)
    {
        if (obj.status == 'ok')
        {
            vzAjax($('#savedSearches'))

            $('#modal-save').modal('close')

            M.toast({
                html: 'Arama Kaydedildi',
                classes: 'green darken-2'
            }, 200)
        }
    }

    function __search_delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#savedSearches').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-alert').modal('close')

            vzAjax($('#savedSearches'))

            M.toast({
                html: 'Arama Silindi',
                classes: 'green darken-2'
            }, 200)
        }
    }
@endpush

@section('wildcard')
    <div class="d-flex" id="search-area">
        <a href="#" class="flex-fill d-flex" data-trigger="clear">
            <i class="material-icons align-self-center">clear</i>
        </a>
        <a
            href="#"
            class="flex-fill d-flex"
            data-trigger="save">
            <i class="material-icons align-self-center">save</i>
        </a>
        <input
            type="text"
            name="string"
            id="string"
            value="{{ $q }}"
            placeholder="Arayın"
            class="json json-search"
            data-json-target="ul#search" />
    </div>
    <div id="search-operators">
        <div class="d-flex flex-wrap">
            <div class="p-1">
                <span class="grey-text">Genel</span>
                <button data-update-click type="button" class="btn-flat waves-effect waves-green btn-small d-table" data-validate="string" data-search="+">+Olsun</button>
                <button data-update-click type="button" class="btn-flat waves-effect waves-red btn-small d-table" data-validate="string" data-search="-">-Olmasın</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="site_id">Site / Sözlük Id</button>
            </div>

            <div class="p-1">
                <span class="grey-text">Sözlük Filtreleri</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="author">Yazar Adı</button>
            </div>

            <div class="p-1">
                <span class="grey-text">Twitter Filtreleri (Kullanıcı)</span>
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
                <span class="grey-text">Twitter Filtreleri (Tweet)</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.hashtag">Hashtag Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.mention">Mention Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.url">Bağlantı Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.media">Medya Sayısı</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!external.type:retweet">ReTweetler Hariç</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!external.type:quote">Alıntılar Hariç</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!external.type:reply">Cevaplar Hariç</button>
            </div>

            <div class="p-1">
                <span class="grey-text">YouTube Filtreleri</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="channel.title">Kanal Başlığı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="channel.id">Kanal Id</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="video_id">Video Id</button>
            </div>

            <div class="p-1">
                <span class="grey-text">Kaynak Tercihleri</span> <sup><a href="{{ route('sources.index') }}">Yönet</a></sup>
                @forelse ($sources as $source)
                    <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-operator=" " data-search="[s:{{ $source->id }}]">{{ $source->name }}</button>
                @empty
                    <span class="d-block red-text">Henüz tercih oluşturulmadı.</span>
                @endforelse
            </div>
        </div>
    </div>

    @if (@$trends)
        <div class="owl-chips owl-carousel grey lighten-4 z-depth-1">
            @foreach ($trends as $trend)
                <a data-update-click data-search-new="true" class="chip grey lighten-2 waves-effect mb-0" data-search="{{ $trend->data->key }}" href="#">{{ $trend->data->key }}</a>
            @endforeach
        </div>
    @endif
@endsection

@push('wildcard-bottom')
    <div id="date-area" class="d-flex justify-content-between grey lighten-4">
        <div class="grey-text align-self-center hide-on-med-and-down pl-1" data-name="stats">
            <div class="ml-1 d-flex">
                <i class="material-icons align-self-center mr-1">info</i>
                <span class="align-self-center">Aramak istediğiniz metni arama alanına girin.</span>
            </div>
        </div>
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
                data-value="{{ date('Y-m-d') }}">Bugün (Grafik Alınabilir)</a>
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
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Dün (Grafik Alınabilir)</a>
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
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Son 2 Gün (Grafik Alınabilir)</a>
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
                    data-value="{{ date('Y-m-d', strtotime('-7 day')) }}">Son 7 Gün (Grafik Alınabilir)</a>
            </li>
        @endif
        @if ($organisation->historical_days >= 14)
            <li>
                <a
                    href="#"
                    class="collection-item waves-effect"
                    data-update-click
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-14 day')) }}">Son 14 Gün (Grafik Alınabilir)</a>
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
    function __banner(__, obj)
    {
        if (obj.status == 'ok')
        {
            switch(__.data('type'))
            {
                case 'place':
                    if (obj.data.place[0].hit)
                    {
                        $('.banner').removeClass('hide').css({ 'background-image': 'url(/img/photo/city.jpg)' })
                        $('.banner').find('[data-name=overlay]').css({ 'background-color': '#333' })
                        $('.banner').find('[data-name=text]').html('Bu konu en çok ' + obj.data.place[0].name + ' bölgesinde konuşuldu. Tam olarak ' + obj.data.place[0].hit + ' defa!')
                    }
                break;
                case 'sentiment':
                    var def = {
                        'key': null,
                        'val': 0
                    };

                    var option = {
                        'neu': {
                            'text': 'Nötr',
                            'image': '/img/photo/neutral.jpg',
                            'color': '#333'
                        },
                        'pos': {
                            'text': 'Pozitif',
                            'image': '/img/photo/positive.jpg',
                            'color': '#4caf50'
                        },
                        'neg': {
                            'text': 'Negatif',
                            'image': '/img/photo/negative.jpg',
                            'color': '#f44336'
                        },
                        'hte': {
                            'text': 'Nefret Söylemi',
                            'image': '/img/photo/hate.jpg',
                            'color': '#333'
                        }
                    };

                    $.each(obj.data, function(k, count) {
                        if (count > def.val)
                        {
                            def = {
                                'key': k,
                                'val': count
                            };
                        }
                    })

                    if (def.key)
                    {
                        $('.banner').removeClass('hide').css({ 'background-image': 'url(' + option[def.key].image + ')' })
                        $('.banner').find('[data-name=overlay]').css({ 'background-color': option[def.key].color })
                        $('.banner').find('[data-name=text]').html('Görünen o ki ilgilendiğiniz konu çoğunlukla ' + option[def.key].text + ' mesaj içeriyor.')
                    }
                break;
                case 'consumer':
                    var def = {
                        'key': null,
                        'val': 0
                    };

                    var option = {
                        'que': {
                            'text': 'Soru',
                            'image': '/img/photo/question.jpg',
                            'color': '#333'
                        },
                        'req': {
                            'text': 'İstek',
                            'image': '/img/photo/request.jpg',
                            'color': '#009688'
                        },
                        'nws': {
                            'text': 'Haber',
                            'image': '/img/photo/news.jpg',
                            'color': '#fdd835'
                        },
                        'cmp': {
                            'text': 'Şikayet',
                            'image': '/img/photo/complaint.jpg',
                            'color': '#9c27b0'
                        }
                    };

                    $.each(obj.data, function(k, count) {
                        if (count > def.val)
                        {
                            def = {
                                'key': k,
                                'val': count
                            };
                        }
                    })

                    if (def.key)
                    {
                        $('.banner').removeClass('hide').css({ 'background-image': 'url(' + option[def.key].image + ')' })
                        $('.banner').find('[data-name=overlay]').css({ 'background-color': option[def.key].color })
                        $('.banner').find('[data-name=text]').html('Bu konu çok fazla ' + option[def.key].text + ' mesajı içeriyor.')
                    }
                break;
                case 'gender':
                    var def = {
                        'key': null,
                        'val': 0
                    };

                    var option = {
                        'male': {
                            'text': 'Erkek',
                            'image': '/img/photo/male.jpg',
                            'color': '#1565c0'
                        },
                        'female': {
                            'text': 'Kadın',
                            'image': '/img/photo/female.jpg',
                            'color': '#e91e63'
                        }
                    };

                    $.each(obj.data, function(k, count) {
                        if (count > def.val)
                        {
                            def = {
                                'key': k,
                                'val': count
                            };
                        }
                    })

                    if (def.key && def.key != 'unknown')
                    {
                        $('.banner').removeClass('hide').css({ 'background-image': 'url(' + option[def.key].image + ')' })
                        $('.banner').find('[data-name=overlay]').css({ 'background-color': option[def.key].color })
                        $('.banner').find('[data-name=text]').html('Görünüşe bakılırsa bu konu hakkında en çok ' + (def.val).toFixed(2) + '% oranla ' + option[def.key].text + ' kullanıcılar yazmış.')
                    }
                break;
                case 'hashtag':
                    $.each(obj.data, function(word, count) {
                        if (count)
                        {
                            $('.banner').removeClass('hide').css({ 'background-image': 'url(/img/photo/hashtag.jpg)' })
                            $('.banner').find('[data-name=overlay]').css({ 'background-color': '#ff9800' })
                            $('.banner').find('[data-name=text]').html('Bu konuda en çok bahsedilen kelime, <strong class="bold yellow black-text">' + word + '</strong> oldu. Tam olarak ' + count + ' paylaşımda bahsi geçti.')
                        }

                        return false;
                    })
                break;
            }
        }
    }

    var bannerTimer;

    function __search_archive(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.stats.hits)
            {
                $('[data-name=stats]').html('Yaklaşık ' + number_format(obj.stats.hits) + ' sonuç bulundu (' + obj.stats.took + ' saniye)').removeClass('hide');
            }
            else
            {
                $('[data-name=stats]').html('Daha fazla sonuç için arama kriterlerini azaltmanız gerekiyor.')
            }

            $('[data-name=twitter-tweet]').html(number_format(obj.stats.counts.twitter_tweet)).attr('data-count', obj.stats.counts.twitter_tweet);
            $('[data-name=sozluk-entry]').html(number_format(obj.stats.counts.sozluk_entry)).attr('data-count', obj.stats.counts.sozluk_entry);
            $('[data-name=youtube-video]').html(number_format(obj.stats.counts.youtube_video)).attr('data-count', obj.stats.counts.youtube_video);
            $('[data-name=youtube-comment]').html(number_format(obj.stats.counts.youtube_comment)).attr('data-count', obj.stats.counts.youtube_comment);
            $('[data-name=media-article]').html(number_format(obj.stats.counts.media_article)).attr('data-count', obj.stats.counts.media_article);
            $('[data-name=blog-document]').html(number_format(obj.stats.counts.blog_document)).attr('data-count', obj.stats.counts.blog_document);
            $('[data-name=shopping-product]').html(number_format(obj.stats.counts.shopping_product)).attr('data-count', obj.stats.counts.shopping_product);
            $('[data-name=instagram-media]').html(number_format(obj.stats.counts.instagram_media)).attr('data-count', obj.stats.counts.instagram_media);

            $('.banner').addClass('hide')

            if (obj.stats.hits)
            {
                const date1 = new Date($('input[name=start_date]').val());
                const date2 = new Date($('input[name=end_date]').val());
                const diffTime = Math.abs(date2.getTime() - date1.getTime());
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

                if (diffDays <= 14)
                {
                    window.clearTimeout(bannerTimer)

                    bannerTimer = window.setTimeout(function() {
                        var bucket = [ 'place', 'sentiment', 'consumer', 'gender', 'hashtag' ];
                        var selected_type = bucket[Math.floor(Math.random() * bucket.length)];

                        vzAjax($('<div />', {
                            'data-href': '{{ route('search.banner') }}',
                            'data-method': 'post',
                            'data-callback': '__banner',
                            'data-type': selected_type,
                            'data-include': '{{ $elements }}'
                        }))
                    }, 4000)
                }
            }

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                    var model;

                    switch(o._type)
                    {
                        case 'tweet'   : model = _tweet_   (o); break;
                        case 'entry'   : model = _entry_   (o); break;
                        case 'article' : model = _article_ (o); break;
                        case 'document': model = _document_(o); break;
                        case 'product' : model = _product_ (o); break;
                        case 'comment' : model = _comment_ (o); break;
                        case 'video'   : model = _video_   (o); break;
                        case 'media'   : model = _media_   (o); break;
                    }

                    model.find('.text-area').mark(obj.words, {
                        'element': 'span',
                        'className': 'marked yellow black-text',
                        'accuracy': 'complementary'
                    })

                    item.html(model).appendTo(__)
                })
            }

            operator('fast-close')

            $('.tabs').tabs('select', 'search-tab')
        }
    }

    function __chart_generate(id)
    {
        $('#' + id + 'Chart').addClass('hide')

        var chart_element = $('<div />', {
            'class': 'chart',
            'html': [
                $('<div />', {
                    'id': id + 'Chart',
                    'class': 'chart-container hide'
                })
            ]
        })

        chart_element.prependTo('#chart-tab')
    }

    function __table_generate(id)
    {
        var table = $('#' + id);
            table.remove()

        var table = $('<table />', {
                'id': id,
                'class': 'highlight mb-1'
            })
            table.append(
                [
                    $('<thead />'),
                    $('<tbody />'),
                    $('<tfoot />', {
                        'class': 'hide',
                        'html': $('<tr />', {
                            'html': $('<th />', {
                                'colspan': 2,
                                'html': $('<a />', {
                                    'href': '#',
                                    'class': 'd-table mx-auto',
                                    'data-class': '#' + id + '->find(tr.hide)',
                                    'data-class-remove': 'hide',
                                    'data-class-hide': '#' + id + '->find(tfoot)',
                                    'html': 'Tümünü Göster'
                                })
                            })
                        })
                    })
                ]
            )

            table.prependTo('#chart-tab')

        return table;
    }

    function __chart(__, obj)
    {
        const options = {
            chart: {
                height: 350,
                type: 'line',
                shadow: {
                    enabled: true,
                    color: '#000',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 1
                },
                toolbar: { show: false }
            },
            dataLabels: { enabled: true },
            stroke: {
                width: 0,
                curve: 'smooth'
            },
            series: [],
            title: {
                text: $('input[name=string]').val() + ' / ' + $('input[name=start_date]').val() + ' - ' + $('input[name=end_date]').val(),
                align: 'left'
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: [ '#f3f3f3', 'transparent' ],
                    opacity: 0.5
                }
            },
            markers: { size: 4 },
            xaxis: {
                categories: [],
                title: { text: '' }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: true,
                offsetY: -25,
                offsetX: -5
            }
        }

        if (obj.status == 'ok')
        {
            var key = __.data('type');

            switch (__.data('type'))
            {
                case 'histogram':
                    __chart_generate('histogramHourly');
                    __chart_generate('histogramDaily');

                    const hourlyChartOption = JSON.parse(JSON.stringify(options));
                    const dailyChartOption = JSON.parse(JSON.stringify(options));

                    hourlyChartOption['stroke']['width'] = 2;
                    hourlyChartOption['yaxis'] = {
                        'title': {
                            'text': 'Saatlik Paylaşım Grafiği'
                        }
                    }
                    hourlyChartOption['xaxis']['categories'] = [ '00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00' ];

                    dailyChartOption['stroke']['width'] = 2;
                    dailyChartOption['yaxis'] = {
                        'title': {
                            'text': 'Günlük Paylaşım Grafiği'
                        }
                    }
                    dailyChartOption['xaxis']['categories'] = [ 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar' ];

                    $.each(obj.data, function(module_key, module) {
                        var label = '';

                        switch (module_key)
                        {
                            case 'twitter'         :label = 'Tweet' ; break;
                            case 'instagram'       :label = 'Medya' ; break;
                            case 'sozluk'          :label = 'Entry' ; break;
                            case 'news'            :label = 'Haber' ; break;
                            case 'blog'            :label = 'Makale'; break;
                            case 'youtube_video'   :label = 'Video' ; break;
                            case 'youtube_comment' :label = 'Yorum' ; break;
                            case 'shopping'        :label = 'İlan'  ; break;
                        }

                        $.each(module, function(time_key, time) {
                            switch (time_key)
                            {
                                case 'hourly':
                                    var hourly_datas = [];
                                    var _hourly_datas = { 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0, 17: 0, 18: 0, 19: 0, 20: 0, 21: 0, 22: 0, 23: 0 };

                                    $.each(time, function(bucket_key, bucket) {
                                        $.each(bucket, function(key, o) {
                                            _hourly_datas[o.key] = o.doc_count;
                                        })
                                    })

                                    $.each(_hourly_datas, function(key, o) { hourly_datas.push(o) })

                                    hourlyChartOption['series'].push({
                                        name: label,
                                        data: hourly_datas
                                    })

                                    $('#histogramHourlyChart').removeClass('hide')
                                break;
                                case 'daily':
                                    var daily_datas = [];
                                    var _daily_datas = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0 };

                                    $.each(time, function(bucket_key, bucket) {
                                        $.each(bucket, function(key, o) {
                                            _daily_datas[o.key] = o.doc_count;
                                        })
                                    })

                                    $.each(_daily_datas, function(key, o) { daily_datas.push(o) })

                                    dailyChartOption['series'].push({
                                        name: label,
                                        data: daily_datas
                                    })

                                    if (daily_datas.length)
                                    {
                                        $('#histogramDailyChart').removeClass('hide')
                                    }
                                break;
                            }
                        })
                    })

                    var hourlyChart = new ApexCharts(document.querySelector('#histogramHourlyChart'), hourlyChartOption);
                    var dailyChart = new ApexCharts(document.querySelector('#histogramDailyChart'), dailyChartOption);
                        hourlyChart.render()
                        dailyChart.render()
                break;
                case 'place':
                    const twitterChartOption = JSON.parse(JSON.stringify(options));
                    const instagramChartOption = JSON.parse(JSON.stringify(options));

                    twitterChartOption['chart']['type'] = 'bar';
                    twitterChartOption['plotOptions'] = {
                        bar: {
                            distributed: true,
                            horizontal: true,
                            barHeight: '100%',
                            dataLabels: { position: 'bottom' }
                        }
                    };

                    twitterChartOption['subtitle'] = { 'text': 'Twitter Konum Grafiği' };

                    instagramChartOption['chart']['type'] = 'bar';
                    instagramChartOption['plotOptions'] = {
                        bar: {
                            distributed: true,
                            horizontal: true,
                            barHeight: '100%',
                            dataLabels: { position: 'bottom' }
                        }
                    };

                    instagramChartOption['subtitle'] = { 'text': 'Instagram Konum Grafiği' };

                    var instagram_hits = false;
                    var twitter_hits = false;

                    $.each(obj.data, function(module_key, module) {
                        var categories = [];
                        var datas = [];

                        if (module.place.buckets.length)
                        {
                            $.each(module.place.buckets, function(key, bucket) {
                                categories.push(bucket.key)
                                datas.push(bucket.doc_count)
                            })

                            if (module_key == 'twitter')
                            {
                                twitter_hits = true;
                            }

                            if (module_key == 'instagram')
                            {
                                instagram_hits = true;
                            }
                        }

                        switch (module_key)
                        {
                            case 'instagram':
                                instagramChartOption['series'] = [
                                    {
                                        name: 'Medya',
                                        data: datas
                                    }
                                ];

                                instagramChartOption['xaxis'] = {
                                    categories: categories
                                };
                            break;
                            case 'twitter':
                                twitterChartOption['series'] = [
                                    {
                                        name: 'Tweet',
                                        data: datas
                                    }
                                ];

                                twitterChartOption['xaxis'] = {
                                    categories: categories
                                };
                            break;
                        }
                    })

                    if (instagram_hits)
                    {
                        __chart_generate('instagramPlace')

                        var instagramChart = new ApexCharts(document.querySelector('#instagramPlaceChart'), instagramChartOption);
                            instagramChart.render()

                        $('#instagramPlaceChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Instagram paylaşımlarında konum bulunamadı!' }, 200)
                    }

                    if (twitter_hits)
                    {
                        __chart_generate('twitterPlace')

                        var twitterChart = new ApexCharts(document.querySelector('#twitterPlaceChart'), twitterChartOption);
                            twitterChart.render()

                        $('#twitterPlaceChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Twitter paylaşımlarında konum bulunamadı!' }, 200)
                    }
                break;
                case 'platform':
                    const twitterPlatformChartOption = JSON.parse(JSON.stringify(options));

                    twitterPlatformChartOption['chart']['type'] = 'bar';
                    twitterPlatformChartOption['plotOptions'] = {
                        bar: {
                            distributed: true,
                            horizontal: true,
                            barHeight: '100%',
                            dataLabels: { position: 'bottom' }
                        }
                    };
                    twitterPlatformChartOption['subtitle'] = { 'text': 'Twitter Platform Grafiği' };

                    var twitter_hits = false;

                    $.each(obj.data, function(module_key, module) {
                        var categories = [];
                        var datas = [];

                        if (module.platform.buckets.length)
                        {
                            $.each(module.platform.buckets, function(key, bucket) {
                                categories.push(bucket.key)
                                datas.push(bucket.doc_count)
                            })

                            if (module_key == 'twitter')
                            {
                                twitter_hits = true;
                            }
                        }

                        switch (module_key)
                        {
                            case 'twitter':
                                twitterPlatformChartOption['series'] = [
                                    {
                                        name: 'Tweet',
                                        data: datas
                                    }
                                ];
                                twitterPlatformChartOption['xaxis'] = {
                                    categories: categories
                                };
                            break;
                        }
                    })

                    if (twitter_hits)
                    {
                        __chart_generate('twitterPlatform')

                        var twitterPlatformChart = new ApexCharts(document.querySelector('#twitterPlatformChart'), twitterPlatformChartOption);
                            twitterPlatformChart.render()

                        $('#twitterPlatformChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Twitter paylaşımlarında platform bulunamadı!' }, 200)
                    }
                break;
                case 'author':
                    var query = $('input[name=string]').val() + ' / ' + $('input[name=start_date]').val() + ' - ' + $('input[name=end_date]').val();

                    $.each(obj.data, function(module_key, module) {
                        switch (module_key)
                        {
                            case 'twitter':
                                if ((module.mentions.hits.buckets).length)
                                {
                                    var table = __table_generate('twitterMentions')

                                    var i = 0;

                                    $.each(module.mentions.hits.buckets, function(bucket_key, bucket) {
                                        table.children('tbody').append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': [
                                                            $('<span />', {
                                                                'class': 'd-table',
                                                                'html': bucket.properties.hits.hits[0]._source.mention.name
                                                            }),
                                                            $('<a />', {
                                                                'href': '#',
                                                                'class': 'd-table grey-text',
                                                                'data-search': bucket.properties.hits.hits[0]._source.mention.screen_name,
                                                                'data-update-click': true,
                                                                'data-module': 'twitter',
                                                                'html': '@' + bucket.properties.hits.hits[0]._source.mention.screen_name
                                                            })
                                                        ]
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.children('thead').prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Kullanıcı' }), $('<th />', { 'class': 'right-align', 'html': 'Tweet' }) ] }))
                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'Twitter: Bahsedilen Kullanıcılar #100' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }

                                if ((module.users.buckets).length)
                                {
                                    var table = __table_generate('twitterUsers')

                                    var i = 0;

                                    $.each(module.users.buckets, function(bucket_key, bucket) {
                                        table.children('tbody').append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': [
                                                            $('<span />', {
                                                                'class': 'd-table',
                                                                'html': bucket.properties.hits.hits[0]._source.user.name
                                                            }),
                                                            $('<a />', {
                                                                'href': '#',
                                                                'class': 'd-table grey-text',
                                                                'data-search': '@' + bucket.properties.hits.hits[0]._source.user.screen_name,
                                                                'data-update-click': true,
                                                                'data-module': 'twitter',
                                                                'html': '@' + bucket.properties.hits.hits[0]._source.user.screen_name
                                                            })
                                                        ]
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.children('thead').prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Kullanıcı' }), $('<th />', { 'class': 'right-align', 'html': 'Tweet' }) ] }))
                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'Twitter: Paylaşım Yapan Kullanıcılar #100' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                            case 'youtube_video':
                                if ((module.users.buckets).length)
                                {
                                    var table = __table_generate('youtubeUsers')

                                    var i = 0;

                                    $.each(module.users.buckets, function(bucket_key, bucket) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': [
                                                            $('<span />', {
                                                                'class': 'd-table',
                                                                'html': bucket.properties.hits.hits[0]._source.channel.title
                                                            }),
                                                            $('<a />', {
                                                                'href': '#',
                                                                'class': 'd-table grey-text',
                                                                'data-search': 'channel.id:"' + bucket.key + '"',
                                                                'data-update-click': true,
                                                                'data-module': 'youtube_video',
                                                                'html': bucket.key
                                                            })
                                                        ]
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Kullanıcı' }), $('<th />', { 'class': 'right-align', 'html': 'Video' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'YouTube: Yükleme Yapan Kullanıcılar #100' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                            case 'youtube_comment':
                                if ((module.users.buckets).length)
                                {
                                    var table = __table_generate('youtubeComments')

                                    var i = 0;

                                    $.each(module.users.buckets, function(bucket_key, bucket) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': [
                                                            $('<span />', {
                                                                'class': 'd-table',
                                                                'html': bucket.properties.hits.hits[0]._source.channel.title
                                                            }),
                                                            $('<a />', {
                                                                'href': '#',
                                                                'class': 'd-table grey-text',
                                                                'data-search': 'channel.id:"' + bucket.key + '"',
                                                                'data-update-click': true,
                                                                'data-module': 'youtube_comment',
                                                                'html': bucket.key
                                                            })
                                                        ]
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Kullanıcı' }), $('<th />', { 'class': 'right-align', 'html': 'Yorum' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'YouTube: Yorum Yapan Kullanıcılar #100' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                            case 'sozluk':
                                if ((module.sites).length)
                                {
                                    var table = __table_generate('sozlukSites')

                                    var i = 0;

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'sozluk',
                                                            'html': o.name + ' #' + o.id
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Sözlük' }), $('<th />', { 'class': 'right-align', 'html': 'Entry' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'Paylaşım Yapılan Sözlükler' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }

                                if ((module.users).length)
                                {
                                    var table = __table_generate('sozlukUsers')

                                    var i = 0;

                                    $.each(module.users, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': [
                                                            $('<span />', {
                                                                'class': 'd-table',
                                                                'html': o.name
                                                            }),
                                                            $('<a />', {
                                                                'href': '#',
                                                                'class': 'd-table grey-text',
                                                                'data-search': 'author:"' + o.name + '"',
                                                                'data-update-click': true,
                                                                'data-module': 'sozluk',
                                                                'html': '@' + o.name + ', ' + o.site
                                                            })
                                                        ]
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Sözlük' }), $('<th />', { 'class': 'right-align', 'html': 'Entry' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'Paylaşım Yapan Sözlük Yazarları' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                            case 'news':
                                if ((module.sites).length)
                                {
                                    var table = __table_generate('newsSites')

                                    var i = 0;

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'news',
                                                            'html': o.name + ' #' + o.id
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Site' }), $('<th />', { 'class': 'right-align', 'html': 'Haber' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'Haber Yapan Siteler #100' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                            case 'blog':
                                if ((module.sites).length)
                                {
                                    var table = __table_generate('blogSites')

                                    var i = 0;

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'blog',
                                                            'html': o.name + ' #' + o.id
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Site' }), $('<th />', { 'class': 'right-align', 'html': 'Blog' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'Blog Yazan Siteler' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                            case 'shopping':
                                if ((module.sites).length)
                                {
                                    var table = __table_generate('shoppingSites')

                                    var i = 0;

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'shopping',
                                                            'html': o.name + ' #' + o.id
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Site' }), $('<th />', { 'class': 'right-align', 'html': 'İlan' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'E-ticaret: İlan Paylaşılan Siteler' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }

                                if ((module.users).length)
                                {
                                    var table = __table_generate('shoppingUsers')

                                    var i = 0;

                                    $.each(module.users, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'class': i >= 5 ? 'hide' : '',
                                                'html': [
                                                    $('<td />', {
                                                        'html': [
                                                            $('<span />', {
                                                                'class': 'd-table',
                                                                'html': o.name
                                                            }),
                                                            $('<a />', {
                                                                'href': '#',
                                                                'class': 'd-table grey-text',
                                                                'data-search': 'seller.name:"' + o.name + '"',
                                                                'data-update-click': true,
                                                                'data-module': 'shopping',
                                                                'html': '@' + o.name + ', ' + o.site
                                                            })
                                                        ]
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )

                                        i++;
                                    })

                                    table.prepend($('<tr />', { 'html': [ $('<th />', { 'html': 'Site' }), $('<th />', { 'class': 'right-align', 'html': 'İlan' }) ] }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 2,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': 'E-ticaret: İlan Paylaşan Kullanıcılar' + '<br />' + query
                                            })
                                        ]
                                    }))

                                    if (i > 5)
                                    {
                                        table.children('tfoot').removeClass('hide')
                                    }
                                }
                                else
                                {
                                    //
                                }
                            break;
                        }
                    })
                break;
                case 'hashtag':
                    const twitterHashtagChartOption = JSON.parse(JSON.stringify(options));
                    const instagramHashtagChartOption = JSON.parse(JSON.stringify(options));

                    twitterHashtagChartOption['chart']['type'] = 'bar';
                    twitterHashtagChartOption['plotOptions'] = {
                        bar: {
                            distributed: true,
                            horizontal: true,
                            barHeight: '100%',
                            dataLabels: { position: 'bottom' }
                        }
                    };
                    twitterHashtagChartOption['subtitle'] = { 'text': 'Twitter Hashtag Grafiği' };

                    instagramHashtagChartOption['chart']['type'] = 'bar';
                    instagramHashtagChartOption['plotOptions'] = {
                        bar: {
                            distributed: true,
                            horizontal: true,
                            barHeight: '100%',
                            dataLabels: { position: 'bottom' }
                        }
                    };
                    instagramHashtagChartOption['subtitle'] = { 'text': 'Instagram Hashtag Grafiği' };

                    var instagram_hits = false;
                    var twitter_hits = false;

                    $.each(obj.data, function(module_key, module) {
                        var categories = [];
                        var datas = [];

                        if (module.hashtag.hits.buckets.length)
                        {
                            $.each(module.hashtag.hits.buckets, function(key, bucket) {
                                categories.push(bucket.key)
                                datas.push(bucket.doc_count)
                            })

                            if (module_key == 'twitter')
                            {
                                twitter_hits = true;
                            }

                            if (module_key == 'instagram')
                            {
                                instagram_hits = true;
                            }
                        }

                        switch (module_key)
                        {
                            case 'instagram':
                                instagramHashtagChartOption['series'] = [
                                    {
                                        name: 'Medya',
                                        data: datas
                                    }
                                ];

                                instagramHashtagChartOption['xaxis'] = {
                                    categories: categories
                                };
                            break;
                            case 'twitter':
                                twitterHashtagChartOption['series'] = [
                                    {
                                        name: 'Tweet',
                                        data: datas
                                    }
                                ];

                                twitterHashtagChartOption['xaxis'] = {
                                    categories: categories
                                };
                            break;
                        }
                    })

                    if (twitter_hits)
                    {
                        __chart_generate('twitterHashtag')

                        var twitterChart = new ApexCharts(document.querySelector('#twitterHashtagChart'), twitterHashtagChartOption);
                            twitterChart.render()

                        $('#twitterHashtagChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Twitter paylaşımlarında hashtag verisi bulunamadı!' }, 200)
                    }

                    if (instagram_hits)
                    {
                        __chart_generate('instagramHashtag')

                        var instagramChart = new ApexCharts(document.querySelector('#instagramHashtagChart'), instagramHashtagChartOption);
                            instagramChart.render()

                        $('#instagramHashtagChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Instagram paylaşımlarında hashtag verisi bulunamadı!' }, 200)
                    }
                break;
                case 'consumer':
                    __chart_generate('consumer');

                    const consumerChartOption = JSON.parse(JSON.stringify(options));

                    consumerChartOption['stroke']['width'] = 4;
                    consumerChartOption['xaxis']['title']['text'] = 'Müşteri Grafiği';
                    consumerChartOption['xaxis']['categories'] = [ '% İstek', '% Soru', '% Şikayet', '% Haber' ];

                    $.each(obj.data, function(module_key, module) {
                        var label = '';
                        var total_html = '';

                        switch (module_key)
                        {
                            case 'twitter':
                                label = 'Twitter';
                                total_html = $('[data-name=twitter-tweet]').html();
                            break;
                            case 'instagram':
                                label = 'Instagram';
                                total_html = $('[data-name=instagram-media]').html();
                            break;
                            case 'sozluk':
                                label = 'Sözlük';
                                total_html = $('[data-name=sozluk-entry]').html();
                            break;
                            case 'youtube_video':
                                label = 'YouTube Video';
                                total_html = $('[data-name=youtube-video]').html();
                            break;
                            case 'youtube_comment':
                                label = 'YouTube Yorum';
                                total_html = $('[data-name=youtube-comment]').html();
                            break;
                        }

                        var datas = [];

                        var req = module.req;
                        var que = module.que;
                        var cmp = module.cmp;
                        var nws = module.nws;

                        datas.push((req ? req : 0).toFixed(2))
                        datas.push((que ? que : 0).toFixed(2))
                        datas.push((cmp ? cmp : 0).toFixed(2))
                        datas.push((nws ? nws : 0).toFixed(2))

                        consumerChartOption['series'].push({ name: label, data: datas })

                        if (datas.length)
                        {
                            $('#consumerChart').removeClass('hide')
                        }
                    })

                    var consumerChart = new ApexCharts(document.querySelector('#consumerChart'), consumerChartOption);
                        consumerChart.render()
                break;
                case 'sentiment':
                    __chart_generate('sentiment');

                    const sentimentChartOption = JSON.parse(JSON.stringify(options));

                    sentimentChartOption['stroke']['width'] = 4;
                    sentimentChartOption['xaxis']['title']['text'] = 'Duygu Grafiği';
                    sentimentChartOption['xaxis']['categories'] = [ '% Pozitif', '% Nötr', '% Negatif', '% Nefret Söylemi' ];

                    $.each(obj.data, function(module_key, module) {
                        var label = '';
                        var total_html = '';

                        switch (module_key)
                        {
                            case 'twitter':
                                label = 'Twitter';
                                total_html = $('[data-name=twitter-tweet]').html();
                            break;
                            case 'instagram':
                                label = 'Instagram';
                                total_html = $('[data-name=instagram-media]').html();
                            break;
                            case 'sozluk':
                                label = 'Sözlük';
                                total_html = $('[data-name=sozluk-entry]').html();
                            break;
                            case 'news':
                                label = 'Haber';
                                total_html = $('[data-name=media-article]').html();
                            break;
                            case 'blog':
                                label = 'Blog';
                                total_html = $('[data-name=blog-document]').html();
                            break;
                            case 'youtube_video':
                                label = 'YouTube Video';
                                total_html = $('[data-name=youtube-video]').html();
                            break;
                            case 'youtube_comment':
                                label = 'YouTube Yorum';
                                total_html = $('[data-name=youtube-comment]').html();
                            break;
                            case 'shopping':
                                label = 'E-ticaret';
                                total_html = $('[data-name=shopping-product]').html();
                            break;
                        }

                        var datas = [];

                        var pos = module.pos;
                        var neu = module.neu;
                        var neg = module.neg;
                        var hte = module.hte;

                        datas.push((pos ? pos : 0).toFixed(2))
                        datas.push((neu ? neu : 0).toFixed(2))
                        datas.push((neg ? neg : 0).toFixed(2))
                        datas.push((hte ? hte : 0).toFixed(2))

                        sentimentChartOption['series'].push({ name: label, data: datas })

                        if (datas.length)
                        {
                            $('#sentimentChart').removeClass('hide')
                        }
                    })

                    var sentimentChart = new ApexCharts(document.querySelector('#sentimentChart'), sentimentChartOption);
                        sentimentChart.render()
                break;
                case 'gender':
                    var genders = { 'male': 'Erkek', 'female': 'Kadın', 'unknown': 'Bilinmeyen' };

                    const genderChartOption = JSON.parse(JSON.stringify(options));

                    genderChartOption['xaxis']['title']['text'] = 'Cinsiyet Grafiği';
                    genderChartOption['markers']['size'] = 10;

                    var gender_hits = false;

                    $.each(obj.data, function(module_key, module) {
                        var label = '';

                        switch (module_key)
                        {
                            case 'twitter'         :label = 'Twitter'      ; break;
                            case 'sozluk'          :label = 'Sözlük'       ; break;
                            case 'youtube_video'   :label = 'YouTube Video'; break;
                            case 'youtube_comment' :label = 'YouTube Yorum'; break;
                        }

                        var datas = [];

                        if (module)
                        {
                            $.each(module.gender.buckets, function(gender_key, gender) {
                                genderChartOption['xaxis']['categories'].push(genders[gender.key])
                                datas.push(gender.doc_count)

                                gender_hits = true;
                            })
                        }

                        genderChartOption['series'].push({
                            name: label,
                            data: datas
                        })
                    })

                    if (gender_hits)
                    {
                        __chart_generate('gender');

                        var genderChart = new ApexCharts(document.querySelector('#genderChart'), genderChartOption);
                            genderChart.render()

                        $('#genderChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Paylaşımlarda cinsiyet verisi bulunamadı!' }, 200)
                    }
                break;
            }

            $('.tabs').tabs('select', 'chart-tab')
        }
    }
@endpush

@section('panel-icon', 'pie_chart')
@section('panel')
    <div class="collection collection-unstyled">
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="histogram" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Zaman İstatistikleri</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="place" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Lokasyon</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="platform" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Platform</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="sentiment" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Duygu</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="consumer" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Soru, İstek, Şikayet ve Haber</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="gender" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Cinsiyet Grafiği</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="author" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">@bahsedenler</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="hashtag" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">#hashtagler</a>
        <a href="#" class="collection-item loading" data-callback="__chart" data-type="hashtag" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}">Kategori <sup class="red-text">Yakında</sup></a>
    </div>
@endsection

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
                            <a href="#chart-tab">İstatistikler</a>
                        </li>
                    </ul>
                </div>
                <div class="card-content">
                    <div id="search-tab">
                        <ul class="collection collection-unstyled json-clear loading" 
                            id="search"
                            data-href="{{ route('search.dashboard') }}"
                            data-skip="0"
                            data-more-button="#search-more_button"
                            data-callback="__search_archive"
                            data-method="post"
                            data-include="{{ $elements }}"
                            data-nothing>
                            <li class="collection-item nothing">
                                @component('components.alert')
                                    @slot('icon', 'info')
                                    @slot('text', 'Hiç sonuç bulunamadı!')
                                @endcomponent
                            </li>
                            <li class="collection-item model hide"></li>
                        </ul>

                        <a href="#"
                           class="more hide json"
                           id="search-more_button"
                           data-json-target="ul#search">Daha Fazla</a>
                    </div>
                    <div id="chart-tab" style="display: none;">
                        <span class="grey-text text-darken-2" data-id="chart-alert">
                            @component('components.alert')
                                @slot('icon', 'info')
                                @slot('text', 'Öncelikle bir sorgu girin ve soldaki menüden bir istatistik isteği yapın.')
                            @endcomponent
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col hide-on-med-and-down hide-on-large-only show-on-extra-large xl4">
            <div class="banner mb-1 lighten-4 z-depth-1 hide">
                <p class="white-text" data-name="text"></p>
                <div class="banner-overlay" data-name="overlay"></div>
            </div>

            <div class="banner-4 mb-1">
                <div class="banner-item tweet">
                    <strong data-name="twitter-tweet">0</strong> tweet
                </div>
                <div class="banner-item entry">
                    <strong data-name="sozluk-entry">0</strong> sözlük
                </div>
                <div class="banner-item article">
                    <strong data-name="media-article">0</strong> haber
                </div>
                <div class="banner-item video">
                    <strong data-name="youtube-video">0</strong> youtube
                </div>
                <div class="banner-item video-comment">
                    <strong data-name="youtube-comment">0</strong> youtube yorum
                </div>
                <div class="banner-item product">
                    <strong data-name="shopping-product">0</strong> ilan
                </div>
                <div class="banner-item document">
                    <strong data-name="blog-document">0</strong> blog
                </div>
                <div class="banner-item media">
                    <strong data-name="instagram-media">0</strong> Instagram
                </div>
            </div>

            <div class="d-flex flex-wrap mb-2">
                <div class="d-flex flex-column flex-fill">
                    <h6 class="blue-grey-text">Duygu</h6>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="sentiment_pos" value="5" />
                            <span class="lever"></span>
                            Pozitif
                        </label>
                    </div>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="sentiment_neu" value="5" />
                            <span class="lever"></span>
                            Nötr
                        </label>
                    </div>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="sentiment_neg" value="5" />
                            <span class="lever"></span>
                            Negatif
                        </label>
                    </div>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="sentiment_hte" value="5" />
                            <span class="lever"></span>
                            Nefret Söylemi
                        </label>
                    </div>
                </div>
                <div class="d-flex flex-column flex-fill">
                    <h6 class="blue-grey-text">Müşteri</h6>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="consumer_que" value="5" />
                            <span class="lever"></span>
                            Soru
                        </label>
                    </div>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="consumer_req" value="5" />
                            <span class="lever"></span>
                            İstek
                        </label>
                    </div>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="consumer_cmp" value="5" />
                            <span class="lever"></span>
                            Şikayet
                        </label>
                    </div>
                    <div class="switch">
                        <label>
                            <input type="checkbox" data-update name="consumer_nws" value="5" />
                            <span class="lever"></span>
                            Haber
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <h6 class="blue-grey-text">Cinsiyet</h6>
                <div class="d-flex">
                    <label class="flex-fill">
                        <input name="gender" type="radio" data-update-click value="all" checked />
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
            <div>
                <h6 class="blue-grey-text">Kategori <sup class="red-text">Yakında</sup></h6>
                <div class="d-flex flex-wrap"> 
                   @foreach(config('system.analysis.category.types') as $key => $cat)
                        <label class="flex-fill" style="width: 50%;">
                            <input type="checkbox" name="categories" id="categories" data-multiple="true" value="{{ $key }}" />
                            <span>{{ $cat['title'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).on('dblclick', '.module-label', function(e) {
        $('input[name=modules]').prop('checked', false)
        $(this).children('input[type=checkbox]').prop('checked', true)

        setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 600)
    }).on('click', '[data-trigger=select-allSources]', function() {
        $('input[name=modules]').prop('checked', true)

        setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 400)
    })

    $(document).on('click', '[data-module]', function() {
        var __ = $(this);

        $('input[name=modules]').prop('checked', false)
        $('input[name=modules][value=' + __.data('module') + ']').prop('checked', true)

        setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 600)
    })
@endpush

@section('dock')
    <div class="card card-unstyled mb-1">
        <div class="collection collection-unstyled">
            <label class="collection-item d-block">
                <input data-update name="reverse" value="on" type="checkbox" />
                <span>İlk İçerikler</span>
            </label>
        </div>
        <div class="card-content">
            <a href="#" class="card-title d-flex" data-trigger="select-allSources">
                <i class="material-icons align-self-center mr-1">device_hub</i>
                Kaynak
            </a>
        </div>
        <ul class="collection collection-unstyled collapsible">
            @foreach (config('system.modules') as $key => $module)
                <li class="collection-item">
                    @if ($key == 'twitter')
                        <div class="d-flex justify-content-between">
                            <label class="module-label">
                                <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                                <span>{{ $module }}</span>
                            </label>

                            <label class="module-label" data-tooltip="Olive, Twitter iyi sonuç algoritması." data-position="left">
                                <input data-update name="sharp" checked value="on" type="checkbox" />
                                <span>İyi Sonuç</span>
                            </label>
                        </div>
                    @else
                        <label class="module-label">
                            <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                            <span>{{ $module }}</span>
                        </label>
                    @endif
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
        <ul class="collection collection-unstyled load hide"
            id="savedSearches"
            data-href="{{ route('search.list') }}"
            data-callback="__saved_searches"
            data-method="post"
            data-loader="#ss-loader"
            data-nothing>
            <li class="collection-item model hide justify-content-between">
                <a href="#" class="align-self-center" data-name="name" data-trigger="loadSearch"></a>
                <a
                    class="btn-floating btn-small waves-effect align-self-center white"
                    data-trigger="delete">
                    <i class="material-icons grey-text text-darken-2">delete_forever</i>        
                </a>
            </li>
            <li class="nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'ss-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <div class="input-field">
                <select data-update name="take" id="take">
                    <option value="10" selected>10</option>
                    <option value="20">20</option>
                    <option value="40">40</option>
                </select>
                <label>Sayfalama</label>
                <span class="helper-text">Her kaynak için gösterilecek içerik sayısı.</span>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=loadSearch]', function() {
        var __ = $(this),
            option = __.data('options');

        $('input[name=string]').val(option.string)
        $('input[name=reverse]').prop('checked', option.reverse ? true : false)
        $('input[name=sharp]').prop('checked', option.sharp ? true : false)

        $('input[name=sentiment_pos]').prop('checked', option.sentiment_pos ? true : false)
        $('input[name=sentiment_neu]').prop('checked', option.sentiment_neu ? true : false)
        $('input[name=sentiment_neg]').prop('checked', option.sentiment_neg ? true : false)
        $('input[name=sentiment_hte]').prop('checked', option.sentiment_hte ? true : false)

        $('input[name=consumer_que]').prop('checked', option.sentiment_hte ? true : false)
        $('input[name=consumer_req]').prop('checked', option.consumer_req ? true : false)
        $('input[name=consumer_cmp]').prop('checked', option.consumer_cmp ? true : false)
        $('input[name=consumer_nws]').prop('checked', option.consumer_nws ? true : false)

        $('input[name=gender][value=' + option.gender + ']').prop('checked', true)

        $('select[name=take]').find('option[value=' + option.take + ']').prop('selected', true);
        $('select[name=take]').formSelect();

        $('input[name=modules]').prop('checked', false)
        $('input[name=categories]').prop('checked', false)

        $.each(JSON.parse(option.modules), function(key, module) {
            $('input[name=modules][value=' + module + ']').prop('checked', true)
        })

        $.each(JSON.parse(option.categories), function(key, category) {
            $('input[name=categories][value=' + category + ']').prop('checked', true)
        })

        var search = $('ul#search');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })

    function __saved_searches(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            __.removeClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = __.children('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name).attr('data-options', JSON.stringify(o))
                        item.find('[data-trigger=delete]').data('id', o.id)

                        item.appendTo(__)
                })
            }
        }
    }

    $('select').formSelect()
    $('.tabs').tabs()
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.canvasjs.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/apex.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush
