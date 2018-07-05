@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@push('local.scripts')
$('select').formSelect();
@endpush

@section('content')
    <div class="step-title">
        <span class="step">4</span>
        <span class="text">İşlem Özeti</span>
    </div>

    @php
    print_r($plan);
    @endphp
@endsection
