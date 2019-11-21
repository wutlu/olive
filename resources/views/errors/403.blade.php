@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Yetkisiz Erişim!'
        ]
    ],
    'footer_hide' => true
])

@section('code', '403')

@section('content')
    <div class="olive-alert alert-left warning">
        <div class="anim"></div>
        <h4 class="mb-2">403</h4>
        <p class="mb-1">Üzgünüm, bu bölüme erişiminiz kısıtlanmış gibi görünüyor.</p>
        <p>- Bulunduğunuz organizasyonun süresi bitmiş olabilir.</p>
        <p>- Bulunduğunuz organizasyon bu alanı kapsamıyor olabilir.</p>
    </div>
@endsection
