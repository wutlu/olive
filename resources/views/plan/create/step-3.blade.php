@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
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
            <i class="material-icons green-text large">check</i>
            <span class="card-title">Organizasyon Oluşturuldu</span>
            <p class="grey-text">Ödeme bilgileri ve diğer detaylar e-posta adresinize gönderildi.</p>
            <p class="grey-text">Organizasyonunuz ödeme işlemi gerçekleştikten sonra aktif hale gelecektir.</p>
        </div>
    </div>
@endsection
