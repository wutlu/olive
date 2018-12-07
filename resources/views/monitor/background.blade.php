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
            'text' => 'Arkaplan İşleri'
        ]
    ]
])

@push('local.scripts')
    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('#console.collection'),
                model = collection.children('.collection-item.d-none');

            collection.children('.collection-item:not(.d-none)').remove()

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var item = model.clone();

                        item.removeClass('d-none').attr('data-pid', o.pid)

                        item.find('[data-name=time]').html(o.time)
                        item.find('[data-name=pid]').html(o.pid)
                        item.find('[data-name=command]').html(o.command)

                        item.prependTo(collection)
                })
            }

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax(collection)
            }, 2000)
        }
    }

    function __kill(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'İşlem Sonlandı!', classes: 'green darken-2' })
        }
    }
@endpush

@push('local.styles')
    #console.collection {
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center;
        padding: 1rem 0;
    }
    #console.collection > .collection-item:hover {
        background-color: rgba(255, 255, 255, .2);
        color: #fff;
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Arkaplan İşleri" />
            <span class="card-title">Arkaplan İşleri</span>
        </div>
        <div class="card-content grey-text">
            <p>Sunucu üzerinde çalışan sisteme özgü işlemleri dinamik olarak izleyebilirsiniz.</p>
            <p>Sonlandırmak için sonlandırmak istediğiniz işlemin üzerine tıklayın.</p>
        </div>
        <div
            id="console"
            class="collection black load d-flex align-items-end flex-wrap"
            data-href="{{ route('admin.monitoring.background.processes') }}"
            data-callback="__log"
            data-method="post">
            <a
                href="#"
                class="collection-item waves-effect d-none json waves-red"
                data-href="{{ route('admin.monitoring.process.kill') }}"
                data-method="post"
                data-callback="__kill"
                style="width: 100%;">
                <span data-name="command"></span>
                <span data-name="time"></span>
                <span data-name="pid"></span>
            </a>
        </div>
    </div>
@endsection
