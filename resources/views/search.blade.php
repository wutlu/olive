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
    'dock' => true,
    'report_menu' => true
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

    var updateTimer;

    $(document).on('click', '[data-trigger=clear]', function() {
        $('input[name=string]').val('').effect( 'highlight', { 'color': '#e8f5e9' }, 800 ).focus()
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
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }

        operator('clear')
    }).on('focus click', 'input[name=string]', function() {
        operator('open')
    }).on('blur', 'input[name=string]', function() {
        operator('close')
    }).on('change', '[data-update]', function() {
        window.clearTimeout(updateTimer)

        updateTimer = window.setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 200)
    }).on('click', '[data-update-click]', function() {
        updateTimer = window.setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 200)
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

@include('_inc.alerts.search_operators')

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
        <a
            href="#"
            class="flex-fill d-flex"
            data-trigger="info">
            <i class="material-icons align-self-center">help</i>
        </a>
        <input
            type="text"
            name="string"
            id="string"
            value="{{ $q }}"
            placeholder="Arayın"
            class="json-search"
            data-json-target="ul#search" />
    </div>
    <div id="search-operators">
        <div class="d-flex flex-wrap">
            <div class="p-1">
                <span class="grey-text">Genel</span>
                <button data-update-click type="button" class="btn-flat waves-effect waves-green btn-small d-table" data-validate="string" data-search="+">+Olsun</button>
                <button data-update-click type="button" class="btn-flat waves-effect waves-red btn-small d-table" data-validate="string" data-search="-">-Olmasın</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="site_id">Site / Sözlük Id</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="place.name" data-tooltip="(İstanbul, Ankara..vb)" data-position="right">Konum</button>
            </div>

            <div class="p-1">
                <span class="grey-text">Sözlük Filtreleri</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="author">Yazar Adı</button>
            </div>

            <div class="p-1">
                <span class="grey-text">Twitter Filtreleri (Kullanıcı)</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="user.screen_name">Kullanıcı Adı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.id">Kullanıcı Id</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="user.verified:true">Tanınmış Hesaplar</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.statuses">Tweet Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.favourites">Favori Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.listed">Liste Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.friends">Takip Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="user.counts.followers">Takipçi Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="user.description">Profil Açıklaması</button>
            </div>

            <div class="p-1">
                <span class="grey-text">Twitter Filtreleri (Tweet)</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.retweet">ReTweet Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.favorite">Favori Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.quote">Alıntı Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.reply">Cevap Sayısı</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.hashtag">Hashtag Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.mention">Mention Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.url">Bağlantı Sayısı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="number" data-search="counts.media">Medya Sayısı</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!external.type:retweet">ReTweetler Hariç</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!external.type:quote">Alıntılar Hariç</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-search="!external.type:reply">Cevaplar Hariç</button>

                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="lang" data-tooltip="(tr,en,fr..vb)" data-position="right">Dil</button>
            </div>

            <div class="p-1">
                <span class="grey-text">YouTube Filtreleri</span>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="channel.title">Kanal Başlığı</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="channel.id">Kanal Id</button>
                <button data-update-click type="button" class="btn-flat waves-effect btn-small d-table" data-validate="string" data-search="video_id">Video Id</button>
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
    function __report__aggs__add(__, obj)
    {
        __report__aggs(__, obj)
    }

    $(document).on('click', '[data-trigger=stats-more]', function() {
        var _search_ = $('#search').clone();
            _search_.attr('data-aggs', 'on')

            var __ = $(this);

            if (__.data('report-type'))
            {
                _search_.attr('data-callback', '__report__aggs__add')
                _search_.attr('data-report', __.data('report-type'))
            }

            vzAjax(_search_)
    })

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
        if (obj.status == 'ok')
        {
            if (__.data('aggs'))
            {
                if ($('input[name=modules][value=twitter]').is(':checked'))
                {
                    $('[data-name=twitter-unique_users]').html(number_format(obj.stats.twitter.unique_users)).closest('p').removeClass(obj.stats.twitter.unique_users ? 'hide' : '');
                    $('[data-name=twitter-reach]').html(number_format(obj.stats.twitter.reach)).closest('p').removeClass(obj.stats.twitter.reach ? 'hide' : '');
                    $('[data-name=twitter-verified_users]').html(number_format(obj.stats.twitter.verified_users)).closest('p').removeClass(obj.stats.twitter.verified_users ? 'hide' : '');
                    $('[data-name=twitter-followers]').html(obj.stats.twitter.followers ? number_format((obj.stats.twitter.followers).toFixed(0)) : 0).closest('p').removeClass(obj.stats.twitter.followers ? 'hide' : '');
                    $('[data-name=twitter-hashtags]').html(number_format(obj.stats.twitter.hashtags)).closest('p').removeClass(obj.stats.twitter.hashtags ? 'hide' : '');
                    $('[data-name=twitter-mentions]').html(number_format(obj.stats.twitter.mentions)).closest('p').removeClass(obj.stats.twitter.mentions ? 'hide' : '');
                }

                if ($('input[name=modules][value=instagram]').is(':checked'))
                {
                    $('[data-name=instagram-unique_users]').html(number_format(obj.stats.instagram.unique_users)).closest('p').removeClass(obj.stats.instagram.unique_users ? 'hide' : '');
                    $('[data-name=instagram-hashtags]').html(number_format(obj.stats.instagram.hashtags)).closest('p').removeClass(obj.stats.instagram.hashtags ? 'hide' : '');
                    $('[data-name=instagram-mentions]').html(number_format(obj.stats.instagram.mentions)).closest('p').removeClass(obj.stats.instagram.mentions ? 'hide' : '');
                }

                if ($('input[name=modules][value=sozluk]').is(':checked'))
                {
                    $('[data-name=sozluk-unique_users]').html(number_format(obj.stats.sozluk.unique_users)).closest('p').removeClass(obj.stats.sozluk.unique_users ? 'hide' : '');
                    $('[data-name=sozluk-unique_topics]').html(number_format(obj.stats.sozluk.unique_topics)).closest('p').removeClass(obj.stats.sozluk.unique_topics ? 'hide' : '');
                    $('[data-name=sozluk-unique_sites]').html(number_format(obj.stats.sozluk.unique_sites)).closest('p').removeClass(obj.stats.sozluk.unique_sites ? 'hide' : '');
                }

                if ($('input[name=modules][value=blog]').is(':checked'))
                {
                    $('[data-name=blog-unique_sites]').html(number_format(obj.stats.blog.unique_sites)).closest('p').removeClass(obj.stats.blog.unique_sites ? 'hide' : '');
                }

                if ($('input[name=modules][value=youtube_video]').is(':checked'))
                {
                    $('[data-name=youtube_video-unique_users]').html(number_format(obj.stats.youtube_video.unique_users)).closest('p').removeClass(obj.stats.youtube_video.unique_users ? 'hide' : '');
                    $('[data-name=youtube_video-hashtags]').html(number_format(obj.stats.youtube_video.hashtags)).closest('p').removeClass(obj.stats.youtube_video.hashtags ? 'hide' : '');
                }

                if ($('input[name=modules][value=youtube_comment]').is(':checked'))
                {
                    $('[data-name=youtube_comment-unique_users]').html(number_format(obj.stats.youtube_comment.unique_users)).closest('p').removeClass(obj.stats.youtube_comment.unique_users ? 'hide' : '');
                    $('[data-name=youtube_comment-unique_videos]').html(number_format(obj.stats.youtube_comment.unique_videos)).closest('p').removeClass(obj.stats.youtube_comment.unique_videos ? 'hide' : '');
                }

                if ($('input[name=modules][value=shopping]').is(':checked'))
                {
                    $('[data-name=shopping-unique_sites]').html(number_format(obj.stats.shopping.unique_sites)).closest('p').removeClass(obj.stats.shopping.unique_sites ? 'hide' : '');
                    $('[data-name=shopping-unique_users]').html(number_format(obj.stats.shopping.unique_users)).closest('p').removeClass(obj.stats.shopping.unique_users ? 'hide' : '');
                }

                if ($('input[name=modules][value=news]').is(':checked'))
                {
                    $('[data-name=news-unique_sites]').html(number_format(obj.stats.news.unique_sites)).closest('p').removeClass(obj.stats.news.unique_sites ? 'hide' : '');
                    $('[data-name=news-local_states]').html(number_format(obj.stats.news.local_states)).closest('p').removeClass(obj.stats.news.local_states ? 'hide' : '');
                }
            }
            else
            {
                var item_model = __.children('.model');
                    item_model.addClass('hide')

                if (obj.stats.hits)
                {
                    $('[data-name=stats]').html('Yaklaşık ' + number_format(obj.stats.hits) + ' sonuç bulundu (' + obj.stats.took + ' saniye)').removeClass('hide');
                }
                else
                {
                    $('[data-name=stats]').html('Daha fazla sonuç için arama kriterlerini azaltmanız gerekiyor.')
                }

                $('[data-stat]').addClass('hide')

                $('[data-name=twitter-tweet]').html(number_format(obj.stats.counts.twitter_tweet)).attr('data-count', obj.stats.counts.twitter_tweet);
                $('[data-name=instagram-media]').html(number_format(obj.stats.counts.instagram_media)).attr('data-count', obj.stats.counts.instagram_media);
                $('[data-name=sozluk-entry]').html(number_format(obj.stats.counts.sozluk_entry)).attr('data-count', obj.stats.counts.sozluk_entry);
                $('[data-name=media-article]').html(number_format(obj.stats.counts.media_article)).attr('data-count', obj.stats.counts.media_article);
                $('[data-name=blog-document]').html(number_format(obj.stats.counts.blog_document)).attr('data-count', obj.stats.counts.blog_document).closest('p').removeClass(obj.stats.counts.blog_documen ? 'hide' : '');
                $('[data-name=youtube-video]').html(number_format(obj.stats.counts.youtube_video)).attr('data-count', obj.stats.counts.youtube_video).closest('p').removeClass(obj.stats.counts.youtube_vide ? 'hide' : '');
                $('[data-name=youtube-comment]').html(number_format(obj.stats.counts.youtube_comment)).attr('data-count', obj.stats.counts.youtube_comment).closest('p').removeClass(obj.stats.counts.youtube_commen ? 'hide' : '');
                $('[data-name=shopping-product]').html(number_format(obj.stats.counts.shopping_product)).attr('data-count', obj.stats.counts.shopping_product);

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
                            var bucket = [ 'sentiment', 'consumer', 'gender', 'hashtag' ];
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
                            item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o._id)

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
    }

    function __chart_generate(id, data)
    {
        $('#' + id + 'Chart').addClass('hide')

        var chart_element = $('<div />', {
            'class': 'chart',
            'html': [
                $('<div />', {
                    'id': id + 'Chart',
                    'class': 'chart-container hide'
                }),
                $('<div />', {
                    'class': 'd-flex mb-2',
                    'html': $('<a />', {
                        'class': 'btn-flat waves-effect d-flex',
                        'data-report-type': 'chart',
                        'data-trigger': 'report-chart',
                        'data-title': data.title,
                        'data-subtitle': data.subtitle,
                        'html': [
                            $('<i />', {
                                'class': 'material-icons align-self-center mr-1',
                                'html': 'note_add'
                            }),
                            $('<span />', {
                                'class': 'align-self-center',
                                'html': 'Grafiği Rapora Ekle'
                            })
                        ]
                    })
                }),
                $('<input />', {
                    'type': 'hidden',
                    'data-chart': 'value'
                })
            ]
        })

        chart_element.prependTo('#chart-tab')
    }

    $(document).on('click', '[data-trigger=report-chart]', function() {
        var __ = $(this);
        var action = '/raporlar/aggs';

        var form = __report__page_form(
            {
                'action': action,
                'method': 'put',
                'callback': '__report__page_create',
                'type': __.data('report-type')
            }
        );

        form.find('input[name=title]').val(__.data('title'))
        form.find('input[name=subtitle]').val(__.data('subtitle'))

        __report__pattern(__.closest('.chart').find('input[data-chart=value]').val(), form, __.data('report-type'), 'write')

        full_page_wrapper(form)

        form.find('input[name=title]').focus()
    }).on('click', '[data-trigger=report-table]', function() {
        var __ = $(this);
        var action = '/raporlar/aggs';
        var type = __.closest('.card').children('input[data-table=key]').val();
        var value = __.closest('.card').children('input[data-table=value]').val();

        var form = __report__page_form(
            {
                'action': action,
                'method': 'put',
                'callback': '__report__page_create',
                'type': type
            }
        );

        __report__pattern(value, form, type, 'write')

        full_page_wrapper(form)

        form.find('input[name=title]').focus()
    })

    function __table_generate(id)
    {
        var div = $('<div />', {
            'class': 'card mb-1',
            'css': {
                'max-height': '400px',
                'overflow': 'auto'
            },
            'html': [
                $('<input />', {
                    'type': 'hidden',
                    'data-table': 'value'
                }),
                $('<input />', {
                    'type': 'hidden',
                    'data-table': 'key'
                }),
                $('<div />', {
                    'class': 'card-content d-flex justify-content-end',
                    'html': [
                        $('<a />', {
                            'class': 'btn-flat waves-effect d-flex ml-1',
                            'data-report-type': 'table',
                            'data-trigger': 'report-table',
                            'data-table': '#' + id,
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center mr-1',
                                    'html': 'note_add'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center',
                                    'html': 'Tabloyu Rapora Ekle'
                                })
                            ]
                        }),
                        $('<a />', {
                            'class': 'btn-flat waves-effect ml-1',
                            'href': '#',
                            'data-excel': '#' + id,
                            'data-name': 'Excel Kopya',
                            'html': 'Excel'
                        })
                    ]
                }),
                $('<div />', {
                    'class': 'card-content table-area'
                })
            ]
        });

        var table = $('#' + id);
            table.closest('.card').remove()

        var table = $('<table />', {
                'id': id,
                'class': 'highlight'
            })
            table.append(
                [
                    $('<thead />'),
                    $('<tbody />')
                ]
            )

            div.children('.table-area').html(table)
            div.prependTo('#chart-tab')

        return table;
    }

    function chartToJson(selector, data)
    {
        var ndata = JSON.parse(JSON.stringify(data));
            ndata.chart.toolbar.show = false;
            ndata.grid = {
                  borderColor: 'transparent',
                  row: {
                      colors: [ 'transparent', 'transparent' ]
                  }
            };
            ndata.chart.height = 400;
            ndata.title.text = 'Grafik';
            delete ndata.xaxis.title;
            delete ndata.yaxis;

            if (ndata.subtitle)
            {
                ndata.title.text = ndata.subtitle.text;

                delete ndata.subtitle;
            }

        $(selector).parent('.chart').find('input[data-chart]').val(JSON.stringify(ndata))
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
                toolbar: {
                    show: true,
                    tools: {
                        download: '<i class="material-icons">save</i>'
                    }
                }
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
                    __chart_generate('histogramHourly', __.data());
                    __chart_generate('histogramDaily', __.data());

                    const hourlyChartOption = JSON.parse(JSON.stringify(options));
                    const dailyChartOption = JSON.parse(JSON.stringify(options));

                    hourlyChartOption['stroke']['width'] = 2;
                    hourlyChartOption['yaxis'] = {
                        'title': {
                            'text': 'Paylaşımların Saatlere Dağılımı'
                        }
                    }
                    hourlyChartOption['xaxis']['categories'] = [ '00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00' ];

                    dailyChartOption['stroke']['width'] = 2;
                    dailyChartOption['yaxis'] = {
                        'title': {
                            'text': 'Paylaşımların Günlere Dağılımı'
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

                    chartToJson('#histogramHourlyChart', hourlyChartOption)
                    chartToJson('#histogramDailyChart', dailyChartOption)

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
                        __chart_generate('instagramPlace', __.data())

                        chartToJson('#instagramPlaceChart', instagramChartOption)

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
                        __chart_generate('twitterPlace', __.data())

                        chartToJson('#twitterPlaceChart', twitterChartOption)

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
                        __chart_generate('twitterPlatform', __.data())

                        chartToJson('#twitterPlatformChart', twitterPlatformChartOption)

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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.mentions.hits.buckets))
                                        card.children('input[data-table=key]').val('twitterMentions')

                                    $.each(module.mentions.hits.buckets, function(bucket_key, bucket) {
                                        table.children('tbody').append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.mention.name }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': bucket.properties.hits.hits[0]._source.mention.screen_name,
                                                            'data-update-click': true,
                                                            'data-module': 'twitter',
                                                            'html': '@' + bucket.properties.hits.hits[0]._source.mention.screen_name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )
                                    })

                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Adı</b>' }),
                                            $('<th />', { 'html': '<b>Kullanıcı Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Tweet Sayısı</b>' })
                                        ]
                                    }))
                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Twitter: Bahsedilen Kullanıcılar #100</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
                                }
                                else
                                {
                                    //
                                }

                                if ((module.influencers.buckets).length)
                                {
                                    var table = __table_generate('twitterInfluencers')
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.influencers.buckets))
                                        card.children('input[data-table=key]').val('twitterInfluencers')

                                    $.each(module.influencers.buckets, function(bucket_key, bucket) {
                                        table.children('tbody').append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.user.name }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': '@' + bucket.properties.hits.hits[0]._source.user.screen_name,
                                                            'data-update-click': true,
                                                            'data-module': 'twitter',
                                                            'html': '@' + bucket.properties.hits.hits[0]._source.user.screen_name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count }),
                                                    $('<td />', { 'class': 'right-align', 'html': number_format(bucket.properties.hits.hits[0]._source.user.counts.followers) })
                                                ]
                                            })
                                        )
                                    })

                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Adı</b>' }),
                                            $('<th />', { 'html': '<b>Kullanıcı Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Tweet Sayısı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Takipçi Sayısı</b>' })
                                        ]
                                    }))
                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 4,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Twitter: Paylaşım Yapan Kullanıcılar #100 (Takipçi Sayısına Göre)</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
                                }
                                else
                                {
                                    //
                                }

                                if ((module.users.buckets).length)
                                {
                                    var table = __table_generate('twitterUsers')
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.users.buckets))
                                        card.children('input[data-table=key]').val('twitterUsers')

                                    $.each(module.users.buckets, function(bucket_key, bucket) {
                                        table.children('tbody').append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.user.name }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': '@' + bucket.properties.hits.hits[0]._source.user.screen_name,
                                                            'data-update-click': true,
                                                            'data-module': 'twitter',
                                                            'html': '@' + bucket.properties.hits.hits[0]._source.user.screen_name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )
                                    })

                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Adı</b>' }),
                                            $('<th />', { 'html': '<b>Kullanıcı Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Tweet Sayısı</b>' })
                                        ]
                                    }))
                                    table.children('thead').prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Twitter: Paylaşım Yapan Kullanıcılar #100</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.users.buckets))
                                        card.children('input[data-table=key]').val('youtubeUsers')

                                    $.each(module.users.buckets, function(bucket_key, bucket) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': 'channel.id:"' + bucket.key + '"',
                                                            'data-update-click': true,
                                                            'data-module': 'youtube_video',
                                                            'html': bucket.key
                                                        })
                                                    }),
                                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.channel.title }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Kanal Kimliği</b>' }),
                                            $('<th />', { 'html': '<b>Kanal Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Video Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>YouTube: Yükleme Yapan Kullanıcılar #100</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.users.buckets))
                                        card.children('input[data-table=key]').val('youtubeComments')

                                    $.each(module.users.buckets, function(bucket_key, bucket) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': 'channel.id:"' + bucket.key + '"',
                                                            'data-update-click': true,
                                                            'data-module': 'youtube_comment',
                                                            'html': bucket.key
                                                        })
                                                    }),
                                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.channel.title }),
                                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Kanal Kimliği</b>' }),
                                            $('<th />', { 'html': '<b>Kanal Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Yorum Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>YouTube: Yorum Yapan Kullanıcılar #100</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.sites))
                                        card.children('input[data-table=key]').val('sozlukSites')

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': '#' + o.id }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'sozluk',
                                                            'html': o.name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Site Kimliği</b>' }),
                                            $('<th />', { 'html': '<b>Sözlük Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Entry Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Paylaşım Yapılan Sözlükler</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
                                }
                                else
                                {
                                    //
                                }

                                if ((module.users).length)
                                {
                                    var table = __table_generate('sozlukUsers')
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.users))
                                        card.children('input[data-table=key]').val('sozlukUsers')

                                    $.each(module.users, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': o.site }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': 'author:"' + o.name + '"',
                                                            'data-update-click': true,
                                                            'data-module': 'sozluk',
                                                            'html': o.name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Sözlük Adı</b>' }),
                                            $('<th />', { 'html': '<b>Yazar Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Entry Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Paylaşım Yapan Sözlük Yazarları</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
                                }
                                else
                                {
                                    //
                                }

                                if ((module.topics).length)
                                {
                                    var table = __table_generate('sozlukTopics')
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.topics))
                                        card.children('input[data-table=key]').val('sozlukTopics')

                                    $.each(module.topics, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': o.site }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': '"' + o.title + '"',
                                                            'data-update-click': true,
                                                            'data-module': 'sozluk',
                                                            'html': o.title
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Sözlük Adı</b>' }),
                                            $('<th />', { 'html': '<b>Başlık</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Entry Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>En Çok Cevap Alan Sözlük Başlıkları</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.sites))
                                        card.children('input[data-table=key]').val('newsSites')

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': '#' + o.id }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'news',
                                                            'html': o.name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Site Kimliği</b>' }),
                                            $('<th />', { 'html': '<b>Site Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Haber Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Haber Yapan Siteler #100</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.sites))
                                        card.children('input[data-table=key]').val('blogSites')

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': '#' + o.id }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'blog',
                                                            'html': o.name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Site Kimliği</b>' }),
                                            $('<th />', { 'html': '<b>Blog Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>Blog Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>Blog Yazan Siteler</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.sites))
                                        card.children('input[data-table=key]').val('shoppingSites')

                                    $.each(module.sites, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': '#' + o.id }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table',
                                                            'data-search': 'site_id:' + o.id,
                                                            'data-update-click': true,
                                                            'data-module': 'shopping',
                                                            'html': o.name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Site Kimliği</b>' }),
                                            $('<th />', { 'html': '<b>Site Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>İlan Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>E-ticaret: İlan Paylaşılan Siteler</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
                                }
                                else
                                {
                                    //
                                }

                                if ((module.users).length)
                                {
                                    var table = __table_generate('shoppingUsers')
                                    var card = table.closest('.card');
                                        card.children('input[data-table=value]').val(JSON.stringify(module.users))
                                        card.children('input[data-table=key]').val('shoppingUsers')

                                    $.each(module.users, function(key, o) {
                                        table.append(
                                            $('<tr />', {
                                                'html': [
                                                    $('<td />', { 'html': o.site }),
                                                    $('<td />', {
                                                        'html': $('<a />', {
                                                            'href': '#',
                                                            'class': 'd-table grey-text',
                                                            'data-search': 'seller.name:"' + o.name + '"',
                                                            'data-update-click': true,
                                                            'data-module': 'shopping',
                                                            'html': o.name
                                                        })
                                                    }),
                                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                                ]
                                            })
                                        )
                                    })

                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', { 'html': '<b>Site Adı</b>' }),
                                            $('<th />', { 'html': '<b>Satıcı Adı</b>' }),
                                            $('<th />', { 'class': 'right-align', 'html': '<b>İlan Sayısı</b>' })
                                        ]
                                    }))
                                    table.prepend($('<tr />', {
                                        'html': [
                                            $('<th />', {
                                                'colspan': 3,
                                                'class': 'yellow lighten-4 pl-1 pr-1',
                                                'html': '<b>E-ticaret: İlan Paylaşan Kullanıcılar</b>' + '<br />' + query
                                            })
                                        ]
                                    }))
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
                    const youtubeVideoHashtagChartOption = JSON.parse(JSON.stringify(options));

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
                    twitterHashtagChartOption['chart']['events'] = {
                        'click': function(event, chartContext, config) {
                            //console.log(chartContext)
                        }
                    };

                    youtubeVideoHashtagChartOption['chart']['type'] = 'bar';
                    youtubeVideoHashtagChartOption['plotOptions'] = {
                        bar: {
                            distributed: true,
                            horizontal: true,
                            barHeight: '100%',
                            dataLabels: { position: 'bottom' }
                        }
                    };
                    youtubeVideoHashtagChartOption['subtitle'] = { 'text': 'YouTube Hashtag Grafiği' };
                    youtubeVideoHashtagChartOption['chart']['events'] = {
                        'click': function(event, chartContext, config) {
                            //console.log(chartContext)
                        }
                    };

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
                    var youtube_video_hits = false;

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

                            if (module_key == 'youtube_video')
                            {
                                youtube_video_hits = true;
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
                            case 'youtube_video':
                                youtubeVideoHashtagChartOption['series'] = [
                                    {
                                        name: 'Video',
                                        data: datas
                                    }
                                ];

                                youtubeVideoHashtagChartOption['xaxis'] = {
                                    categories: categories
                                };
                            break;
                        }
                    })

                    if (twitter_hits)
                    {
                        __chart_generate('twitterHashtag', __.data())

                        chartToJson('#twitterHashtagChart', twitterHashtagChartOption)

                        var twitterChart = new ApexCharts(document.querySelector('#twitterHashtagChart'), twitterHashtagChartOption);
                            twitterChart.render()

                        $('#twitterHashtagChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Twitter paylaşımlarında hashtag verisi bulunamadı!' }, 200)
                    }

                    if (youtube_video_hits)
                    {
                        __chart_generate('youtubeVideoHashtag', __.data())

                        chartToJson('#youtubeVideoHashtagChart', youtubeVideoHashtagChartOption)

                        var youtubeVideoChart = new ApexCharts(document.querySelector('#youtubeVideoHashtagChart'), youtubeVideoHashtagChartOption);
                            youtubeVideoChart.render()

                        $('#youtubeVideoHashtagChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'YouTube paylaşımlarında hashtag verisi bulunamadı!' }, 200)
                    }

                    if (instagram_hits)
                    {
                        __chart_generate('instagramHashtag', __.data())

                        chartToJson('#instagramHashtagChart', instagramHashtagChartOption)

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
                    __chart_generate('consumer', __.data());

                    const consumerChartOption = JSON.parse(JSON.stringify(options));

                    consumerChartOption['stroke']['width'] = 4;
                    consumerChartOption['xaxis']['title']['text'] = 'Müşteri Grafiği';
                    consumerChartOption['xaxis']['categories'] = [ 'İstek', 'Soru', 'Şikayet', 'Haber' ];

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

                        var req = module.req.doc_count;
                        var que = module.que.doc_count;
                        var cmp = module.cmp.doc_count;
                        var nws = module.nws.doc_count;

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

                    chartToJson('#consumerChart', consumerChartOption)

                    var consumerChart = new ApexCharts(document.querySelector('#consumerChart'), consumerChartOption);
                        consumerChart.render()
                break;
                case 'sentiment':
                    __chart_generate('sentiment', __.data());

                    const sentimentChartOption = JSON.parse(JSON.stringify(options));

                    sentimentChartOption['stroke']['width'] = 4;
                    sentimentChartOption['xaxis']['title']['text'] = 'Duygu Grafiği';
                    sentimentChartOption['xaxis']['categories'] = [ 'Pozitif', 'Nötr', 'Negatif', 'Nefret Söylemi' ];

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

                        var pos = module.pos.doc_count;
                        var neu = module.neu.doc_count;
                        var neg = module.neg.doc_count;
                        var hte = module.hte.doc_count;

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

                    chartToJson('#sentimentChart', sentimentChartOption)

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
                        __chart_generate('gender', __.data());

                        chartToJson('#genderChart', genderChartOption)

                        var genderChart = new ApexCharts(document.querySelector('#genderChart'), genderChartOption);
                            genderChart.render()

                        $('#genderChart').removeClass('hide')
                    }
                    else
                    {
                        M.toast({ html: 'Paylaşımlarda cinsiyet verisi bulunamadı!' }, 200)
                    }
                break;
                case 'category':
                    var _items_ = {};
                    var chart_data = false;

                    $.each(obj.data, function(module_key, module) {
                        $.each(module.category.buckets, function(key, bucket) {
                            chart_data = true;
                            _items_[bucket.key] = _items_[bucket.key] ? _items_[bucket.key] + bucket.doc_count : bucket.doc_count;
                        })
                    })

                    if (chart_data)
                    {
                        const categoryChartOption = JSON.parse(JSON.stringify(options));

                        __chart_generate('category', __.data())

                        categoryChartOption['chart']['type'] = 'bar';
                        categoryChartOption['chart']['height'] = 500;
                        categoryChartOption['plotOptions'] = {
                            bar: {
                                distributed: true,
                                horizontal: true,
                                barHeight: '100%',
                                dataLabels: { position: 'bottom' }
                            }
                        };
                        categoryChartOption['subtitle'] = { 'text': 'Kategori Grafiği' };

                        var categories = [];
                        var datas = [];

                        $.each(_items_, function(key, value) {
                            categories.push(key)
                            datas.push(value)
                        })

                        $('#categoryChart').removeClass('hide')

                        categoryChartOption['series'] = [
                            { name: 'İçerik', data: datas }
                        ];

                        categoryChartOption['xaxis'] = { categories: categories };

                        chartToJson('#categoryChart', categoryChartOption)

                        var categoryChart = new ApexCharts(document.querySelector('#categoryChart'), categoryChartOption);
                            categoryChart.render()
                    }
                    else
                    {
                        M.toast({ html: 'Kategorize edilmiş içerik bulunamadı.' }, 200)
                    }
                break;
            }

            $('.tabs').tabs('select', 'chart-tab')
        }
    }

    function __map(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.news && obj.data.news.locals.buckets.length)
            {
                var chart = $('#local_press-chart');

                if (chart.length)
                {
                    chart.remove()
                }

                var chart = $('<div />', {
                    'id': 'local_press-chart',
                    'class': 'chart',
                    'html': [
                        $('<h6 />', {
                            'html': 'Yerel Basın - ' + $('input[name=string]').val() + ' / ' + $('input[name=start_date]').val() + ' - ' + $('input[name=end_date]').val()
                        }),
                        $('<div />', {
                            'class': 'tr-map'
                        }),
                        $('<div />', {
                            'class': 'd-flex mb-2',
                            'html': $('<a />', {
                                'class': 'btn-flat waves-effect d-flex',
                                'data-report-type': 'tr_map',
                                'data-trigger': 'report-chart',
                                'data-title': __.data('title'),
                                'data-subtitle': __.data('subtitle'),
                                'html': [
                                    $('<i />', {
                                        'class': 'material-icons align-self-center mr-1',
                                        'html': 'note_add'
                                    }),
                                    $('<span />', {
                                        'class': 'align-self-center',
                                        'html': 'Grafiği Rapora Ekle'
                                    })
                                ]
                            })
                        }),
                        $('<input />', {
                            'type': 'hidden',
                            'data-chart': 'value',
                            'value': JSON.stringify(obj.data.news.locals.buckets)
                        })
                    ]
                })

                var total = 0;

                $.each(obj.data.news.locals.buckets, function(key, o) {
                    total = total + o.doc_count;
                })

                $.each(obj.data.news.locals.buckets, function(key, o) {
                    var per = parseInt(o.doc_count*255)/total;
                    var cr = per,
                        cg = 0,
                        cb = 0,
                        color = 'rgba(' + cr + ', ' + cg + ', ' + cb + ')';

                    chart.children('.tr-map').append($('<small />', {
                        'class': 'state state-' + getSlug(o.key),
                        'data-title': o.key,
                        'html': o.doc_count,
                        'css': { 'background-color': color }
                    }))
                })

                chart.prependTo('#chart-tab')
            }
            else
            {
                M.toast({ html: 'Yerel haber bulunamadı.' }, 200)
            }

            $('.tabs').tabs('select', 'chart-tab')
        }
    }
@endpush

@section('panel-icon', 'pie_chart')
@section('panel')
    <div class="collection collection-unstyled">
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="histogram" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Zaman Grafiği">Zaman İstatistikleri</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="place" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Lokasyon Grafiği" data-subtitle="Konum bilgisi paylaşan kullanıcılardan elde edilmiş başlıca lokasyonlar.">Lokasyon</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="platform" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Platform Grafiği" data-subtitle="Tweet paylaşımında kullanılan başlıca uygulamalar.">Platform</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="sentiment" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Duygu Grafiği">Duygu</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="consumer" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Soru, İstek, Şikayet ve Haber Grafiği">Soru, İstek, Şikayet ve Haber</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="gender" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Cinsiyet Grafiği">Cinsiyet Grafiği</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="author" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Bahsedenler">@bahsedenler</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="hashtag" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Hashtag Grafiği" data-subtitle="Konu ile birlikte kullanılan başlıca hashtagler.">#hashtagler</a>
        <a href="#" class="collection-item json loading" data-callback="__chart" data-type="category" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Kategori Grafiği" data-subtitle="Verilerin genel kategori dağılımı.">Kategori</a>
        <a href="#" class="collection-item json loading" data-callback="__map" data-type="local_press" data-href="{{ route('search.aggregation') }}" data-method="post" data-include="{{ $elements }}" data-title="Yerel Basın">Yerel Basın</a>
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
                                @php
                                    $hints = [
                                        'Bir Twitter kullanıcısını aramak için, <span class="green darken-2 white-text">@KullaniciAdı</span> olarak arama gerçekleştirebilirsiniz. Büyük/Küçük harf duyarlılığına ve tarih aralığının geniş olmasına da dikkat edin!',
                                    ];

                                    shuffle($hints);
                                @endphp

                                <div class="pt-1 pb-1">
                                    <div class="green-text text-darken-2 mt-1">
                                        @component('components.alert')
                                            @slot('icon', 'lightbulb_outline')
                                            @slot('text', $hints[0])
                                        @endcomponent
                                    </div>
                                </div>
                            </li>
                            <li class="collection-item model hide"></li>
                        </ul>

                        <a href="#"
                           class="more hide json"
                           id="search-more_button"
                           data-json-target="ul#search">Daha Fazla</a>
                    </div>
                    <div id="chart-tab" style="display: none;"></div>
                </div>
            </div>
        </div>
        <div class="col hide-on-med-and-down hide-on-large-only show-on-extra-large xl4">
            <div class="banner mb-1 lighten-4 z-depth-1 hide">
                <p class="white-text" data-name="text"></p>
                <div class="banner-overlay" data-name="overlay"></div>
            </div>

            <div class="right-align">
                <a href="#" class="btn-flat btn-floating waves-effect" data-report-type="stats" data-trigger="stats-more" data-tooltip="Sayacı Rapora Ekle">
                    <i class="material-icons">note_add</i>
                </a>
                <a href="#" class="btn-flat btn-floating waves-effect" data-trigger="stats-more" data-tooltip="Sayaç Detaylarını Göster">
                    <i class="material-icons">settings_input_svideo</i>
                </a>
                <a href="#" class="btn-flat btn-floating waves-effect" data-image="#banner-hits" data-tooltip="Sayaç Görüntüsünü Kaydet">
                    <i class="material-icons">save</i>
                </a>
                <table id="banner-hits" class="mb-1">
                    <tbody>
                        <tr>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="twitter-tweet" style="font-weight: bold;">0</strong>
                                <span class="grey-text">tweet</span>
                            </td>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="instagram-media" style="font-weight: bold;">0</strong>
                                <span class="grey-text">Instagram</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">RT, ALINTI, CEVAP</small>
                                        <small class="pl-1" data-name="twitter-reach">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL KULLANICI</small>
                                        <small class="pl-1" data-name="twitter-unique_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TANINMIŞ HESAP</small>
                                        <small class="pl-1" data-name="twitter-verified_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">ORTALAMA TAKİPÇİ</small>
                                        <small class="pl-1" data-name="twitter-followers">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">MENTION</small>
                                        <small class="pl-1" data-name="twitter-mentions">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">HASHTAG</small>
                                        <small class="pl-1" data-name="twitter-hashtags">0</small>
                                    </span>
                                </p>
                            </td>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL KULLANICI</small>
                                        <small class="pl-1" data-name="instagram-unique_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">MENTION</small>
                                        <small class="pl-1" data-name="instagram-mentions">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">HASHTAG</small>
                                        <small class="pl-1" data-name="instagram-hashtags">0</small>
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="youtube-comment" style="font-weight: bold;">0</strong>
                                <span class="grey-text">youtube yorum</span>
                            </td>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="youtube-video" style="font-weight: bold;">0</strong>
                                <span class="grey-text">youtube</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL KULLANICI</small>
                                        <small class="pl-1" data-name="youtube_comment-unique_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL VİDEO</small>
                                        <small class="pl-1" data-name="youtube_comment-unique_videos">0</small>
                                    </span>
                                </p>
                            </td>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL KULLANICI</small>
                                        <small class="pl-1" data-name="youtube_video-unique_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">HASHTAG</small>
                                        <small class="pl-1" data-name="youtube_video-hashtags">0</small>
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="media-article" style="font-weight: bold;">0</strong>
                                <span class="grey-text">haber</span>
                            </td>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="blog-document" style="font-weight: bold;">0</strong>
                                <span class="grey-text">blog</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL SİTE</small>
                                        <small class="pl-1" data-name="news-unique_sites">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">YEREL MEDYA</small>
                                        <small class="pl-1" data-name="news-local_states">0</small>
                                    </span>
                                </p>
                            </td>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL SİTE</small>
                                        <small class="pl-1" data-name="blog-unique_sites">0</small>
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="shopping-product" style="font-weight: bold;">0</strong>
                                <span class="grey-text">ilan</span>
                            </td>
                            <td style="font-size: 20px; text-transform: uppercase;" class="pb-0 right-align">
                                <strong data-name="sozluk-entry" style="font-weight: bold;">0</strong>
                                <span class="grey-text">sözlük</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL SATICI</small>
                                        <small class="pl-1" data-name="shopping-unique_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL SİTE</small>
                                        <small class="pl-1" data-name="shopping-unique_sites">0</small>
                                    </span>
                                </p>
                            </td>
                            <td style="padding: 0; text-transform: uppercase; vertical-align: top;" class="right-align">
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL YAZAR</small>
                                        <small class="pl-1" data-name="sozluk-unique_users">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL BAŞLIK</small>
                                        <small class="pl-1" data-name="sozluk-unique_topics">0</small>
                                    </span>
                                </p>
                                <p class="mb-0 hide" data-stat>
                                    <span class="d-flex justify-content-end">
                                        <small class="grey-text">TEKİL SÖZLÜK</small>
                                        <small class="pl-1" data-name="sozluk-unique_sites">0</small>
                                    </span>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                <h6 class="blue-grey-text">Kategori</h6>
                <div class="d-flex flex-wrap">
                    <label class="flex-fill" style="width: 100%;">
                        <input data-update type="radio" name="category" id="category" value="" checked />
                        <span>Tümü</span>
                    </label>
                    @foreach(config('system.analysis.category.types') as $key => $cat)
                        <label class="flex-fill" style="width: 50%;">
                            <input data-update type="radio" name="category" id="category" value="{{ $key }}" />
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

        window.clearTimeout(updateTimer)

        updateTimer = window.setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 400)
    }).on('click', '[data-trigger=select-allSources]', function() {
        $('input[name=modules]').prop('checked', true)

        updateTimer = window.setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 400)
    })

    $(document).on('click', '[data-module]', function() {
        var __ = $(this);

        $('input[name=modules]').prop('checked', false)
        $('input[name=modules][value=' + __.data('module') + ']').prop('checked', true)

        updateTimer = window.setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 600)
    }).on('change', 'input[name=reverse]', function() {
        if ($(this).is(':checked'))
        {
            $('select[name=twitter_sort]').val('').formSelect()
            $('select[name=twitter_sort_operator]').val('asc').formSelect()
        }
    }).on('change', 'select[name=twitter_sort]', function() {
        if ($(this).val != '')
        {
            $('input[name=reverse]').prop('checked', false)
        }
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
        <ul class="collection collection-unstyled">
            @foreach (config('system.modules') as $key => $module)
                <li class="collection-item">
                    @if ($key == 'twitter')
                        <div class="d-flex justify-content-between mb-2">
                            <label class="module-label">
                                <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                                <span>{{ $module }}</span>
                            </label>

                            <label class="module-label" data-tooltip="Olive, Twitter iyi sonuç algoritması." data-position="left">
                                <input data-update name="sharp" checked value="on" type="checkbox" />
                                <span>İyi Sonuç</span>
                            </label>
                        </div>
                        <div class="d-flex">
                            <div class="input-field">
                                <select name="twitter_sort" id="twitter_sort" data-update>
                                    <option value="">Normal</option>
                                    <option value="counts.favorite">Favori</option>
                                    <option value="counts.retweet">ReTweet</option>
                                    <option value="counts.quote">Alıntı</option>
                                    <option value="counts.reply">Cevap</option>
                                    <option value="" disabled>---</option>
                                    <option value="user.counts.followers">Takipçi</option>
                                    <option value="user.counts.friends">Takip</option>
                                    <option value="user.counts.statuses">Tweet</option>
                                </select>
                                <label>Twitter Sıralaması</label>
                            </div>
                            <div class="input-field">
                                <select name="twitter_sort_operator" id="twitter_sort_operator" data-update>
                                    <option value="desc">Azalan</option>
                                    <option value="asc">Artan</option>
                                </select>
                            </div>
                        </div>
                    @elseif ($key == 'news')
                        <div class="d-flex justify-content-between mb-2">
                            <label class="module-label">
                                <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                                <span>{{ $module }}</span>
                            </label>
                        </div>
                        <div class="input-field">
                            <select name="state" id="state" data-update>
                                <option value="">Hepsi</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->name }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            <label>Yerel Basın</label>
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
            <li class="collection-item nothing hide">
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

        $('input[name=consumer_que]').prop('checked', option.consumer_que ? true : false)
        $('input[name=consumer_req]').prop('checked', option.consumer_req ? true : false)
        $('input[name=consumer_cmp]').prop('checked', option.consumer_cmp ? true : false)
        $('input[name=consumer_nws]').prop('checked', option.consumer_nws ? true : false)

        $('input[name=gender][value=' + option.gender + ']').prop('checked', true)

        $('select[name=take]').find('option[value=' + option.take + ']').prop('selected', true);
        $('select[name=take]').formSelect();

        $('input[name=modules]').prop('checked', false)
        $('input[name=category]').prop('checked', false)
        $('select[name=state]').val('').formSelect()
        $('select[name=twitter_sort]').val('').formSelect()
        $('select[name=twitter_sort_operator]').val('asc').formSelect()

        $.each(option.modules, function(key, module) {
            $('input[name=modules][value=' + module + ']').prop('checked', true)
        })

        if (option.category)
        {
            $('input[name=category][value=' + option.category + ']').prop('checked', true)
        }
        else if (option.category === null)
        {
            $('input[name=category][value=""]').prop('checked', true)
        }

        if (option.state)
        {
            $('select[name=state]').val(option.state).formSelect()
        }

        if (option.twitter_sort)
        {
            $('select[name=twitter_sort]').val(option.twitter_sort).formSelect()
        }

        if (option.twitter_sort_operator)
        {
            $('select[name=twitter_sort_operator]').val(option.twitter_sort_operator).formSelect()
        }

        updateTimer = window.setTimeout(function() {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }, 200)
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
                    var selector = __.children('[data-id=' + o.id + ']');

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
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.table2excel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/speakingurl.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush
