@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Trend Arşivi'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    $('.collapsible').collapsible()
@endpush

@section('dock')
    @include('trends._menu', [ 'active' => 'archive' ])
@endsection

@section('header.title', 'Test')

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Trend Arşivi</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#crawlers"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <ul class="collapsible with-header">
            <li>
                <div class="collapsible-header d-flex justify-content-between">
                    <div>
                        <span class="d-block">Yıllık Web Trendleri: 2019</span>
                        <span class="grey-text">2018-12-12 14:02:00</span>
                    </div>
                    <i class="material-icons arrow">keyboard_arrow_down</i>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            <li>
                <div class="collapsible-header d-flex justify-content-between">
                    <div>
                        <span class="d-block">Web Trendleri: Ocak</span>
                        <span class="grey-text">2018-12-12 14:02:00</span>
                    </div>
                    <i class="material-icons arrow">keyboard_arrow_down</i>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            <li>
                <div class="collapsible-header d-flex justify-content-between">
                    <div>
                        <span class="d-block">Haftalık Web Trendleri: Ocak Ayı 1. Hafta</span>
                        <span class="grey-text">2018-12-12 14:02:00</span>
                    </div>
                    <i class="material-icons arrow">keyboard_arrow_down</i>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            <li>
                <div class="collapsible-header d-flex justify-content-between">
                    <div>
                        <span class="d-block">Web Trendleri: 01 Ocak 2019</span>
                        <span class="grey-text">2018-12-12 14:02:00</span>
                    </div>
                    <i class="material-icons arrow">keyboard_arrow_down</i>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
        </ul>
    </div>
@endsection
