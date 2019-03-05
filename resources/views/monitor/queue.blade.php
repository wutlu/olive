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

    .card.with-bg {
        line-height: 0;
    }
@endpush

@section('content')
    <div class="card with-bg">
        <iframe src="{{ url('horizon') }}"></iframe>
    </div>
@endsection
