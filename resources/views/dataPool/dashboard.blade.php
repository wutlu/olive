@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title">Veri Havuzu</span>
        </div>
        <div class="card-content">
            <p class="grey-text text-darken-2">- Sizi yakından ilgilendiren kriterleri belirterek veri toplama sonasında yüksek analiz sonuçları elde edebilirsiniz.</p>
            <p class="grey-text text-darken-2">- Veri toplayıcı örümceklerimiz; ilgilendiğiniz alanlara yoğunlaşarak, veri kaçırmama odağıyla çalışırlar.</p>
            <p class="grey-text text-darken-2">- Bu ayarlar bulunduğunuz organizasyon için geçerlidir.</p>
            <p class="grey-text text-darken-2">- Organizasyona dahil tüm kullanıcıların takip havuzları ortaktır.</p>
            <p class="grey-text text-darken-2">- Elde edilen veriler tüm veri.zone kullanıcıları tarafından ortak veritabanından analize açık halde olacaktır.</p>
        </div>
    </div>
@endsection

@section('dock')
	@include('dataPool._menu', [ 'active' => 'dashboard' ])
@endsection
