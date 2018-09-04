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
            'text' => 'İzleme Ekranı'
        ]
    ]
])

@push('local.styles')
    img.icon {
        width: 32px;
        height: 32px;
        position: relative;
        top: -10px;
        left: -10px;
    }
@endpush

@section('content')
    <div class="card-deck">
        <div class="card grey darken-4">
            <div class="card-content">
                <span class="card-title">
                    <img class="icon" alt="CPU" src="{{ asset('img/cpu.png') }}" /> CPU
                </span>
            </div>
        </div>

        <div class="card grey darken-4">
            <div class="card-content">
                <span class="card-title">
                    <img class="icon" alt="HDD" src="{{ asset('img/hdd.png') }}" /> HDD
                </span>
            </div>
        </div>

        <div class="card grey darken-4">
            <div class="card-content">
                <span class="card-title">
                    <img class="icon" alt="RAM" src="{{ asset('img/ram-memory.png') }}" /> RAM
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-content">test</div>
        </div>
    </div>
@endsection
