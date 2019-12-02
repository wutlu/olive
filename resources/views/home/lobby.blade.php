@extends('layouts.app', [
    'title' => [
        'text' => 'Veri Zone Teknoloji'
    ],
    'description' => 'Veri Zone Teknoloji, Büyük Veri Takip ve Raporlama Merkezi!',
    'footer_hide' => true
])

@section('content')
    <div class="home-items d-flex">
        <a href="{{ route('home', [ 'type' => 'kisiler' ]) }}" class="item flex-fill" data-type="kisiler">
            <div class="cover"></div>
        </a>
        <a href="{{ route('home', [ 'type' => 'markalar' ]) }}" class="item flex-fill" data-type="markalar">
            <div class="cover"></div>
        </a>
        <a href="{{ route('home', [ 'type' => 'reklam-ajanslari' ]) }}" class="item flex-fill" data-type="reklam-ajanslari">
            <div class="cover"></div>
        </a>
    </div>
@endsection
