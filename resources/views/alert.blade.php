@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Uyarı'
        ]
    ],
    'footer_hide' => true
])

@section('content')
    <div class="olive-alert warning">
        <div class="anim"></div>
        <h4 class="mb-1">Uyarı</h4>
        @if (session('alert'))
            <p class="mb-1">{{ session('alert') }}</p>
        @endif
        <a href="{{ route('dashboard') }}" class="btn-flat waves-effect">Ana Sayfa</a>
    </div>
@endsection
