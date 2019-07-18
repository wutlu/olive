@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu'
        ]
    ],
    'footer_hide' => true
])

@push('local.styles')
    [data-name=menu] > .item > a {
        display: table;
        margin: 0 auto;
    }
    [data-name=menu] > .item > a > img {
        width: 64px;
    }
@endpush

@section('content')
    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Veri Havuzu
            </span>
        </div>
        <div data-name="menu" class="d-flex flex-wrap justify-content-start">
            <div class="item flex-fill">
                <a href="#">
                    <img alt="YouTube" src="{{ asset('img/logos/youtube.svg') }}" />
                </a>
                <div class="collection center-align">
                    <a class="collection-item waves-effect" href="{{ route('youtube.channel.list') }}">Kanal Havuzu</a>
                    <a class="collection-item waves-effect" href="{{ route('youtube.video.list') }}">Video Havuzu</a>
                    <a class="collection-item waves-effect" href="{{ route('youtube.keyword.list') }}">Kelime Havuzu</a>
                </div>
            </div>
            <div class="item flex-fill">
                <a href="#">
                    <img alt="Twitter" src="{{ asset('img/logos/twitter.svg') }}" />
                </a>
                <div class="collection center-align">
                    <a class="collection-item waves-effect" href="{{ route('twitter.keyword.list') }}">Kelime Havuzu</a>
                    <a class="collection-item waves-effect" href="{{ route('twitter.account.list') }}">Kullanıcı Havuzu</a>
                </div>
            </div>
            <div class="item flex-fill">
                <a href="#">
                    <img alt="Instagram" src="{{ asset('img/logos/instagram.svg') }}" />
                </a>
                <div class="collection center-align">
                    <a class="collection-item waves-effect" href="{{ route('instagram.url.list') }}">Bağlantı Havuzu</a>
                </div>
            </div>
        </div>
        <ul class="collection collection-unstyled">
            <li class="collection-item">Olive ekosistemi, ortak veri havuzu prensibiyle çalışır.</li>
            <li class="collection-item">Olive organizasyonlarının ve Olive ekibinin belirlediği kriterlere göre, sosyal medya verileri analiz edilmek üzere toplanır.</li>
            <li class="collection-item">Elde edilen veriler; anlık veri sorgulama motorları ile, kullanıcı ekranlarına sunulur.</li>
            <li class="collection-item">Havuz kriterlerini, organizasyonunuza ait limitler doğrultusunda kullanabilirsiniz.</li>
        </ul>
    </div>
@endsection
