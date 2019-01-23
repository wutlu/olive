@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sistem İzleme'
        ],
        [
            'text' => '🐞 Kuyruk Ekranı (Laravel Horizon)'
        ]
    ]
])

@push('local.styles')
    iframe {
        border-width: 0;
        width: 100%;
        height: 600px;
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Kuyruk Ekranı (Laravel Horizon)" />
            <span class="card-title">Kuyruk Ekranı (Laravel Horizon)</span>
        </div>
    </div>
    <iframe class="card" src="{{ config('app.ssl') ? secure_url('horizon') : url('horizon') }}"></iframe>
@endsection
