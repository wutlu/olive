@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Bot Yönetimi'
        ]
    ]
])

@section('content')
    <a href="{{ route('crawlers.media.list') }}" class="d-block card-panel hoverable waves-effect" data-tooltip="Medya Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Medya Botları</span>
    </a>
    <a href="{{ route('crawlers.sozluk.list') }}" class="d-block card-panel hoverable waves-effect" data-tooltip="Sözlük Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Sözlük Botları</span>
    </a>
    <a href="{{ route('crawlers.shopping.list') }}" class="d-block card-panel hoverable waves-effect" data-tooltip="E-ticaret Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">E-ticaret Botları</span>
    </a>
    <a href="{{ route('admin.twitter.settings') }}" class="d-block card-panel hoverable waves-effect" data-tooltip="Twitter Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Twitter Ayarları</span>
    </a>
    <a href="{{ route('admin.youtube.settings') }}" class="d-block card-panel hoverable waves-effect" data-tooltip="YouTube Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">YouTube Ayarları</span>
    </a>
    <a href="{{ route('admin.google.settings') }}" class="d-block card-panel hoverable waves-effect" data-tooltip="Google Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Google Ayarları</span>
    </a>
@endsection
