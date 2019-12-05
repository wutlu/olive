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
        <div class="card red">
            <div class="card-content">
                <div class="white-text">
                    @component('components.alert')
                        @slot('icon', 'warning')
                        @slot('text', '!! BU BÖLÜM BİR ARAÇ DEĞİLDİR !!<br />
                            Bu bölüm sadece veri kaynaklarını beslemek için gerekli olan ekstra kriterleri belirtmeniz için vardır.<br />
                            Bazen bazı veriler gözümüzden kaçabiliyor. Bu durumun önüne geçebilmek için bize kriter belirtmenizi istiyoruz.')
                    @endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection
