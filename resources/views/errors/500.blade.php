@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Bir Şeyler Ters Gitti'
        ]
    ],
    'footer_hide' => true
])

@section('code', '500')

@section('content')
    <div class="olive-alert alert-left info">
        <div class="anim"></div>
        <h4 class="mb-2">İç Sunucu Hatası</h4>
        <h5>Sunucu tarafında beklenmedik bir hata medyada geldi.</h5>
        <p>Sizi böyle bir sayfa ile karşılaştırdığımız için çok üzgünüz.</p>
        <p>Biz bu hatayı düzelte duralım, siz de farklı bir bölümde çalışmalarınıza devam edin.</p>
    </div>
    <div class="tap-target green white-text" data-target="search-trigger">
        <div class="tap-target-content">
            <h5>Farklı bir şeyler?</h5>
        </div>
    </div>
@endsection

@push('local.scripts')
    $('[data-target=search-trigger]').tapTarget()
    $('[data-target=search-trigger]').tapTarget('open')
@endpush
