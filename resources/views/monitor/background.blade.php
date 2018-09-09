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
            var terminal = $('ul#console');
            var model = terminal.children('li.collection-item.d-none');

                terminal.children('li.collection-item:not(.d-none)').remove()

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var item = model.clone().removeClass('d-none');
                        item.find('[data-name=time]').html(o[0])
                        item.find('[data-name=pid]').html(o[1])
                        item.find('[data-name=cmd]').html(o[2])

                        item.prependTo(terminal)
                })
            }

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax($('ul#console'))
            }, 2000)
        }
    }
@endpush

@push('local.styles')
    ul#console {
        height: 600px;
        overflow-y: scroll;
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center;
        padding: 1rem 0;
    }
    ul#console > li.collection-item {
        padding-top: .4rem;
        padding-bottom: .4rem;
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/6.jpg') }}" alt="Arkaplan İşleri" />
            <span class="card-title">Arkaplan İşleri</span>
        </div>
        <div class="card-content yellow lighten-4">
            Sunucu üzerinde çalışan sisteme özgü işlemleri dinamik olarak izleyebilirsiniz.
        </div>
        <ul
            id="console"
            class="collection black load d-flex align-items-end flex-wrap"
            data-href="{{ route('admin.monitoring.background') }}"
            data-callback="__log"
            data-include="cmd"
            data-method="post">
            <li class="collection-item d-none" style="width: 100%;">
                <span style="padding: .2rem;" class="orange-text" data-name="time"></span>
                <span style="padding: .2rem;" class="red-text" data-name="pid"></span>
                <span style="padding: .2rem;" class="green-text" data-name="cmd"></span>
            </li>
        </ul>
    </div>
@endsection
