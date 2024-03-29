@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Bot Yönetimi'
        ]
    ],
    'footer_hide' => true
])

@section('content')
    <a href="{{ route('crawlers.media.list') }}" class="d-block card-panel hoverable" data-tooltip="Medya Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Medya Botları</span>
    </a>
    <a href="{{ route('crawlers.sozluk.list') }}" class="d-block card-panel hoverable" data-tooltip="Sözlük Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Sözlük Botları</span>
    </a>
    <a href="{{ route('crawlers.shopping.list') }}" class="d-block card-panel hoverable" data-tooltip="E-ticaret Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">E-ticaret Botları</span>
    </a>
    <a href="{{ route('crawlers.blog.list') }}" class="d-block card-panel hoverable" data-tooltip="Blog & Forum Botları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Blog & Forum Botları</span>
    </a>

    <a href="{{ route('admin.twitter.settings') }}" class="d-block card-panel hoverable" data-tooltip="Twitter Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Twitter Ayarları</span>
    </a>
    <a href="{{ route('admin.youtube.settings') }}" class="d-block card-panel hoverable" data-tooltip="YouTube Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">YouTube Ayarları</span>
    </a>
    <a href="#" class="d-block card-panel hoverable" style="opacity: .4;" data-tooltip="Facebook Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Facebook Ayarları</span>
    </a>
    <a href="{{ route('admin.instagram.settings') }}" class="d-block card-panel hoverable" data-tooltip="Instagram Ayarları" data-position="right">
        <i class="material-icons">widgets</i>
        <span class="d-block">Instagram Ayarları</span>
    </a>
@endsection
