@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı'
        ]
    ],
    'dock' => true,
    'wide' => true,
    'pin_group' => true
])

@push('local.styles')
    #keyword-groups > .collection-item {
        padding-right: 24px;
        padding-left: 24px;
    }

    .list-alert {
        border-radius: .4rem !important;
        margin: 1rem !important;
    }

    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    .time-line > .collection {
        max-height: 4000px;
        overflow: hidden;

        border-width: 0 0 0 1rem;
        border-style: solid;
        border-color: #4caf50;
    }

    .time-line > .collection.active {
        border-color: #f44336;
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
    <div class="wild-area z-depth-1">
        <div class="wild-content d-flex grey lighten-4" data-wild="volume">
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
                <a class="d-flex" href="#" data-class="[data-wild=volume]" data-class-add="active">
                    <i class="material-icons mr-1">volume_up</i>
                    <span class="align-self-center">Sesler</span>
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
@endsection

@section('content')
    <div
        class="card time-line"
        data-href="{{ route('realtime.query') }}"
        data-callback="__realtime"
        data-method="post"
        data-include="keyword_group,sentiment">
        <div class="card-content">
            <span class="card-title">Gerçek Zamanlı</span>
        </div>
        <ul class="collection">
            <li class="collection-item model hide">test</li>
            <li class="collection-item">
                <p class="d-flex">
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

    function livePush()
    {
        if (buffer.length)
        {
            var obj = buffer[0];

            if (!$('#' + obj.uuid).length)
            {
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

    $(document).on('mouseenter', '.time-line > .collection', function() {
        time = 60000;

        $('.time-line > .collection').addClass('active')
    }).on('mouseleave', '.time-line', speed_change)

    $('input[name=speed]').change(speed_change)

    function speed_change()
    {
        time = $('input[name=speed]:checked').val();

        window.clearTimeout(liveTimer);

        liveTimer = window.setTimeout(function() {
            livePush()
        }, time)

        $('.time-line > .collection').removeClass('active')
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
        }
    })

    function __keyword_groups(__, obj)
    {
        var ul = $('#keyword-groups');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = ul.children('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-callback=__get_keyword_group]').attr('data-id', o.id)
                        item.find('[name=keyword_group]').val(o.id)

                    if (!selector.length)
                    {
                        item.appendTo(ul)
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
                        'class': 'collection',
                        'html': [
                        @foreach (config('system.modules') as $key => $module)
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

@section('dock')
    <div class="card with-bg mb-1">
        <div class="card-content">
            <span class="card-title">Kelime Grupları</span>
        </div>
        <div class="card-image">
            <a href="#" class="btn-floating halfway-fab waves-effect white" data-trigger="create-keyword-group">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content">
            <span data-name="keyword-group-count">0</span> / <span data-name="keyword-group-limit">0</span>
        </div>
        <ul class="collection load" 
             id="keyword-groups"
             data-href="{{ route('realtime.keyword.groups') }}"
             data-callback="__keyword_groups"
             data-method="post"
             data-loader="#keyword-group-loader"
             data-nothing>
            <li class="collection-item nothing hide grey-text">Henüz kelime grubu oluşturmadınız.</li>
            <li class="collection-item model hide justify-content-between">
                <span data-name="name" class="align-self-center mr-auto"></span>
                <div class="switch align-self-center">
                    <label>
                        <input type="checkbox" name="keyword_group" data-multiple="true" />
                        <span class="lever"></span>
                    </label>
                </div>
                <a
                    class="btn-floating btn-small waves-effect json align-self-center white"
                    data-href="{{ route('realtime.keyword.group') }}"
                    data-method="post"
                    data-callback="__get_keyword_group"
                    href="#">
                    <i class="material-icons grey-text text-darken-2">create</i>
                </a>
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
