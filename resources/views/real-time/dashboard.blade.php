@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => $pin_group ? [
        [
            'text' => 'Gerçek Zamanlı',
            'link' => route('realtime')
        ],
        [
            'text' => $pin_group->name
        ]
    ]
    :
    [
        [
            'text' => 'Gerçek Zamanlı'
        ]
    ],
    'dock' => true
])

@push('local.styles')
    #keyword-groups > .collection-item {
        padding-right: 24px;
        padding-left: 24px;
    }

    .time-line > .collection {
        min-height: 800px;
    }
@endpush

@push('local.scripts')
    @if ($pin_group)
        var buffer = [
            { 'id': 1, 'module': 'twitter', 'text': 'Award-winning 🌍 humanitarian & human rights defender〡📚 Author #VikingsKurdishLove〡Lover #Art #History Culture #MedicalResearch〡📩 ☞ https://t.co/kHmLxEERgA 💐💐💐' },
            { 'id': 2, 'module': 'twitter', 'text': 'Geçmişte söylediğimiz sözlerin arkasındayız,her yıl katıldığımız Türkçe olimpiyatlarında söylediğimiz sözlerinde arkasındayız.' },
            { 'id': 3, 'module': 'twitter', 'text': 'Biz çok büyük bir camiayız. Güzel mesajlarınız için çok teşekkür ederim. Hepinizi çok seviyorum. 4. Yıldız birlikte kazanacağız.' },
            { 'id': 4, 'module': 'twitter', 'text': 'Good Night #Sinjar &amp; #Kobane\n💕\nYOUR PEACEFUL SUPPORTERS KILLED TO SILENCE U!\n💕\nhttp://t.co/aUNFTSaIGr \n#SaveYazidis #SaveKobane #DrWidad' },
            { 'id': 5, 'module': 'twitter', 'text': 'İşte F.Bahçe ve G.Sarayda bileti kesilen oyuncular\n4. yıldız mücadelesi veren Fenerbahçe ve Galatasarayda devre... http://t.co/Su4irPq8Z7' },
            { 'id': 6, 'module': 'twitter', 'text': 'Avukat / Parézer/ Attorney at Law - Diyarbakır Barosu Başkanı / Seroké Baroya Amedé / Chairman of Diyarbakır Bar Association' },
            { 'id': 7, 'module': 'twitter', 'text': 'birazdan gelip panel kürsüsüne oturacak da bize cesur ve mütevazı bir hak savunucusu nasıl olur gösterecek gibi... https://t.co/uGjWBtZNp1' },
            { 'id': 8, 'module': 'twitter', 'text': 'Diyarbakırın simgelerinden Dört Ayaklı Minarenin ayaklarına silahlı SUIKAST... https://t.co/0SJVF1NFz8' },
            { 'id': 9, 'module': 'twitter', 'text': 'Avukat / Parézer/ Attorney at Law - Diyarbakır Barosu Başkanı / Seroké Baroya Amedé / Chairman of Diyarbakır Bar Association' },
            { 'id': 10, 'module': 'twitter', 'text': 'Biz bugün hayatını barış ve insan hakları mücadelesine adamış çok önemli bir hukukçuyu kaybettik. Lütfen onu daha iyi tanıyın ve unutmayın.' },
            { 'id': 11, 'module': 'twitter', 'text': 'Aranızda belki Tahir Elçiyi gerektiği gibi tanımayanlar vardır. Tahir Elçi ülkenin en iyi insan hakları hukukçularından biridir - biriydi.' },
            { 'id': 12, 'module': 'twitter', 'text': 'Award-winning 🌍 humanitarian & human rights defender〡📚 Author #VikingsKurdishLove〡Lover #Art #History Culture #MedicalResearch〡📩 ☞ https://t.co/kHmLxEERgA 💐💐💐' },
            { 'id': 13, 'module': 'twitter', 'text': 'Geçmişte söylediğimiz sözlerin arkasındayız,her yıl katıldığımız Türkçe olimpiyatlarında söylediğimiz sözlerinde arkasındayız.' },
            { 'id': 14, 'module': 'twitter', 'text': 'Biz çok büyük bir camiayız. Güzel mesajlarınız için çok teşekkür ederim. Hepinizi çok seviyorum. 4. Yıldız birlikte kazanacağız.' },
            { 'id': 15, 'module': 'twitter', 'text': 'Good Night #Sinjar &amp; #Kobane\n💕\nYOUR PEACEFUL SUPPORTERS KILLED TO SILENCE U!\n💕\nhttp://t.co/aUNFTSaIGr \n#SaveYazidis #SaveKobane #DrWidad' },
            { 'id': 16, 'module': 'twitter', 'text': 'İşte F.Bahçe ve G.Sarayda bileti kesilen oyuncular\n4. yıldız mücadelesi veren Fenerbahçe ve Galatasarayda devre... http://t.co/Su4irPq8Z7' },
            { 'id': 17, 'module': 'twitter', 'text': 'Avukat / Parézer/ Attorney at Law - Diyarbakır Barosu Başkanı / Seroké Baroya Amedé / Chairman of Diyarbakır Bar Association' },
            { 'id': 18, 'module': 'twitter', 'text': 'birazdan gelip panel kürsüsüne oturacak da bize cesur ve mütevazı bir hak savunucusu nasıl olur gösterecek gibi... https://t.co/uGjWBtZNp1' },
            { 'id': 19, 'module': 'twitter', 'text': 'Diyarbakırın simgelerinden Dört Ayaklı Minarenin ayaklarına silahlı SUIKAST... https://t.co/0SJVF1NFz8' },
            { 'id': 20, 'module': 'twitter', 'text': 'Avukat / Parézer/ Attorney at Law - Diyarbakır Barosu Başkanı / Seroké Baroya Amedé / Chairman of Diyarbakır Bar Association' },
            { 'id': 21, 'module': 'twitter', 'text': 'Biz bugün hayatını barış ve insan hakları mücadelesine adamış çok önemli bir hukukçuyu kaybettik. Lütfen onu daha iyi tanıyın ve unutmayın.' },
            { 'id': 22, 'module': 'twitter', 'text': 'Aranızda belki Tahir Elçiyi gerektiği gibi tanımayanlar vardır. Tahir Elçi ülkenin en iyi insan hakları hukukçularından biridir - biriydi.' },
            { 'id': 23, 'module': 'twitter', 'text': 'Award-winning 🌍 humanitarian & human rights defender〡📚 Author #VikingsKurdishLove〡Lover #Art #History Culture #MedicalResearch〡📩 ☞ https://t.co/kHmLxEERgA 💐💐💐' },
            { 'id': 24, 'module': 'twitter', 'text': 'Geçmişte söylediğimiz sözlerin arkasındayız,her yıl katıldığımız Türkçe olimpiyatlarında söylediğimiz sözlerinde arkasındayız.' },
            { 'id': 25, 'module': 'twitter', 'text': 'Biz çok büyük bir camiayız. Güzel mesajlarınız için çok teşekkür ederim. Hepinizi çok seviyorum. 4. Yıldız birlikte kazanacağız.' },
            { 'id': 26, 'module': 'twitter', 'text': 'Good Night #Sinjar &amp; #Kobane\n💕\nYOUR PEACEFUL SUPPORTERS KILLED TO SILENCE U!\n💕\nhttp://t.co/aUNFTSaIGr \n#SaveYazidis #SaveKobane #DrWidad' },
            { 'id': 27, 'module': 'twitter', 'text': 'İşte F.Bahçe ve G.Sarayda bileti kesilen oyuncular\n4. yıldız mücadelesi veren Fenerbahçe ve Galatasarayda devre... http://t.co/Su4irPq8Z7' },
            { 'id': 28, 'module': 'twitter', 'text': 'Avukat / Parézer/ Attorney at Law - Diyarbakır Barosu Başkanı / Seroké Baroya Amedé / Chairman of Diyarbakır Bar Association' },
            { 'id': 29, 'module': 'twitter', 'text': 'birazdan gelip panel kürsüsüne oturacak da bize cesur ve mütevazı bir hak savunucusu nasıl olur gösterecek gibi... https://t.co/uGjWBtZNp1' },
            { 'id': 30, 'module': 'twitter', 'text': 'Diyarbakırın simgelerinden Dört Ayaklı Minarenin ayaklarına silahlı SUIKAST... https://t.co/0SJVF1NFz8' },
            { 'id': 31, 'module': 'twitter', 'text': 'Avukat / Parézer/ Attorney at Law - Diyarbakır Barosu Başkanı / Seroké Baroya Amedé / Chairman of Diyarbakır Bar Association' },
            { 'id': 32, 'module': 'twitter', 'text': 'Biz bugün hayatını barış ve insan hakları mücadelesine adamış çok önemli bir hukukçuyu kaybettik. Lütfen onu daha iyi tanıyın ve unutmayın.' },
            { 'id': 33, 'module': 'twitter', 'text': 'Aranızda belki Tahir Elçiyi gerektiği gibi tanımayanlar vardır. Tahir Elçi ülkenin en iyi insan hakları hukukçularından biridir - biriydi.' }
        ];

        var time = 100;
        var liveTimer;

        $(window).on('load', function() {
            livePush()
        })

        var bucket = $('.time-line > .collection');
        var model = bucket.children('.model');

        function livePush()
        {
            if (buffer.length)
            {
                var obj = buffer[0];

                if (!$('#' + obj.module + '-' + obj.id).length)
                {
                    var item = model.clone();
                        item.find('[data-name=text]').html(obj.text)
                        item.find('[data-name=platform]').html(obj.module)

                        item.attr('id', obj.module + '-' + obj.id)
                            .hide(function() {
                                $(this).slideDown(100)
                            })
                            .removeClass('model d-none')

                        item.prependTo(bucket)
                }

                buffer.shift()

                if (bucket.children('.collection-item').length > 100)
                {
                    bucket.children('.collection-item:last-child').remove()
                }
            }

            window.clearTimeout(liveTimer);

            liveTimer = window.setTimeout(function() {
                livePush();
            }, time)
        }

        $(document).on('mouseenter', '.time-line > .collection', function() {
            time = 1000;
        }).on('mouseleave', '.time-line', function() {
            time = 100;
        }).on('click', '.time-line > .collection > .collection-item', function() {
            M.toast({ html: 'İçerik Pinlendi!', classes: 'orange darken-2' })

            var pins_button = $('[data-name=pins-button]');
            var pin_count = pins_button.children('span.count');
                pin_count.html(parseInt(pin_count.html()) + 1)
        })
    @else
        function __pin_groups(__, obj)
        {
            var ul = $('#pin-groups');
            var item_model = ul.children('.model');

            if (obj.status == 'ok')
            {
                item_model.addClass('d-none')

                if (obj.hits.length)
                {
                    $.each(obj.hits, function(key, o) {
                        var item = item_model.clone();
                            item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.id)

                            item.find('.name').html(o.name).attr('data-id', o.id)
                            item.find('[data-name=edit]').attr('data-id', o.id)
                            item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                            item.find('[data-name=count]').html(o.pins.length + ' pin')

                            item.appendTo(ul)
                    })
                }

                $('#home-loader').hide()
            }
        }

        function pin_group_modal()
        {
            var mdl = modal({
                'id': 'pin-group',
                'body': $('<form />', {
                    'action': '{{ route('realtime.pin.group') }}',
                    'id': 'pin-group-form',
                    'class': 'json',
                    'data-callback': '__pin_group_callback',
                    'html': [
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'name',
                                    'name': 'name',
                                    'type': 'text',
                                    'class': 'validate',
                                    'data-length': 32
                                }),
                                $('<label />', {
                                    'for': 'name',
                                    'html': 'Grup Adı'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Pin grubu için isim girin.'
                                })
                            ]
                        }),
                        $('<br />'),
                        $('<div />', {
                            'class': 'right-align',
                            'html': [
                               $('<a />', {
                                   'href': '#',
                                   'class': 'modal-close waves-effect btn-flat',
                                   'html': buttons.cancel
                               }),
                               $('<span />', {
                                   'html': ' '
                               }),
                               $('<a />', {
                                   'data-trigger': 'delete-pin-group',
                                   'href': '#',
                                   'class': 'waves-effect btn-flat red-text d-none',
                                   'html': buttons.remove
                               }),
                               $('<span />', {
                                   'html': ' '
                               }),
                               $('<button />', {
                                   'type': 'submit',
                                   'class': 'waves-effect btn',
                                   'data-submit': 'form#pin-group-form',
                                   'html': buttons.ok
                               })
                            ]
                        })
                    ]
                }),
                'size': 'modal-medium',
                'options': {
                    dismissible: false
                }
            });

            mdl.find('input[name=name]').characterCounter()

            M.updateTextFields()

            return mdl;
        }

        $(document).on('click', '[data-trigger=create-pin-group]', function() {
            var mdl = pin_group_modal();
                mdl.find('.modal-title').html('Grup Oluştur')
                mdl.find('form#pin-group-form').data('method', 'put')

                mdl.find('[name=name]').val('')

            $('[data-trigger=delete-pin-group]').removeAttr('data-id').addClass('d-none')
        })

        function __get_pin_group(__, obj)
        {
            if (obj.status == 'ok')
            {
                var mdl = pin_group_modal();
                    mdl.find('.modal-title').html('Grup Güncelle')
                    mdl.find('form#pin-group-form').data('id', obj.data.id)
                                                   .data('method', 'patch')
                    mdl.find('[name=name]').val(obj.data.name)

                $('[data-trigger=delete-pin-group]').data('id', obj.data.id).removeClass('d-none')
            }
        }

        function __pin_group_callback(__, obj)
        {
            if (obj.status == 'ok')
            {
                $('#modal-pin-group').modal('close')

                if (obj.type == 'created')
                {
                    vzAjax($('#pin-groups').data('skip', 0).addClass('json-clear'))
                }
                else if (obj.type == 'updated')
                {
                    $('#pin-groups').children('[data-id=' + obj.data.id + ']').find('.name').html(obj.data.name)
                }

                M.toast({
                    html: obj.type == 'created' ? 'Pin Grubu Oluşturuldu' : obj.type == 'updated' ? 'Pin Grubu Güncellendi' : 'İşlem Gerçekleşti',
                    classes: 'green darken-2'
                })
            }
        }

        $(document).on('click', '[data-trigger=delete-pin-group]', function() {
            var mdl = modal({
                    'id': 'pin-group-alert',
                    'body': 'Silmek istediğinizden emin misiniz?',
                    'size': 'modal-small',
                    'title': 'Sil',
                    'options': {}
                });

                mdl.find('.modal-footer')
                   .html([
                        $('<a />', {
                            'href': '#',
                            'class': 'modal-close waves-effect btn-flat',
                            'html': buttons.cancel
                        }),
                        $('<span />', {
                            'html': ' '
                        }),
                        $('<a />', {
                            'href': '#',
                            'class': 'waves-effect btn red json',
                            'html': buttons.ok,
                            'data-href': '{{ route('realtime.pin.group') }}',
                            'data-method': 'delete',
                            'data-id': $(this).data('id'),
                            'data-callback': '__delete_pin_group'
                        })
                   ])
        })

        function __delete_pin_group(__, obj)
        {
            if (obj.status == 'ok')
            {
                $('#pin-groups').children('[data-id=' + obj.data.id + ']').remove()

                $('#modal-pin-group-alert').modal('close')

                setTimeout(function() {
                    $('#modal-pin-group').modal('close')
                }, 200)

                M.toast({
                    html: 'Pin Grubu Silindi',
                    classes: 'green darken-2'
                })

                vzAjax($('#pin-groups').data('skip', 0).addClass('json-clear'))
            }
        }

        function __go_pins(__, obj)
        {
            if (obj.status == 'ok')
            {
                location.href = obj.route;
            }
        }
    @endif
@endpush

@section('content')
    @if ($pin_group)
        <div class="card time-line">
            <div class="card-content">
                <div class="d-flex justify-content-between">
                    <span class="card-title mb-0 align-self-center">{{ $pin_group->name }}</span>
                    <a class="btn-flat waves-effect" data-name="pins-button" href="#">Pinler (<span class="count">0</span>)</a>
                </div>
            </div>
            <div class="card-content cyan lighten-5">
                <p class="d-flex">
                    <img alt="Pin" src="{{ asset('img/pin.svg') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                    <span class="align-self-center">Pinlemek istediğiniz içeriğe tıklayın.</span>
                </p>
                <p class="d-flex">
                    <img alt="Pin" src="{{ asset('img/cold-plant.svg') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                    <span class="align-self-center">Akışı yavaşlatmak için fareyi akışın üzerine getirin.</span>
                </p>
            </div>
            <div class="collection">
                <a href="#" class="collection-item waves-effect d-none model grey-text">
                    <p data-name="platform" class="grey-text text-darken-4"></p>
                    <p data-name="text"></p>
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/9.jpg') }});">
                <span class="card-title white-text mb-0">Pin Grupları</span>
            </div>
            <div class="card-image">
                <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-pin-group">
                    <i class="material-icons black-text">add</i>
                </a>
            </div>
            <div class="card-content">
                <p class="grey-text">Pinleme başlığı altında gerçek zamanlı verileri kalıcı olarak tutabilirsiniz.</p>
                <p class="grey-text">Pinleme başlıklarını istediğiniz zaman PDF halinde rapor olarak alabilirsiniz.</p>
            </div>
            <nav class="grey darken-4">
                <div class="nav-wrapper">
                    <div class="input-field">
                        <input id="string"
                               name="string"
                               type="search"
                               class="validate json json-search"
                               data-json-target="#pin-groups"
                               placeholder="Ara" />
                        <label class="label-icon" for="string">
                            <i class="material-icons">search</i>
                        </label>
                        <i class="material-icons">close</i>
                    </div>
                </div>
            </nav>
            <ul id="pin-groups"
                 class="collection load json-clear" 
                 data-href="{{ route('realtime.pin.groups') }}"
                 data-skip="0"
                 data-take="5"
                 data-include="string"
                 data-more-button="#pin-groups-more_button"
                 data-callback="__pin_groups"
                 data-nothing>
                <li class="collection-item nothing d-none">
                    <div class="not-found">
                        <i class="material-icons">cloud</i>
                        <i class="material-icons">cloud</i>
                        <i class="material-icons">wb_sunny</i>
                    </div>
                </li>
                <li class="collection-item model d-none">
                    <span>
                        <span class="d-flex">
                            <a
                                class="btn-floating btn-small waves-effect red darken-1 json align-self-center"
                                href="#"
                                data-href="{{ route('realtime.pin.group') }}"
                                data-method="get"
                                data-callback="__get_pin_group"
                                data-name="edit">
                                <i class="material-icons">create</i>
                            </a>
                            <a
                                href="#"
                                class="align-self-center json name"
                                style="margin: 0 0 0 .4rem;"
                                data-href="{{ route('route.generate.id') }}"
                                data-name="realtime.stream"
                                data-callback="__go_pins"></a>
                        </span>
                        <time data-name="created-at" class="timeago grey-text"></time>
                    </span>
                    <small data-name="count" class="badge ml-auto"></small>
                </li>
            </ul>
        </div>

        @component('components.loader')
            @slot('color', 'red')
            @slot('id', 'home-loader')
        @endcomponent
        <div class="center-align">
            <button class="btn-flat waves-effect d-none json"
                    id="pin-groups-more_button"
                    type="button"
                    data-json-target="#pin-groups">Öncekiler</button>
        </div>
    @endif
@endsection

@section('dock')
    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/21.jpg') }});">
            <span class="card-title white-text mb-0">Kelime Grupları</span>
        </div>
        <div class="card-image">
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-keyword-group">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content" style="padding-bottom: 0;">
            <span data-name="keyword-group-count">0</span> / <span data-name="keyword-group-limit">0</span>
        </div>
        <ul class="collection load" 
             id="keyword-groups"
             data-href="{{ route('realtime.keyword.groups') }}"
             data-callback="__keyword_groups"
             data-nothing>
            <li class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                </div>
            </li>
            <li class="collection-item model d-none justify-content-between">
                <span data-name="name" class="align-self-center mr-auto"></span>
                @if ($pin_group)
                <div class="switch align-self-center">
                    <label>
                        <input type="checkbox" name="" id="" />
                        <span class="lever"></span>
                    </label>
                </div>
                @endif
                <a
                    class="btn-floating btn-small waves-effect json teal align-self-center"
                    data-href="{{ route('realtime.keyword.group') }}"
                    data-method="get"
                    data-callback="__get_keyword_group"
                    href="#">
                    <i class="material-icons">create</i>
                </a>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'red')
        @slot('id', 'keyword-group-loader')
    @endcomponent

    <div class="card-panel teal">
        Takip etmek istediğiniz<br/>Kelime Grubunu aktif edin.
    </div>
@endsection

@push('local.scripts')
    function __keyword_groups(__, obj)
    {
        var ul = $('#keyword-groups');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = ul.children('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-callback=__get_keyword_group]').attr('data-id', o.id)

                    if (!selector.length)
                    {
                        item.appendTo(ul)
                    }
                })
            }

            $('[data-name=keyword-group-count]').html(obj.hits.length)
            $('[data-name=keyword-group-limit]').html(obj.limit)

            $('#keyword-group-loader').hide()
        }
    }

    function keyword_group_modal()
    {
        var mdl = modal({
            'id': 'keyword-group',
            'body': $('<form />', {
                'action': '{{ route('realtime.keyword.group') }}',
                'id': 'keyword-group-form',
                'class': 'json',
                'data-callback': '__keyword_group_callback',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'name',
                                'name': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 10
                            }),
                            $('<label />', {
                                'for': 'name',
                                'html': 'Grup Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Kelime grubu için isim girin.'
                            })
                        ]
                    }),
                    $('<br />'),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<textarea />', {
                                'id': 'keywords',
                                'name': 'keywords',
                                'class': 'materialize-textarea validate',
                                'data-length': 64
                            }),
                            $('<label />', {
                                'for': 'keywords',
                                'html': 'Kelime Listesi'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Birden fazla anahtar kelime veya cümle için birden fazla satır kullanabilirsiniz.'
                            })
                        ]
                    }),
                    $('<br />'),
                    $('<div />', {
                        'class': 'collection',
                        'html': [
                        @foreach (config('app.modules') as $key => $module)
                            $('<label />', {
                                'class': 'collection-item waves-effect d-block',
                                'html': [
                                    $('<input />', {
                                        'name': 'module_{{ $key }}',
                                        'id': 'module_{{ $key }}',
                                        'value': '1',
                                        'type': 'checkbox'
                                    }),
                                    $('<span />', {
                                        'html': '{{ title_case($module) }} Verilerini Dahil Et'
                                    })
                                ]
                            }),
                        @endforeach
                        ]
                    }),
                    $('<br />'),
                    $('<div />', {
                        'class': 'right-align',
                        'html': [
                           $('<a />', {
                               'href': '#',
                               'class': 'modal-close waves-effect btn-flat',
                               'html': buttons.cancel
                           }),
                           $('<span />', {
                               'html': ' '
                           }),
                           $('<a />', {
                               'data-trigger': 'delete-keyword-group',
                               'href': '#',
                               'class': 'waves-effect btn-flat red-text d-none',
                               'html': buttons.remove
                           }),
                           $('<span />', {
                               'html': ' '
                           }),
                           $('<button />', {
                               'type': 'submit',
                               'class': 'waves-effect btn',
                               'data-submit': 'form#keyword-group-form',
                               'html': buttons.ok
                           })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'options': {
                dismissible: false
            }
        });

        mdl.find('input[name=name], textarea[name=keywords]').characterCounter()

        M.updateTextFields()

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-keyword-group]', function() {
        var mdl = keyword_group_modal();
            mdl.find('.modal-title').html('Grup Oluştur')
            mdl.find('form#keyword-group-form').data('method', 'put')

            mdl.find('[name=module_youtube]').prop('checked', false)
            mdl.find('[name=module_twitter]').prop('checked', false)
            mdl.find('[name=module_sozluk]').prop('checked', false)
            mdl.find('[name=module_news]').prop('checked', false)
            mdl.find('[name=module_shopping]').prop('checked', false)

            mdl.find('[name=name]').val('')
            mdl.find('[name=keywords]').val('')

        $('[data-trigger=delete-keyword-group]').removeAttr('data-id').addClass('d-none')
    })

    function __get_keyword_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = keyword_group_modal();
                mdl.find('.modal-title').html('Grup Güncelle')
                mdl.find('form#keyword-group-form').data('id', obj.data.id)
                                                   .data('method', 'patch')
                mdl.find('[name=name]').val(obj.data.name)
                mdl.find('[name=keywords]').val(obj.data.keywords)

                M.textareaAutoResize($('textarea[name=keywords]'))

                mdl.find('[name=module_youtube]').prop('checked', obj.data.module_youtube)
                mdl.find('[name=module_twitter]').prop('checked', obj.data.module_twitter)
                mdl.find('[name=module_sozluk]').prop('checked', obj.data.module_sozluk)
                mdl.find('[name=module_news]').prop('checked', obj.data.module_news)
                mdl.find('[name=module_shopping]').prop('checked', obj.data.module_shopping)

            $('[data-trigger=delete-keyword-group]').data('id', obj.data.id).removeClass('d-none')
        }
    }

    function __keyword_group_callback(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-keyword-group').modal('close')

            vzAjax($('#keyword-groups'))

            M.toast({
                html: obj.type == 'created' ? 'Grup Oluşturuldu' : obj.type == 'updated' ? 'Grup Güncellendi' : 'İşlem Gerçekleşti',
                classes: 'green darken-2'
            })
        }
    }

    $(document).on('click', '[data-trigger=delete-keyword-group]', function() {
        var mdl = modal({
                'id': 'keyword-group-alert',
                'body': 'Silmek istediğinizden emin misiniz?',
                'size': 'modal-small',
                'title': 'Sil',
                'options': {}
            });

            mdl.find('.modal-footer')
               .html([
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn red json',
                        'html': buttons.ok,
                        'data-href': '{{ route('realtime.keyword.group') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete_keyword_group'
                    })
               ])
    })

    function __delete_keyword_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#keyword-groups').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-keyword-group-alert').modal('close')

            setTimeout(function() {
                $('#modal-keyword-group').modal('close')
            }, 200)

            M.toast({
                html: 'Kelime Grubu Silindi',
                classes: 'green darken-2'
            })

            vzAjax($('#keyword-groups'))
        }
    }
@endpush
