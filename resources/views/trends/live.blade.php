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

@push('local.scripts')
    $('.tabs').tabs()
    $('.collapsible').collapsible()
@endpush

@section('dock')
    @include('trends._menu', [ 'active' => 'trends' ])
@endsection

@section('content')
    <div class="card teal">
        <div class="card-content white-text">
            <p>Trend analizleri Olive tarafından yapılır. Asıl kaynakların trendleriyle uyum sağlaması beklenemez.</p>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width tabs-transparent">
                <li class="tab">
                    <a href="#olive" class="waves-effect waves-light active">Olive</a>
                </li>
                <li class="tab">
                    <a href="#youtube" class="waves-effect waves-light">YouTube</a>
                </li>
                <li class="tab">
                    <a href="#twitter" class="waves-effect waves-light">Twitter</a>
                </li>
                <li class="tab">
                    <a href="#media" class="waves-effect waves-light">Medya</a>
                </li>
                <li class="tab">
                    <a href="#sozluk" class="waves-effect waves-light">Sözlük</a>
                </li>
                <li class="tab">
                    <a href="#shopping" class="waves-effect waves-light">Alışveriş</a>
                </li>
            </ul>
        </div>

        <ul class="collapsible white" id="olive">
            <li>
                <div class="card-panel orange lighten-4 mt-0 mb-0">Tüm veriler üzerinden yapılan trend analizi sonuçları.</div>
            </li>
            @for ($i = 1; $i <= 10; $i++)
            <li>
                <div class="collapsible-header">
                    <span class="rank grey-text align-self-center">{{ $i }}</span>
                    <span class="align-self-center">test</span>
                    <span class="badge grey lighten-4 grey-text align-self-center">14000+</span>
                </div>
                <div class="collapsible-body grey lighten-4">
                    <span>test</span>
                </div>
            </li>
            @endfor
        </ul>

        <ul class="collapsible white" id="youtube">
            <li>
            @component('components.loader')
                @slot('color', 'cyan')
                @slot('id', 'home-loader')
            @endcomponent
            </li>
        </ul>

        <ul class="collapsible white" id="twitter">
            <li>
            @component('components.loader')
                @slot('color', 'cyan')
                @slot('id', 'home-loader')
            @endcomponent
            </li>
        </ul>

        <ul class="collapsible white" id="media">
            <li>
            @component('components.loader')
                @slot('color', 'cyan')
                @slot('id', 'home-loader')
            @endcomponent
            </li>
        </ul>

        <ul class="collapsible white" id="sozluk">
            <li>
            @component('components.loader')
                @slot('color', 'cyan')
                @slot('id', 'home-loader')
            @endcomponent
            </li>
        </ul>

        <ul class="collapsible white" id="shopping">
            <li>
            @component('components.loader')
                @slot('color', 'cyan')
                @slot('id', 'home-loader')
            @endcomponent
            </li>
        </ul>
    </div>
@endsection
