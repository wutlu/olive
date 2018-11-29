@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı'
        ]
    ],
    'dock' => true
])

@push('local.styles')
    .groups > .collection-item {
        padding-right: 0;
        padding-left: 1rem;
    }
    .groups > .collection-item span.group-name {
        margin: 0 0 0 .5rem;
    }

    .time-line > .collection {
        overflow-y: scroll;
        height: 800px;
    }
@endpush

@push('local.scripts')
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
        { 'id': 11, 'module': 'twitter', 'text': 'Aranızda belki Tahir Elçiyi gerektiği gibi tanımayanlar vardır. Tahir Elçi ülkenin en iyi insan hakları hukukçularından biridir - biriydi.' }
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
                        .removeClass('model d-none');

                    item.prependTo(bucket)
            }

            buffer.shift()
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
        M.toast({ html: 'İçerik Pinlendi!', classes: 'red darken-2' })
    })

    $('input#name').characterCounter()

    function __collections(__, obj)
    {
        var ul = $('#pins');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                        item.find('[data-name=count]').html(20 + ' pin')

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }

    function __go(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = obj.route;
        }
    }

    function __groups(__, obj)
    {
        var ul = $('#groups');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)

                    if (!selector.length)
                    {
                        item.appendTo(ul)
                    }
                })
            }

            $('[data-name=group-count]').html(obj.hits.length)
            $('[data-name=group-limit]').html(obj.limit)

            $('#group-loader').hide()
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/9.jpg') }});">
            <span class="card-title white-text mb-0">Pin Grupları</span>
        </div>
        <div class="card-image">
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
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
                           data-json-target="#pins"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="pins"
             data-href="{{ route('admin.user.list.json') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#pins-more_button"
             data-callback="__collections"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                </div>
            </div>
            <a
                href="#"
                class="collection-item model d-none waves-effect json"
                data-href="{{ route('route.generate.id') }}"
                data-name="admin.user"
                data-callback="__go">
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
                <small data-name="count" class="badge ml-auto"></small>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'red')
        @slot('id', 'home-loader')
    @endcomponent
    <div class="center-align">
        <button class="btn-flat waves-effect d-none json"
                id="pins-more_button"
                type="button"
                data-json-target="#pins">Öncekiler</button>
    </div>

    <div class="card time-line">
        <div class="card-content">
            <span class="card-title mb-0">Veri Akışı</span>
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
@endsection

@section('dock')
    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/9.jpg') }});">
            <span class="card-title white-text mb-0">Kelime Grupları</span>
        </div>
        <div class="card-image">
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-group">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content" style="padding-bottom: 0;">
            <span data-name="group-count">0</span> / <span data-name="group-limit">0</span>
        </div>
        <ul class="collection groups load" 
             id="groups"
             data-href="{{ route('realtime.keyword.groups') }}"
             data-callback="__groups"
             data-nothing>
            <li class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                </div>
            </li>
            <li class="collection-item model d-none">
                <div class="d-flex justify-content-between">
                    <a class="material-icons" href="#">create</a>
                    <span class="group-name" data-name="name"></span>
                    @isset ($stream)
                    <div class="switch ml-auto">
                        <label>
                            <input type="checkbox" name="" id="" />
                            <span class="lever"></span>
                        </label>
                    </div>
                    @else
                    <div class="ml-auto"></div>
                    @endisset
                </div>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'red')
        @slot('id', 'group-loader')
    @endcomponent
@endsection

@push('local.scripts')
    function group_modal()
    {
        var mdl = modal({
            'id': 'group-form',
            'body': $('<form />', {
                'action': '{{ route('realtime.keyword') }}',
                'id': 'group-form',
                'class': 'json',
                'data-callback': '__group_update',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'name',
                                'name': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 16
                            }),
                            $('<label />', {
                                'for': 'name',
                                'html': 'Grup Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<p />', {
                        'class': 'teal-text',
                        'html': 'İçerik akışının sağlanacağı platformları seçin.'
                    }),
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
                                        'html': '{{ title_case($module) }} Verileri'
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
                               'data-trigger': 'delete',
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
                               'data-submit': 'form#group-form',
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

    $(document).on('click', '[data-trigger=create-group]', function() {
        var mdl = group_modal();
            mdl.find('.modal-title').html('Grup Oluştur')
            mdl.find('form#group-form').data('method', 'put')
    }).on('click', '[data-trigger=update-group]', function() {
        var mdl = group_modal();
            mdl.find('.modal-title').html('Grup Güncelle')
            mdl.find('form#group-form').data('id', $(this).data('id'))
                                       .data('method', 'patch')
    })

    function __group_update(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-group-form').modal('close')

            vzAjax($('#groups'))

            M.toast({
                html: 'Grup Oluşturuldu',
                classes: 'green darken-2'
            })
        }
    }
@endpush
