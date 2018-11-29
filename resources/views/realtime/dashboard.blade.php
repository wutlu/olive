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
                        .hide()
                        .removeClass('model d-none')
                        .slideDown(200);

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
        var ul = $('#users');
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

    var collection_timer;

    function __groups(__, obj)
    {
        var ul = $('#indices');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.uuid + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.uuid)

                        item.find('[data-name=health]').html(o.health)
                                                       .removeClass('green-text red-text yellow-text')
                                                       .addClass(o.health + '-text')
                        item.find('[data-name=count]').html(number_format(o['docs.count'] ? o['docs.count'] : 0))
                        item.find('[data-name=size]').html(o['store.size'])

                        if (!selector.length)
                        {
                            item.find('[data-name=name]').html(o.index)
                            item.appendTo(ul)
                        }
                })
            }

            $('#home-loader').hide()
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#indices'))
        }, 10000)
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/9.jpg') }});">
            <span class="card-title white-text mb-0">Pinleme Geçmişi</span>
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
                           data-json-target="#users"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="users"
             data-href="{{ route('admin.user.list.json') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#users-more_button"
             data-callback="__collections"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Pinleme Yok</p>
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
                id="users-more_button"
                type="button"
                data-json-target="#users">Öncekiler</button>
    </div>

    <div class="card time-line">
        <div class="card-content">
            <span class="card-title mb-0">Veri Akışı</span>
        </div>
        <div class="card-content cyan lighten-5">
            <p class="d-flex">
                <img alt="Pin" src="{{ asset('img/pin.svg') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                <span class="align-self-center">Pinlemek istedğiniz içeriğe tıklayın.</span>
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
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content" style="padding-bottom: 0;">
            <span data-name="group-count">1</span> / <span data-name="group-limit">8</span>
        </div>
        <ul class="collection groups load" 
             id="indices"
             data-href="{{ route('admin.twitter.indices.json') }}"
             data-callback="__groups"
             data-nothing>
            <li class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Group Yok</p>
                </div>
            </li>
            <li class="collection-item model d-none">
                <div class="d-flex justify-content-between">
                    <a class="material-icons" href="#">create</a>
                    <span class="group-name">test</span>
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
        @slot('id', 'home-loader')
    @endcomponent
@endsection
