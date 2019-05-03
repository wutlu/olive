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
    @if (session('success'))
        <div class="center-align p-2">
            @if (session('success') == 'ok')
                <i class="material-icons large green-text">check</i>
                <p class="green-text">ÖDEME İŞLEMİ BAŞARILI BİR ŞEKİLDE GERÇEKLEŞTİRİLDİ</p>
                <p class="orange-text">FATURANIZIN ONAYLANMASI BİRKAÇ DAKİKAYI BULABİLİR</p>
            @elseif (session('success') == 'fail')
                <i class="material-icons large red-text">close</i>
                <p class="red-text">ÖDEME İŞLEMİ BAŞARISIZ OLDU</p>
            @endif
        </div>
    @else
        @isset($reason)
            <div class="center-align p-2">
                @if ($reason == 'clean')
                    @component('components.nothing')
                        @slot('text', 'ÖDENECEK FATURANIZ BULUNMAMAKTADIR, TEŞEKKÜR EDERİZ')
                    @endcomponent
                @elseif ($reason == 'done')
                    <i class="material-icons large orange-text">history</i>
                    <p class="orange-text">ÖDEMENİZ GERÇEKLEŞTİ</p>
                    <p class="orange-text">FATURANIZ BİRKAÇ DAKİKA İÇERİSİNDE ONAYLANACAKTIR</p>
                @else
                    <i class="material-icons large red-text">close</i>
                    <p class="red-text">{{ $reason }}</p>
                @endif
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
