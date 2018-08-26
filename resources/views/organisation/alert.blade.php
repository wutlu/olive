@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Uyarı'
        ]
    ]
])

@section('content')
    <div class="card card-unstyled">
        <div class="card-content">
            @if (session('alert'))
            <i class="material-icons large">warning</i>
            <p>{{ session('alert') }}</p>
            @endif
        </div>
    </div>
@endsection
