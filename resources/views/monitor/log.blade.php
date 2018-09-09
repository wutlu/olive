@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sistem İzleme'
        ],
        [
            'text' => 'Log Ekranı'
        ]
    ]
])

@push('local.scripts')
    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('ul.collection');
            var model = collection.children('li.collection-item.d-none');

            if (obj.data.length)
            {
                var scroll = false;

                $.each(obj.data, function(key, o) {
                    var m = $('[data-id=' + o.uuid + ']');

                    var item = m.length ? m : model.clone();
                        item.removeClass('d-none')
                            .attr('data-id', o.uuid)

                        item.find('[data-name=level]').html(o.level + '. seviye').addClass(o.level <= 4 ? 'green-text' : o.level <= 7 ? 'orange-text' : 'red-text')
                        item.find('[data-name=repeat]').html(o.hit + ' tekrar').addClass(o.hit <= 10 ? 'green-text' : o.hit <= 20 ? 'orange-text' : 'red-text')
                        item.find('[data-name=updated-at]').attr('data-time', o.updated_at)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)
                        item.find('[data-name=module]').html(o.module)
                        item.find('[data-name=message]').html(o.message)

                    if (m.length)
                    {
                        if (m.attr('data-repeat') != o.hit)
                        {
                            scroll = true;

                            item.attr('data-repeat', o.hit)
                            item.appendTo(collection)
                        }
                    }
                    else
                    {
                        item.find('[data-name=updated-at]').html(o.updated_at)
                        item.find('[data-name=created-at]').html(o.created_at)
                        item.attr('data-repeat', o.hit)

                        if (__.hasClass('loaded'))
                        {
                            scroll = true;

                            item.appendTo(collection)
                        }
                        else
                        {
                            scroll = true;

                            item.prependTo(collection)
                        }
                    }
                })

                $('[data-callback=__log]').animate({
                    scrollTop: $('[data-callback=__log]').prop('scrollHeight')
                }, 200);
            }

            __.addClass('loaded')

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax($('ul.collection'))
            }, 10000)
        }
    }

    $(window).on('load', function() {
        $('[data-callback=__log]').animate({
            scrollTop: $('[data-callback=__log]').prop('scrollHeight')
        }, 200);
    })
@endpush

@push('local.styles')
    .collection {
        height: 600px;
        overflow-y: scroll;
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center;
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/22.jpg') }}" alt="Log Ekranı" />
            <span class="card-title">Log Ekranı</span>
        </div>
        <div class="card-content yellow lighten-4">
            Son 24 saatte alınan hata loglarını dinamik olarak inceleyebilirsiniz.
        </div>
        <ul
            class="collection black load d-flex align-items-end flex-wrap no-select"
            data-href="{{ route('admin.monitoring.log') }}"
            data-callback="__log"
            data-method="post">
            <li class="collection-item d-none" style="width: 100%;">
                <p>
                    <span data-name="level"></span>
                    <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                    <span data-name="repeat"></span>
                    <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                    <time data-name="updated-at" class="timeago grey-text text-darken-2"></time>
                </p>
                <p>
                    <time data-name="created-at" class="timeago grey-text text-darken-2"></time>
                    <span data-name="module" class="grey-text text-darken-2"></span>
                </p>
                <code data-name="message" class="green-text d-block"></code>
            </li>
        </ul>
    </div>
@endsection
