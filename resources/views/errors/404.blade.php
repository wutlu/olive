@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Sayfa Bulunamadı!'
        ]
    ],
    'footer_hide' => true
])

@section('code', '404')

@section('content')
    <div class="olive-alert alert-left warning">
        <div class="anim"></div>
        <h4 class="mb-2">404</h4>
        <p>Üzgünüm, aradığınız sayfa/içerik bulunamadı.</p>
        <p>Aradığınız sayfayı/içeriği bulmak için aramayı kullanabilirsiniz.</p>
    </div>
    @auth
        <div class="tap-target green white-text" data-target="search-trigger">
            <div class="tap-target-content">
                <h5>Ne Aramıştınız?</h5>
                <p>Aradığınız sayfayı/içeriği bulmak için aramayı kullanabilirsiniz.</p>
            </div>
        </div>
    @endauth
@endsection

@auth
    @push('local.scripts')
        $('[data-target=search-trigger]').tapTarget()
        $('[data-target=search-trigger]').tapTarget('open')
    @endpush
@endauth
