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
    <div class="card">
        <div class="card-content teal">
            <span class="card-title mb-0 white-text">Trend Arşivi</span>
        </div>
        <nav class="grey darken-4">
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
                <div class="collapsible-header">
                    <span class="rank grey-text align-self-center">2019</span>
                    <span class="align-self-center">2019 Yılı Web Trendleri</span>
                    <span class="badge grey lighten-4 grey-text align-self-center">2018-12-12 14:02:00</span>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            <li>
                <div class="collapsible-header">
                    <span class="rank grey-text align-self-center">2019</span>
                    <span class="align-self-center">Ocak Ayı Web Trendleri</span>
                    <span class="badge grey lighten-4 grey-text align-self-center">2018-12-12 14:02:00</span>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            <li>
                <div class="collapsible-header">
                    <span class="rank grey-text align-self-center">2019</span>
                    <span class="align-self-center">Ocak Ayı 1. Hafta, Haftalık Web Trendleri</span>
                    <span class="badge grey lighten-4 grey-text align-self-center">2018-12-12 14:02:00</span>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            <li>
                <div class="collapsible-header">
                    <span class="rank grey-text align-self-center">2019</span>
                    <span class="align-self-center">1 Ocak 2019 Günlük Web Trendleri</span>
                    <span class="badge grey lighten-4 grey-text align-self-center">2018-12-12 14:02:00</span>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
        </ul>
    </div>
@endsection
