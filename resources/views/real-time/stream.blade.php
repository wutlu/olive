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
        max-height: 2160px;
    }

    .list-alert {
        border-radius: .4rem !important;
        margin: 1rem !important;
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
                <a class="btn-flat waves-effect" data-name="pins-button" href="#">Pinler (<span class="count">0</span>)</a>
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
        <div class="collection">
            <a href="#" class="collection-item waves-effect d-none model grey-text">
                <p data-name="platform" class="grey-text text-darken-4"></p>
                <p data-name="text"></p>
            </a>
            <div class="collection-item yellow lighten-4 list-alert">Bir kelime grubu oluşturun ve aktif edin.</div>
        </div>
    </div>
@endsection

@include('real-time._inc.dock')

@push('local.scripts')
    var buffer = [];

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

            if (!$('#' + obj.module + '-' + obj.uuid).length)
            {
                var item = model.clone();
                    item.find('[data-name=text]').html(obj.text)
                    item.find('[data-name=platform]').html(obj.module)

                    item.attr('id', obj.module + '-' + obj.uuid)
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
        time = 500;
    }).on('mouseleave', '.time-line', function() {
        time = 100;
    }).on('click', '.time-line > .collection > a.collection-item', function() {
        M.toast({ html: 'İçerik Pinlendi!', classes: 'orange darken-2' })

        var pins_button = $('[data-name=pins-button]');
        var pin_count = pins_button.children('span.count');
            pin_count.html(parseInt(pin_count.html()) + 1)
    })

    var realTimeTimer;

    function __realtime(__, obj)
    {
        if (obj.status == 'ok')
        {
            $.each(obj.data, function(key, o) {
                if (!$('#' + o.module + '-' + o.uuid).length)
                {
                    buffer.push(o)
                }
            })

            window.clearTimeout(realTimeTimer)

            realTimeTimer = window.setTimeout(function() {
                vzAjax($('.time-line'))
            }, 10000)
        }
    }


    $(document).on('click', '.switch', function() {
        setTimeout(function() {
            vzAjax($('.time-line'))
        }, 1000)
    })
@endpush
