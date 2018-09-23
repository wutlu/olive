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
            'text' => 'Kuyruk Ekranı (Horizon)'
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
            <img src="{{ asset('img/md-s/6.jpg') }}" alt="Kuyruk Ekranı" />
            <span class="card-title">Kuyruk Ekranı (Horizon)</span>
        </div>
    </div>
    <iframe class="card" src="{{ url('horizon') }}"></iframe>
@endsection
