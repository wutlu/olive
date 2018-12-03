@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı',
            'link' => route('realtime')
        ],
        [
            'text' => $pin_group->name
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

    [data-name=buffer-count] {
        background-image: url({{ asset('img/next.gif') }});
        background-repeat: no-repeat;
        background-position: left center;
        display: table;
        width: 96px;
        height: 32px;
        line-height: 32px;
        text-align: right;
    }

    .marked {
        padding: .6rem;
        border-radius: .2rem;
    }
@endpush

@section('content')
    <div
        class="card time-line"
        data-href="{{ route('realtime.query') }}"
        data-callback="__realtime"
        data-method="post"
        data-include="keyword_group">
        <div class="card-content">
            <div class="d-flex justify-content-between">
                <span class="card-title mb-0 align-self-center">{{ $pin_group->name }}</span>
                <a class="btn-flat waves-effect" data-name="pins-button" href="{{ route('realtime.pins', $pin_group->id) }}">Pinler (<span class="count">{{ count($pin_group->pins) }}</span>)</a>
            </div>
        </div>
        <div class="card-content list-alert cyan lighten-5">
            <p class="d-flex">
                <img alt="Pin" src="{{ asset('img/pin.svg') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                <span class="align-self-center">Pinlemek istediğiniz içeriğe tıklayın.</span>
            </p>
            <p class="d-flex">
                <img alt="Pin" src="{{ asset('img/cold-plant.svg') }}" style="width: 32px; height: 32px; margin: 0 .2rem 0 0;" />
                <span class="align-self-center">Akışı yavaşlatmak için fareyi akışın üzerine getirin.</span>
            </p>
        </div>
        <div data-name="buffer-count" data-tooltip="Kriter Havuzunuz" data-position="right">0</div>
        <div class="collection">
            <a
                href="#"
                class="collection-item waves-effect d-none model grey-text json"
                data-href="{{ route('realtime.pin', 'add') }}"
                data-method="post"
                data-callback="__pin">
                <p data-name="module" class="orange-text text-darken-4"></p>
                <time data-name="created-at"></time>
                <p data-name="url" class="grey-text text-darken-2"></p>
                <p data-name="author" class="cyan-text"></p>
                <p data-name="title" class="black-text strong"></p>
                <p data-name="text"></p>
            </a>
            <div class="collection-item yellow lighten-4 list-alert">Bir kelime grubu oluşturun ve aktif edin.</div>
        </div>
    </div>
@endsection

@include('real-time._inc.dock')

@push('external.include.footer')
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('app.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('local.scripts')
    function __pin(__, obj)
    {
        var pins_button = $('[data-name=pins-button]');
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
                    $('<span />', { 'html': 'İçerik Pinlendi', 'classes': 'grey lighten-4' }),
                    $('<a />', {
                        'href': '#',
                        'class': 'btn-flat toast-action json',
                        'html': 'Geri Al',
                        'data-undo': 'true',
                        'data-href': '{{ route('realtime.pin', 'remove') }}',
                        'data-method': 'post',
                        'data-callback': '__pin',
                        'data-id': __.data('id'),
                        'data-type': __.data('type'),
                        'data-index': __.data('index'),
                        'data-group_id': __.data('group_id')
                    })
                ]
            });

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
                    item.find('[data-name=module]').html(obj.module)
                    item.find('[data-name=created-at]').html(obj.created_at)

                    if (obj.module == 'twitter')
                    {
                        item.find('[data-name=author]').html(obj.user.name + ' @' + obj.user.screen_name)
                    }
                    else if (obj.module == 'haber')
                    {
                        item.find('[data-name=url]').html(obj.url)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'sozluk')
                    {
                        item.find('[data-name=author]').html(obj.author)
                        item.find('[data-name=url]').html(obj.url)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'alisveris')
                    {
                        item.find('[data-name=url]').html(obj.url)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'youtube-video')
                    {
                        item.find('[data-name=author]').html(obj.channel.title)
                        item.find('[data-name=title]').html(obj.title)
                    }
                    else if (obj.module == 'youtube-yorum')
                    {
                        item.find('[data-name=author]').html(obj.channel.title)
                        item.find('[data-name=title]').html(obj.title)
                    }

                    item.find('[data-name=title], [data-name=text]').mark(words, {
                        'element': 'span',
                        'className': 'marked yellow black-text',
                        'accuracy': 'complementary'
                    })

                    item.attr('id', obj.uuid)
                        .attr('data-id', obj._id)
                        .attr('data-index', obj._index)
                        .attr('data-type', obj._type)
                        .attr('data-group_id', {{ $pin_group->id }})
                        .hide()
                        .removeClass('model d-none')
                        .show( 'highlight', {}, 200 );

                    item.prependTo(bucket)
            }

            buffer.shift()

            if (bucket.children('.collection-item').length > 200)
            {
                bucket.children('.collection-item:last-child').remove()
            }

            $('[data-name=buffer-count]').html(buffer.length)
        }

        window.clearTimeout(liveTimer);

        liveTimer = window.setTimeout(function() {
            livePush();
        }, time)
    }

    $(document).on('mouseenter', '.time-line > .collection', function() {
        time = 600;
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
            $('[data-name=buffer-count]').html(0)
        }
    })
@endpush
