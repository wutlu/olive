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
    @if (session('success') || session('failed'))
        <div class="center-align p-2">
            @if (session('success') == 'ok')
                <i class="material-icons large green-text">check</i>
                <p class="green-text">ÖDEME BAŞARILI BİR ŞEKİLDE GERÇEKLEŞTİRİLDİ</p>
            @elseif (session('success') == 'fail')
                <i class="material-icons large red-text">close</i>
                <p class="red-text">ÖDEME BAŞARISIZ OLDU</p>
            @endif
        </div>
    @else
        @isset($reason)
            <div class="center-alignp-2">
                <i class="material-icons large red-text">close</i>
                <p class="red-text">{{ $reason }}</p>
            </div>
        @else
            @push('external.include.footer')
                <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                <script>iFrameResize({}, '#paytriframe');</script>
            @endpush

            <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
        @endisset
    @endif
@endsection
