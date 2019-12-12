@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => session('status') == 'success' ? 'Başarılı' : 'Uyarı'
        ]
    ],
    'footer_hide' => true
])

@section('content')
    <div class="olive-alert {{ session('status') == 'success' ? 'success' : 'warning' }}">
        <div class="anim"></div>
        <h4 class="mb-1">{{ session('status') == 'success' ? 'Başarılı' : 'Uyarı' }}</h4>
        @if (session('alert'))
            <p class="mb-1">{!! session('alert') !!}</p>
        @endif
        <a href="{{ route('dashboard') }}" class="btn-flat waves-effect">Ana Sayfa</a>
    </div>
@endsection
