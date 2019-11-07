@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Yetki Kısıtlaması!'
        ]
    ],
    'footer_hide' => true
])

@section('code', '401')

@section('content')
    <div class="olive-alert alert-left warning">
        <div class="anim"></div>
        <h4 class="mb-2">401</h4>
        <p class="mb-1">Üzgünüm, bu bölüme erişiminiz kısıtlanmış gibi görünüyor.</p>
        <p>- Bulunduğunuz organizasyonun süresi bitmiş olabilir.</p>
        <p>- Bulunduğunuz organizasyon bu alanı kapsamıyor olabilir.</p>
        <p>- Olive yönetimiyle ilgili bir alana girmeyi deniyor olabilirsiniz.</p>
    </div>
@endsection
