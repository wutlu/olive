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
            'text' => '🐞 Arkaplan İşleri'
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
                model = collection.children('.collection-item.hide');

            collection.children('.collection-item:not(.hide)').remove()

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var item = model.clone();

                        item.removeClass('hide').attr('data-pid', o.pid)

                        item.find('[data-name=time]').html(o.time)
                        item.find('[data-name=pid]').html(o.pid)
                        item.find('[data-name=command]').html(o.command)

                        item.prependTo(collection)
                })
            }

            collection.removeClass('hide')

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax(collection)
            }, 10000)
        }

        $('#home-loader').hide()
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
    #console.collection > .collection-item {
        color: rgba(255, 255, 255, .6);
    }
    #console.collection > .collection-item:hover {
        background-color: rgba(255, 255, 255, .2);
        color: #fff;
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Arkaplan İşleri</span>
        </div>
        <div
            id="console"
            class="collection load hide"
            data-href="{{ route('admin.monitoring.background.processes') }}"
            data-callback="__log"
            data-method="post">
            <a
                href="#"
                class="collection-item waves-effect hide json waves-red"
                data-href="{{ route('admin.monitoring.process.kill') }}"
                data-method="post"
                data-callback="__kill">
                <div class="d-flex justify-content-end">
                    <span data-name="command" class="mr-auto"></span>
                    <span data-name="pid" style="padding: 0 1rem;"></span>
                    <span data-name="time"></span>
                </div>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection
