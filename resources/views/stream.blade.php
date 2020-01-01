@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı Akış'
        ]
    ],
    'dock' => true,
    'pin_group' => true,
    'footer_hide' => true,
    'report_menu' => true
])

@push('local.styles')
    #keywordGroups > .collection-item {
        padding-right: 24px;
        padding-left: 24px;
    }

    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    @-webkit-keyframes blink { 
       50% { border-color: #ffcdd2; } 
    }

    @keyframes blink { 
       50% { border-color: #ffcdd2; } 
    }

    .time-line > .collection {
        padding: 0;

        border-width: 2px;
        border-style: dashed;
        border-color: transparent;

        max-height: 10000px;
        overflow: hidden;
    }

    .time-line > .collection.active {
        border-color: #f44336;

        -webkit-animation: blink .6s step-end infinite alternate;
                animation: blink .6s step-end infinite alternate;
    }
@endpush

@push('local.scripts')
    @if (!auth()->user()->intro('driver.stream'))
    // driver
    @endif
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush

@section('wildcard')
    <div class="grey lighten-4 z-depth-1">
        <div class="container">
            <div class="wild-area">
                <div class="wild-content d-flex grey lighten-4" data-wild="settings">
                    <span class="wild-body d-flex">
                        <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" style="margin: 0 .4rem 0 0;" data-class=".wild-content" data-class-remove="active">
                            <i class="material-icons">close</i>
                        </a>
                        <label class="align-self-center mr-1">
                            <input name="sound_alert" value="on" type="checkbox" />
                            <span>Uyarı Sesleri</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="mouse" value="on" type="checkbox" />
                            <span>Fare ile Durdur</span>
                        </label>
                    </span>
                </div>
                <div class="wild-content d-flex grey lighten-4" data-wild="speed">
                    <span class="wild-body d-flex">
                        <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" style="margin: 0 .4rem 0 0;" data-class=".wild-content" data-class-remove="active">
                            <i class="material-icons">close</i>
                        </a>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="2048" checked />
                            <span>1</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="1024" />
                            <span>2</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="512" />
                            <span>3</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="256" />
                            <span>4</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="128" />
                            <span>5</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="64" />
                            <span>6</span>
                        </label>
                    </span>
                </div>
                <ul class="wild-menu">
                    <li>
                        <a class="d-flex" href="#" data-class="[data-wild=settings]" data-class-add="active">
                            <i class="material-icons mr-1">settings</i>
                            <span class="align-self-center">Ayarlar</span>
                        </a>
                    </li>
                    <li>
                        <a class="d-flex" href="#" data-class="[data-wild=speed]" data-class-add="active">
                            <i class="material-icons mr-1">fast_forward</i>
                            <span class="align-self-center">Hız</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="status-bar d-flex">
        <div class="p-1 align-self-center">
            <button class="btn-floating red darken-2 btn-large disabled" type="button" data-name="trigger" data-tooltip="Kısayol (ctrl)" data-position="right">
                <i class="material-icons">play_arrow</i>
            </button>
        </div>
        <div class="p-1 align-self-center">
            <small class="grey-text d-block">Ön Bellek</small>
            <span data-name="buffer">0</span>
        </div>
        <div class="p-1 align-self-center">
            <small class="grey-text d-block">Alınan</small>
            <span data-name="received">0</span>
        </div>
        <div class="p-1 align-self-center">
            <small class="grey-text d-block">Ortalama</small>
            <span data-name="1-minute">0</span>
        </div>
    </div>
    <div
        class="card card-unstyled time-line"
        data-href="{{ route('realtime.query') }}"
        data-callback="__realtime"
        data-method="post"
        data-include="keyword_group">
        <div class="card-content">
            <span class="card-title">Gerçek Zamanlı Akış</span>
        </div>
        <ul class="collection">
            <li class="collection-item model hide"></li>
            <li class="collection-item">
                <p class="d-flex mb-0 blue-grey-text">
                    <i class="material-icons realtime">navigate_next</i>
                    <span class="align-self-center">
                        <a class="blue-grey-text text-darken-4" href="{{ route('search.dashboard') }}">Arama Motoru</a> modülünden bir arama yapın ve kaydedin.<br />Kaydettiğiniz aramaları bulunduğunuz bölümden gerçek zamanlı takip edebilirsiniz.
                    </span>
                </p>
            </li>
        </ul>
    </div>
@endsection

@push('local.scripts')
    $('.owl-wildcard').owlCarousel({
        responsiveClass: true,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1440: {
                items: 3
            }
        },
        autoWidth: true,
        dotClass: 'hide'
    })

    var buffer = [];
    var words = [];

    var speed = $('input[name=speed]:checked').val();
    var time = speed;
    var liveTimer;

    $(window).on('load', function() {
        livePush()
    })

    var bucket = $('.time-line > .collection');
    var model = bucket.children('.model');

    var _ui_received = $('[data-name=received]');
    var _ui_buffer = $('[data-name=buffer]');
    var _ui_1_minute = $('[data-name=1-minute]');

    var start_time = 0;
    var start_timer;

    function start_timer_f()
    {
        start_time++;

        _ui_1_minute.html(parseInt((_ui_received.html() / start_time) * 60) + ' veri / dakika')

        window.clearTimeout(start_timer)
        start_timer = window.setTimeout(start_timer_f, 1000)
    }

    function stop_timer_f()
    {
        window.clearTimeout(start_timer)
        start_time = 0;
    }

    function livePush()
    {
        if (buffer.length)
        {
            var obj = buffer[0];

            if (!$('#' + obj.uuid).length)
            {
                _ui_received.html(parseInt(_ui_received.html()) + 1)
                _ui_buffer.html(parseInt(_ui_buffer.html()) - 1).addClass(_ui_buffer.html() <= 25 ? 'green-text' : 'red-text').removeClass(_ui_buffer.html() <= 25 ? 'red-text' : 'green-text')

                var pattern,
                    url = obj.url;

                switch(obj._type)
                {
                    case 'tweet'   : pattern = _tweet_   (obj); break;
                    case 'entry'   : pattern = _entry_   (obj); break;
                    case 'article' : pattern = _article_ (obj); break;
                    case 'document': pattern = _document_(obj); break;
                    case 'product' : pattern = _product_ (obj); break;
                    case 'comment' : pattern = _comment_ (obj); break;
                    case 'video'   : pattern = _video_   (obj); break;
                    case 'media'   : pattern = _media_   (obj); break;
                }

                var item = model.clone().html(pattern);

                item.mark(words, {
                    'element': 'span',
                    'className': 'marked yellow black-text',
                    'accuracy': 'complementary'
                })

                item.attr('id', obj.uuid)
                    .hide()
                    .removeClass('model hide')
                    .show( 'highlight', {
                        'color': '#ffe0b2'
                    }, 1000 );

                item.prependTo(bucket)

                if ($('input[name=sound_alert]').prop('checked') == true)
                {
                    $.playSound('{{ asset('alert-message.mp3?v2') }}')
                }

                item.find('[data-button=view]')
                    .attr('href', '{{ url('/') }}/db/' + obj._index + '/' + obj._type + '/' + obj._id)
            }

            buffer.shift()

            if (bucket.children('.collection-item').length > 600)
            {
                bucket.children('.collection-item:last-child').remove()
            }
        }

        window.clearTimeout(liveTimer);

        liveTimer = window.setTimeout(function() {
            livePush()
        }, time)
    }

    $(document).on('click', '[data-name=trigger]', function() {
        _ui_toggle()
    }).keydown(function(e) {
        if (e.which == 17)
        {
            _ui_toggle()

            return e.preventDefault()
        }
    }).on('mouseleave', '.time-line', function() {
        if ($('input[name=mouse]').is(':checked'))
        {
            speed_change()

            _ui_change('play', true)
        }
    }).on('mouseenter', '.time-line > .collection', function() {
        if ($('input[name=mouse]').is(':checked'))
        {
            time = 60000;

            _ui_change('stop', true)
        }
    })

    function _ui_toggle()
    {
        var _collection_status = $('.time-line > .collection');

        if (_collection_status.hasClass('active'))
        {
            speed_change()

            _ui_change('play', true)
        }
        else
        {
            time = 60000;

            _ui_change('stop', true)
        }
    }

    $('input[name=speed]').change(speed_change)

    function speed_change()
    {
        time = $('input[name=speed]:checked').val();

        window.clearTimeout(liveTimer);

        liveTimer = window.setTimeout(function() {
            livePush()
        }, time)

        _ui_change('play', true)
    }

    function _ui_change(status, pause)
    {
        var _collection_status = $('.time-line > .collection');

        var trigger = $('[data-name=trigger]');
        var trigger_icon = trigger.children('i.material-icons');

        if (status == 'play')
        {
            trigger_icon.html('pause')

            if (pause)
            {
                trigger.removeClass('blue-grey').addClass('red')
                _collection_status.removeClass('active')
            }
            else
            {
                trigger.removeClass('disabled').addClass('red pulse')

                if (start_time == 0)
                {
                    start_timer_f()
                }
            }
        }
        else if (status == 'stop')
        {
            trigger_icon.html('play_arrow')

            if (pause)
            {
                trigger.removeClass('red').addClass('blue-grey')
                _collection_status.addClass('active')
            }
            else
            {
                trigger.addClass('disabled').removeClass('red pulse')

                _ui_buffer.html(0).removeClass('green-text red-text')
                _ui_received.html(0)
                _ui_1_minute.html(0)

                stop_timer_f()
            }
        }
    }

    var streamTimer;

    function __realtime(__, obj)
    {
        if (obj.status == 'ok')
        {
            words = obj.words;

            $.each(obj.data, function(key, o) {
                if ($('#' + o.uuid).length)
                {
                    //
                }
                else
                {
                    var item = buffer.filter(function (x) {
                         return x.uuid === o.uuid
                    })[0];
    
                    if (!item)
                    {
                        _ui_buffer.html(parseInt(_ui_buffer.html()) + 1)

                        buffer.push(o)
                    }
                }
            })

            window.clearTimeout(streamTimer)

            streamTimer = window.setTimeout(function() {
                vzAjax($('.time-line'))
            }, 10000)
        }
    }

    var streamTriggerTimer;

    $(document).on('click', '.switch', function() {
        var stream = false;
        var keyword_group_checkboxes = $('input[name=keyword_group]');
            keyword_group_checkboxes.not($(this).find('input[type=checkbox]')).prop('checked', false);

        $.each(keyword_group_checkboxes, function() {
            var __ = $(this);

            if (__.is(':checked'))
            {
                stream = true;

                _ui_change('play', false)
            }
        })

        window.clearTimeout(streamTriggerTimer)

        if (stream)
        {
            streamTriggerTimer = window.setTimeout(function() {
                vzAjax($('.time-line'))
            }, 1000)
        }
        else
        {
            buffer = [];

            window.clearTimeout(streamTimer)

            _ui_change('stop', false)
        }
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

                        item.find('[data-name=name]').html(o.name)
                        item.find('[name=keyword_group]').val(o.id)

                        item.appendTo(__)
                })
            }
            else
            {
                $('body').addClass('dock-active')
            }
        }
    }
@endpush

@section('dock')
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
                <span data-name="name" class="align-self-center mr-auto"></span>
                <div class="switch align-self-center">
                    <label>
                        <input type="checkbox" name="keyword_group" />
                        <span class="lever"></span>
                    </label>
                </div>
            </li>
            <li class="collection-item nothing hide" data-id="search-alert">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'İlk önce <a class="btn red btn-small waves-effect" href="'.route('search.dashboard').'">Arama Motoru</a> ile bir arama yapıp, aramayı kaydedin.<br /><br />Daha sonra bu alandan kayıtlı aramayı seçmeniz gerekecek.')
                @endcomponent
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'ss-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    @php
        $hints = [
            'CTRL tuşuna basarak akışı durdurabilirsiniz.'
        ];

        shuffle($hints);
    @endphp

    <div class="yellow-text text-darken-2 mt-1">
        @component('components.alert')
            @slot('icon', 'lightbulb_outline')
            @slot('text', $hints[0])
        @endcomponent
    </div>
@endsection

@include('_inc.alerts.search_operators')

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/driver.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/driver.min.js?v='.config('system.version')) }}"></script>
@endpush
