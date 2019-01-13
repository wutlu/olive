@extends('layouts.app')

@section('title', 'Kaynaklar')

@section('content')
    <header id="main">
        <div class="parallax-container">
            <div class="parallax indigo lighten-5">
                <img src="{{ asset('img/bg-2.svg') }}" alt="bg-1" />
            </div>

            <div class="container">
                <a href="{{ route('home') }}">
                    <img alt="Logo" src="{{ asset('img/olive-logo.svg') }}" style="max-width: 200px;" />
                </a>
                <div class="card card-unstyled">
                    <div class="card-content">
                        <span class="card-title mb-0">Kaynaklar</span>
                    </div>

                    <div class="card-content teal-text">
                        <p>
                            Tüm kaynaklar açık kaynak olup, yasal olarak kaynak belirtilerek kullanılmaktadır.
                            Diğer hukuksal bilgilere <a href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a> ve <a href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a> sayfalarından ulaşabilirsiniz.</p>
                        <p>Yerel veya farklı kaynak istekleriniz, <a href="{{ route('settings.support', 'source') }}">DESTEK</a> bölümünden bize bildirebilirsiniz.</p>
                    </div>

                    <ul class="tabs tabs-transparent teal mb-1">
                        <li class="tab">
                            <a href="#media" class="active">Basın</a>
                        </li>
                        <li class="tab">
                            <a href="#shopping">Alışveriş</a>
                        </li>
                        <li class="tab">
                            <a href="#social">Sosyal Medya</a>
                        </li>
                    </ul>
                    <div id="media" class="collection max-height">
                        @forelse ($media as $key => $m)
                            <a href="{{ $m->site }}" target="_blank" class="collection-item waves-effect waves-teal">
                                {{ ($key+1).' - '.$m->name }}
                                
                                @if ($m->status)
                                    <span class="badge green white-text">Aktif</span>
                                @else
                                    <span class="badge red white-text">Pasif</span>
                                @endif
                            </a>
                        @empty
                            <div class="collection-item">Şu an için aktif kaynak bulunmuyor.</div>
                        @endforelse
                    </div>
                    <div id="shopping" class="collection max-height" style="display: none;">
                        @forelse ($shopping as $key => $s)
                            <a href="{{ $s->site }}" target="_blank" class="collection-item waves-effect waves-teal">
                                {{ ($key+1).' - '.$s->name }}
                                
                                @if ($s->status)
                                    <span class="badge green white-text">Aktif</span>
                                @else
                                    <span class="badge red white-text">Pasif</span>
                                @endif
                            </a>
                        @empty
                            <div class="collection-item">Şu an için aktif kaynak bulunmuyor.</div>
                        @endforelse
                    </div>
                    <div id="social" class="collection max-height" style="display: none;">
                        <a href="https://twitter.com" target="_blank" class="collection-item waves-effect waves-teal">
                            Twitter

                            @if ($options['twitter.status'] == true)
                                <span class="badge green white-text">Aktif</span>
                            @else
                                <span class="badge red white-text">Pasif</span>
                            @endif
                        </a>
                        <a  href="https://www.youtube.com" target="_blank" class="collection-item waves-effect waves-teal">
                            YouTube

                            @if ($options['youtube.status'] == true)
                                <span class="badge green white-text">Aktif</span>
                            @else
                                <span class="badge red white-text">Pasif</span>
                            @endif
                        </a>
                        <a href="https://www.google.com" target="_blank" class="collection-item waves-effect waves-teal">
                            Google

                            @if ($options['google.status'] == true)
                                <span class="badge green white-text">Aktif</span>
                            @else
                                <span class="badge red white-text">Pasif</span>
                            @endif
                        </a>
                        @forelse ($sozluk as $s)
                            <a href="{{ $s->site }}" target="_blank" class="collection-item waves-effect waves-teal">
                                {{ $s->name }}
                                
                                @if ($s->status)
                                    <span class="badge green white-text">Aktif</span>
                                @else
                                    <span class="badge red white-text">Pasif</span>
                                @endif
                            </a>
                        @empty
                            <div class="collection-item">Şu an için aktif kaynak bulunmuyor.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.parallax').parallax()
        $('.tabs').tabs()
    })
@endpush
