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
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Veri Havuzu
            </span>
        </div>
        <ul class="collection">
            <li class="collection-item">Sizi yakından ilgilendiren kriterleri belirterek veri toplama sonrasında yüksek analiz sonuçları elde edebilirsiniz.</li>
            <li class="collection-item">Veri toplayıcı örümceklerimiz; ilgilendiğiniz alanlara yoğunlaşarak, kaynak odağıyla çalışırlar.</li>
            <li class="collection-item">Bu ayarlar bulunduğunuz organizasyon için geçerlidir.</li>
            <li class="collection-item">Organizasyonunuza dahil tüm kullanıcıların takip havuzları ortaktır.</li>
            <li class="collection-item">Elde edilen veriler tüm Olive kullanıcıları tarafından ortak veritabanından analize açık hale getirilir.</li>
        </ul>
    </div>
@endsection

@section('dock')
	@include('dataPool._menu', [ 'active' => 'dashboard' ])
@endsection
