@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veriler Güncelleniyor...'
        ]
    ],
    'footer_hide' => true
])

@section('content')
    <div class="center-align">
        <div
            class="loader-table load loading"
            data-href="{{ route('instagram.user.sync') }}"
            data-method="post"
            data-id="{{ $document['_source']['user']['id'] }}"
            data-callback="__sync">
            <div class="d-flex">
                <img class="align-self-center preloader" alt="Yükleniyor..." src="{{ asset('img/preloader.svg') }}" />
                <img class="align-self-center instagram" alt="Instagram" src="{{ asset('img/logos/instagram.svg') }}" />
                <span class="align-self-center timer">~</span>
            </div>
        </div>
        <p class="text-line">Kullanıcı verileri Instagram üzerinden güncelleniyor...</p>
        <p class="text-line">Lütfen bekleyin...</p>
    </div>
@endsection

@push('local.scripts')
    function __sync(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('.timer').data('counter', obj.wait)
            $('.timer').data('wait', obj.wait)

            __timer()
        }
    }

    var _timer;

    function __timer()
    {
        var dom = $('.timer');

        window.clearTimeout(_timer)

        _timer = window.setTimeout(function() {
            var counter = dom.data('counter');
            var wait = dom.data('wait');

            dom.data('counter', counter-0.1).html(parseInt(100-(100/wait*counter)) + '%')

            if (dom.data('counter') >= 0)
            {
                __timer()
            }
            else
            {
                location.reload()
            }
        }, 100)
    }
@endpush

@push('local.styles')
    .loader-table {
        display: table;
        margin: 64px auto 32px;
    }
    .instagram {
        width: 48px;
        height: 48px;
        display: table;
    }
    .preloader {
        width: 160px;
        height: 20px;
        margin: 0 -32px 0 0;
        position: relative;
    }
    p.text-line {
        margin: 0;
    }
    .timer {
        display: table;
        width: 48px;
        height: 48px;
        line-height: 48px;
        border-radius: 50%;
        margin: 0 0 0 1rem;
        position: relative;
        font-size: 20px;
    }
@endpush
