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
    .time-line > .collection {
        min-height: 800px;
        max-height: 6320px;

        overflow: hidden;
    }

    .list-alert {
        border-radius: .4rem !important;
        margin: 1rem !important;
    }

    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    .pin-history-link {
        background-image: url({{ asset('img/icons/pin.png') }});
        background-repeat: no-repeat;
        background-position: left center;
        background-size: 32px 32px;
        padding: 1rem 1rem 1rem 32px;
        display: table;
    }
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('app.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('local.scripts')
    $('.owl-carousel').owlCarousel({
        responsiveClass:true,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:2
            },
            1440:{
                items:3
            }
        },
        autoWidth:true,
        dotClass: 'd-none'
    })
@endpush

@section('wildcard')
    <div id="wildcard-pin-history" class="owl-carousel z-depth-2 d-none"></div>
@endsection

@section('content')
    <div
        class="card time-line"
        data-href="{{ route('realtime.query') }}"
        data-callback="__realtime"
        data-method="post"
        data-include="keyword_group,sentiment">
        <div class="card-content">
            <div class="d-flex justify-content-between">
                <span class="card-title mr-auto mb-0 align-self-center">Gerçek Zamanlı</span>

                <a
                    class="btn-flat waves-effect json"
                    disabled
                    data-button="pins-button"
                    data-href="{{ route('route.generate.id') }}"
                    data-method="post"
                    data-name="pin.pins"
                    data-callback="__go"
                    href="#">Pinler (<span class="count">0</span>)</a>
            </div>
        </div>
        <div class="card-content">
            <p class="d-flex">
                <img alt="Pin" src="{{ asset('img/icons/pin.png') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                <span class="align-self-center">Sağdaki menüden bir pin grubu seçin. Pin grubunuz yoksa oluşturabilirsiniz. Pinlemek için ilgilendiğiniz içeriğe tıklamanız yeterli.</span>
            </p>
            <p class="d-flex">
                <img alt="Pin" src="{{ asset('img/icons/snowflake.png') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                <span class="align-self-center">Akışı yavaşlatmak için fareyi akışın üzerine getirin.</span>
            </p>
        </div>
        <div class="collection">
            <a
                href="#"
                class="collection-item waves-effect d-none model grey-text json"
                data-href="{{ route('pin', 'add') }}"
                data-method="post"
                data-include="group_id"
                data-callback="__pin">
                <time data-name="created-at"></time>
                <p data-name="url" class="grey-text text-darken-2"></p>
                <p data-name="author" class="red-text"></p>
                <p data-name="title" class="black-text strong"></p>
                <p data-name="text"></p>
            </a>
            <div class="collection-item yellow lighten-5">Bir kelime grubu oluşturun ve aktif edin.</div>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('app.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('local.scripts')
    var group_select = $('select[name=group_id]');
        group_select.formSelect()

    function __pin(__, obj)
    {
        var pins_button = $('[data-button=pins-button]');
        var pin_count = pins_button.children('span.count');

        if (obj.status == 'removed')
        {
            M.toast({ html: 'Pin Kaldırıldı', classes: 'red darken-2' })

            pin_count.html(parseInt(pin_count.html()) - 1)
        }
        else if (obj.status == 'pinned')
        {
            var toastHTML = $('<div />', {
                'html': [
                    $('<span />', {
                        'html': 'İçerik Pinlendi',
                        'class': 'white-text'
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'btn-flat toast-action json',
                        'html': 'Geri Al',
                        'data-undo': 'true',
                        'data-href': '{{ route('pin', 'remove') }}',
                        'data-method': 'post',
                        'data-callback': '__pin',
                        'data-id': __.data('id'),
                        'data-type': __.data('type'),
                        'data-index': __.data('index'),
                        'data-include': 'group_id'
                    })
                ]
            });

            $('#wildcard-pin-history').removeClass('d-none')

            $('.owl-carousel').trigger('add.owl.carousel', [$('<a />', {
                'href': __.data('url'),
                'class': 'pin-history-link',
                'target': '_blank',
                'html': str_limit(__.data('url'), 100)
            }), 0]).trigger('refresh.owl.carousel');

            M.toast({ html: toastHTML.get(0).outerHTML })

            pin_count.html(parseInt(pin_count.html()) + 1)
        }
        else if (obj.status == 'failed')
        {
            M.toast({ html: 'Hay aksi, beklenmedik bir durum.', classes: 'orange darken-2' })
        }
    }

    var buffer = [];
    var words = [];

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

            if (!$('#' + obj.uuid).length)
            {
                var item = model.clone();
                    item.find('[data-name=text]').html(obj.text)
                    item.find('[data-name=created-at]').html(obj.created_at)
                var url = '';

                    if (obj.module == 'twitter')
                    {
                        url = 'https://twitter.com/' + obj.user.screen_name + '/status/' + obj._id;

                        item.find('[data-name=author]').html(obj.user.name + ' @' + obj.user.screen_name)
                        item.find('[data-name=url]').html(url)
                    }
                    else if (obj.module == 'haber')
                    {
                        url = obj.url;

                        item.find('[data-name=url]').html(obj.url)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'sozluk')
                    {
                        url = obj.url;

                        item.find('[data-name=author]').html(obj.author)
                        item.find('[data-name=url]').html(obj.url)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'alisveris')
                    {
                        url = obj.url;

                        item.find('[data-name=url]').html(obj.url)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'youtube-video')
                    {
                        url = 'https://www.youtube.com/watch?v=' + obj._id;

                        item.find('[data-name=author]').html(obj.channel.title)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'youtube-yorum')
                    {
                        url = 'https://www.youtube.com/channel/' + obj.channel.id;

                        item.find('[data-name=author]').html(obj.channel.title)
                        item.find('[data-name=title]').html(obj.title)
                    }

                    item.attr('data-url', url)

                    item.find('[data-name=title], [data-name=text]').mark(words, {
                        'element': 'span',
                        'className': 'marked yellow black-text',
                        'accuracy': 'complementary'
                    })

                    item.attr('id', obj.uuid)
                        .attr('data-id', obj._id)
                        .attr('data-index', obj._index)
                        .attr('data-type', obj._type)
                        .hide()
                        .removeClass('model d-none')
                        .show( 'highlight', {}, 200 );

                    item.prependTo(bucket)
            }

            buffer.shift()

            if (bucket.children('.collection-item').length > 1000)
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
    })

    var streamTimer;

    function __realtime(__, obj)
    {
        if (obj.status == 'ok')
        {
            words = obj.words;

            $.each(obj.data, function(key, o) {
                if (!$('#' + o.uuid).length)
                {
                    var item = buffer.find(item => item.uuid === o.uuid);

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
@endpush

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
             data-method="post"
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
                <div class="switch align-self-center">
                    <label>
                        <input type="checkbox" name="keyword_group" data-multiple="true" />
                        <span class="lever"></span>
                    </label>
                </div>
                <a
                    class="btn-floating btn-small waves-effect json teal align-self-center"
                    data-href="{{ route('realtime.keyword.group') }}"
                    data-method="post"
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

    <div class="card-panel teal white-text">Takip etmek istediğiniz<br/>Kelime Grubunu aktif edin.</div>

    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/25.jpg') }});">
            <span class="card-title white-text mb-0">Pin Grupları</span>
        </div>
        <div class="collection collection-bordered">
            @forelse ($pin_groups as $group)
                <label class="collection-item waves-effect d-block" style="padding: 12px 24px;">
                    <input
                        autocomplete="off"
                        name="group_id"
                        id="group_id-{{ $group->id }}"
                        value="{{ $group->id }}"
                        class="json with-gap"
                        data-href="{{ route('pin.group') }}"
                        data-method="post"
                        data-delay="1"
                        type="radio"
                        data-callback="__pin_group" />
                    <span>{{ str_limit($group->name, 10) }}</span>
                </label>
            @empty
                <div class="collection-item red-text">Henüz pin grubu oluşturmadınız.</div>
            @endforelse
            <a class="collection-item waves-effect d-block" href="{{ route('pin.groups') }}">Tümü</a>
        </div>
    </div>

    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/31.jpg') }});">
            <span class="card-title white-text mb-0">Duygu Analizi <sup>Beta</sup></span>
        </div>
        <div class="collection collection-bordered">
            <label class="collection-item waves-effect d-block" style="padding: 12px 24px;">
                <input name="sentiment" value="pos" class="with-gap" type="radio" />
                <span>Pozitif</span>
            </label>
            <label class="collection-item waves-effect d-block" style="padding: 12px 24px;">
                <input name="sentiment" value="neg" class="with-gap" type="radio" />
                <span>Negatif</span>
            </label><label class="collection-item waves-effect d-block" style="padding: 12px 24px;">
                <input name="sentiment" value="neu" class="with-gap" type="radio" />
                <span>Nötr</span>
            </label>
            <label class="collection-item waves-effect d-block" style="padding: 12px 24px;">
                <input checked name="sentiment" value="all" class="with-gap" type="radio" />
                <span>Tümü</span>
            </label>
        </div>
    </div>
@endsection

@push('local.styles')
    #keyword-groups > .collection-item {
        padding-right: 24px;
        padding-left: 24px;
    }
@endpush

@push('local.scripts')
    function __pin_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-button=pins-button]').removeAttr('disabled').attr('data-id', obj.data.id).children('span.count').html(obj.data.pins.length)
        }
    }

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
                        item.find('[name=keyword_group]').val(o.id)

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
                                'data-length': 64
                            }),
                            $('<label />', {
                                'for': 'keywords',
                                'html': 'Kelime Listesi'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Birden fazla anahtar kelime veya cümle için birden fazla satır kullanabilirsiniz. Ayrıca OR, AND ve (parantez) parametrelerini de kullanabilirsiniz.'
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
                classes: 'red darken-2'
            })

            vzAjax($('#keyword-groups'))
        }
    }
@endpush
