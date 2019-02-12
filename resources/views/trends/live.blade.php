@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Canlı Trend'
        ]
    ],
    'dock' => true
])

@section('dock')
    @include('trends._menu', [ 'active' => 'trends' ])
@endsection

@section('content')
    <div class="rects">
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
        <a href="#">Merhaba</a>
    </div>
@endsection

@push('local.scripts')

@endpush
