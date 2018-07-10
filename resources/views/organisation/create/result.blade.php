@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Organizasyon Oluştur'
        ],
        [
            'text' => 'Bitti'
        ]
    ]
])

@section('content')
    <div class="step-title">
        <span class="step">3</span>
        <span class="text">Sipariş Tamamlandı</span>
    </div>

    <div class="card card-unstyled center-align">
        <div class="card-content">
            @if (session('created') == true)
            <i class="material-icons green-text large">check</i>
            <span class="card-title">Organizasyon Oluşturuldu</span>
            <p class="grey-text">Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.</p>
            <p class="grey-text">Sanal faturanız oluşturuldu. Ödemeniz gerçekleştikten sonra sanal faturanız, resmi fatura olarak güncellenecek, organizasyonun ödeme işlemi gerçekleştikten sonra aktif edilecektir.</p>
            @else
            <i class="material-icons red-text large">close</i>
            <span class="card-title">Bir şeyler ters gitti :(</span>
            <p class="grey-text">Beklenmedik bir sorun oluştu. Lütfen işleme <a href="{{ route('organisation.create.select') }}" class="btn-flat waves-effect">Tekrar</a> başlamayı deneyin.</p>
            <p class="grey-text">Sorun devam ediyorsa lütfen bizimle iletişime geçin.</p>
            @endif
        </div>
    </div>
@endsection
