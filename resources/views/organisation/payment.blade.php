@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Organizasyon Ayarları',
            'link' => route('settings.organisation')
        ],
        [
            'text' => 'Ödeme',
        ]
    ]
])

@section('content')
    @isset($reason)
        <div class="center-align red-text p-2">
            <i class="material-icons large">close</i>
            <p>{{ $reason }}</p>
        </div>
    @else
        <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
    @endisset
@endsection

@push('external.include.footer')
    @isset($token)
        <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
        <script>iFrameResize({}, '#paytriframe');</script>
    @endisset
@endpush

@push('local.scripts')
    @if (session('error'))
        M.toast({ html: '{{ session('error') }}', classes: 'green darken-2' })
    @endif

    @if (session('success'))
        M.toast({ html: '{{ session('success') }}', classes: 'green darken-2' })
    @endif
@endpush
