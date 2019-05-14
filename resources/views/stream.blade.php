@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı'
        ]
    ],
    'dock' => true,
    'pin_group' => true,
    'footer_hide' => true
])

@push('local.styles')
    #keyword-groups > .collection-item {
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
        max-height: 8000px;
        overflow: hidden;

        padding: 0;

        border-width: 2px;
        border-style: dashed;
        border-color: transparent;
    }

    .time-line > .collection.active {
        border-color: #f44336;

        -webkit-animation: blink .6s step-end infinite alternate;
                animation: blink .6s step-end infinite alternate;
    }
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
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
                        <label class="align-self-center">
                            <input name="sound_alert" value="on" type="checkbox" />
                            <span>Uyarı Sesleri</span>
                        </label>
                    </span>
                </div>
                <div class="wild-content d-flex grey lighten-4" data-wild="speed">
                    <span class="wild-body d-flex">
                        <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" style="margin: 0 .4rem 0 0;" data-class=".wild-content" data-class-remove="active">
                            <i class="material-icons">close</i>
                        </a>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="1000" />
                            <span>1</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="800" />
                            <span>2</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="600" checked />
                            <span>3</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="400" />
                            <span>4</span>
                        </label>
                        <label class="align-self-center mr-1">
                            <input name="speed" type="radio" value="100" />
                            <span>5</span>
                        </label>
                    </span>
                </div>
                <div class="wild-content d-flex grey lighten-4" data-wild="sentiment">
                    <span class="wild-body d-flex">
                        <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" style="margin: 0 .4rem 0 0;" data-class=".wild-content" data-class-remove="active">
                            <i class="material-icons">close</i>
                        </a>
                    </span>

                    <label class="align-self-center mr-1" data-tooltip="Pozitif">
                        <input data-update type="radio" name="sentiment" value="pos" />
                        <span class="material-icons green-text">sentiment_very_satisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Nötr">
                        <input data-update type="radio" name="sentiment" value="neu" />
                        <span class="material-icons grey-text text-darken-2">sentiment_neutral</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Negatif">
                        <input data-update type="radio" name="sentiment" value="neg" />
                        <span class="material-icons red-text">sentiment_very_dissatisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Tümü">
                        <input data-update type="radio" name="sentiment" value="all" checked="" />
                        <span class="material-icons grey-text text-darken-2">fullscreen</span>
                    </label>
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
                    <li>
                        <a class="d-flex" href="#" data-class="[data-wild=sentiment]" data-class-add="active">
                            <i class="material-icons mr-1">face</i>
                            <span class="align-self-center">Duygu</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="status-bar d-flex mt-1">
        <div class="p-1 align-self-center">
            <button class="btn-floating cyan darken-2 btn-large disabled" type="button" data-name="trigger" data-tooltip="Kısayol (Space)" data-position="right">
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
    </div>
    <div
        class="card card-unstyled time-line"
        data-href="{{ route('realtime.query') }}"
        data-callback="__realtime"
        data-method="post"
        data-include="keyword_group,sentiment">
        <div class="card-content">
            <span class="card-title">Gerçek Zamanlı</span>
        </div>
        <ul class="collection">
            <li class="collection-item model hide"></li>
            <li class="collection-item">
                <p class="d-flex mb-0">
                    <i class="material-icons realtime">navigate_next</i>
                    <span class="align-self-center">Herhangi bir kelime grubu oluşturun, bir kelime grubunuz varsa; yanında bulunan anahtarı aktif edin.</span>
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

    var group_select = $('select[name=group_id]');
        group_select.formSelect()

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
                    case 'tweet':
                        pattern = _tweet_(obj);
                    break;
                    case 'entry':
                        pattern = _entry_(obj);
                    break;
                    case 'article':
                        pattern = _article_(obj);
                    break;
                    case 'product':
                        pattern = _product_(obj);
                    break;
                    case 'comment':
                        pattern = _comment_(obj);
                    break;
                    case 'video':
                        pattern = _video_(obj);
                    break;
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

                if ($('input[name=sound_alert]').prop("checked") == true)
                {
                    $.playSound('{{ asset('alert-message.mp3') }}')
                }

                item.find('[data-button=view]')
                    .attr('href', '{{ url('/') }}/db/' + obj._index + '/' + obj._type + '/' + obj._id)
            }

            buffer.shift()

            if (bucket.children('.collection-item').length > 400)
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
        if (e.which == 32)
        {
            _ui_toggle()

            return e.preventDefault()
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
                _collection_status.removeClass('active')
            }
            else
            {
                trigger.removeClass('disabled').addClass('pulse')
            }
        }
        else if (status == 'stop')
        {
            trigger_icon.html('play_arrow')

            if (pause)
            {
                _collection_status.addClass('active')
            }
            else
            {
                trigger.addClass('disabled').removeClass('pulse')

                _ui_buffer.html(0)
                _ui_received.html(0)
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

        $.each(keyword_group_checkboxes, function() {
            var __ = $(this);

            if (__.is(':checked'))
            {
                stream = true;

                _ui_change('play', false)

                __.blur()
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

    function __keyword_groups(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                __.removeClass('hide')

                $.each(obj.hits, function(key, o) {
                    var selector = __.children('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-callback=__get_keyword_group]').attr('data-id', o.id)
                        item.find('[name=keyword_group]').val(o.id)

                    if (!selector.length)
                    {
                        item.appendTo(__)
                    }
                })
            }

            $('[data-name=keyword-group-count]').html(obj.hits.length)
            $('[data-name=keyword-group-limit]').html(obj.limit)
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
                'date-method': 'post',
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
                                'data-length': 255
                            }),
                            $('<label />', {
                                'for': 'keywords',
                                'html': 'Kelime Listesi'
                            }),
                            $('<span />', {
                                'html': $('<a />', {
                                    'href': '#',
                                    'class': 'd-flex',
                                    'data-trigger': 'info',
                                    'css': { 'margin': '0 .4rem 0 0' },
                                    'html': [
                                        $('<i />', {
                                            'class': 'material-icons mr-1 grey-text align-self-center',
                                            'html': 'info_outline'
                                        }),
                                        $('<span />', {
                                            'class': 'grey-text align-self-center',
                                            'html': 'Arama İfadeleri'
                                        })
                                    ]
                                })
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'collection collection-unstyled',
                        'html': [
                        @foreach (config('system.modules') as $key => $module)
                            @if ($organisation->{'data_'.$key})
                                $('<label />', {
                                    'class': 'collection-item waves-effect d-block',
                                    'html': [
                                        $('<input />', {
                                            'name': 'modules',
                                            'value': '{{ $key }}',
                                            'type': 'checkbox',
                                            'data-multiple': 'true',
                                        }),
                                        $('<span />', {
                                            'html': '{{ title_case($module) }} Verilerini Dahil Et'
                                        })
                                    ]
                                }),
                            @endif
                        @endforeach
                        ]
                    })
                ]
            }),
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'data-trigger': 'delete-keyword-group',
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text hide',
                    'html': buttons.remove
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#keyword-group-form',
                    'html': buttons.ok
                })
            ],
            'size': 'modal-medium',
            'options': {
                dismissible: false
            }
        })

        M.updateTextFields()

        mdl.find('[data-length]').characterCounter()

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-keyword-group]', function() {
        var mdl = keyword_group_modal();
            mdl.find('.modal-title').html('Grup Oluştur')
            mdl.find('form#keyword-group-form').data('method', 'put')

            mdl.find('[name=modules]').prop('checked', false)

            mdl.find('[name=name]').val('')
            mdl.find('[name=keywords]').val('')

        $('[data-trigger=delete-keyword-group]').removeAttr('data-id').addClass('hide')
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

                if (obj.data.modules)
                {
                    $.each(obj.data.modules, function(number, key) {
                        mdl.find('[name=modules][value=' + key + ']').prop('checked', true)
                    })
                }

            $('[data-trigger=delete-keyword-group]').data('id', obj.data.id).removeClass('hide')
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
        return modal({
            'id': 'keyword-group-alert',
            'body': 'Kelime grubu silinecek?',
            'size': 'modal-small',
            'title': 'Sil',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('realtime.keyword.group') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete_keyword_group'
                })
            ]
        })
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
                classes: 'red darken-2'
            })

            vzAjax($('#keyword-groups'))
        }
    }
@endpush

@section('action-bar')
    <a href="#" class="btn-floating halfway-fab waves-effect white" data-trigger="create-keyword-group">
        <i class="material-icons grey-text">add</i>
    </a>
@endsection

@section('dock')
    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">speaker_notes</i>
                Kelime Grupları
            </span>
            <span data-name="keyword-group-count">0</span> / <span data-name="keyword-group-limit">0</span>
        </div>
        <ul class="collection load hide" 
             id="keyword-groups"
             data-href="{{ route('realtime.keyword.groups') }}"
             data-callback="__keyword_groups"
             data-method="post"
             data-loader="#keyword-group-loader"
             data-nothing>
            <li class="collection-item nothing hide grey-text">Henüz kelime grubu oluşturmadınız.</li>
            <li class="collection-item model hide justify-content-between">
                <a
                    class="json align-self-center mr-1"
                    data-href="{{ route('realtime.keyword.group') }}"
                    data-method="post"
                    data-callback="__get_keyword_group"
                    href="#">
                    <i class="material-icons grey-text text-darken-2">create</i>
                </a>
                <span data-name="name" class="align-self-center mr-auto"></span>
                <div class="switch align-self-center">
                    <label>
                        <input type="checkbox" name="keyword_group" data-multiple="true" />
                        <span class="lever"></span>
                    </label>
                </div>
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'keyword-group-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
@endsection

@include('_inc.alerts.search_operators')
