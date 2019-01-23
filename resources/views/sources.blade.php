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
                        <p>Yerel veya farklı kaynak istekleriniz, <a href="{{ route('settings.support', 'kaynak-istegi') }}">DESTEK</a> bölümünden bize bildirebilirsiniz.</p>
                    </div>

                    <ul class="tabs tabs-transparent teal mb-1">
                        <li class="tab">
                            <a href="#media" class="active">Basın</a>
                        </li>
                        <li class="tab">
                            <a href="#shopping">E-Ticaret</a>
                        </li>
                        <li class="tab">
                            <a href="#social">Sosyal Medya</a>
                        </li>
                    </ul>
                    <ul id="media" class="collection max-height">
                        @forelse ($media as $key => $m)
                            <li class="collection-item">
                                <a href="{{ $m->site }}" target="_blank">
                                    {{ ($key+1).' - '.$m->name }}
                                    
                                    @if ($m->status)
                                        <span class="badge green white-text">Aktif</span>
                                    @else
                                        <span class="badge red white-text">Pasif</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="collection-item">Şu an için aktif kaynak bulunmuyor.</li>
                        @endforelse
                    </ul>
                    <ul id="shopping" class="collection max-height" style="display: none;">
                        @forelse ($shopping as $key => $s)
                            <li class="collection-item">
                                <a href="{{ $s->site }}" target="_blank">
                                    {{ ($key+1).' - '.$s->name }}
                                    
                                    @if ($s->status)
                                        <span class="badge green white-text">Aktif</span>
                                    @else
                                        <span class="badge red white-text">Pasif</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="collection-item">Şu an için aktif kaynak bulunmuyor.</li>
                        @endforelse
                    </ul>
                    <ul id="social" class="collection max-height" style="display: none;">
                        <li class="collection-item">
                            <a href="https://twitter.com" target="_blank">
                                Twitter

                                @if ($options['twitter.status'] == 'on')
                                    <span class="badge green white-text">Aktif</span>
                                @else
                                    <span class="badge red white-text">Pasif</span>
                                @endif
                            </a>
                        </li>
                        <li class="collection-item">
                            <a  href="https://www.youtube.com" target="_blank">
                                YouTube

                                @if ($options['youtube.status'] == 'on')
                                    <span class="badge green white-text">Aktif</span>
                                @else
                                    <span class="badge red white-text">Pasif</span>
                                @endif
                            </a>
                        </li>
                        <li class="collection-item">
                            <a href="https://www.google.com" target="_blank">
                                Google

                                @if ($options['google.status'] == 'on')
                                    <span class="badge green white-text">Aktif</span>
                                @else
                                    <span class="badge red white-text">Pasif</span>
                                @endif
                            </a>
                        </li>
                        @forelse ($sozluk as $s)
                        <li class="collection-item">
                                <a href="{{ $s->site }}" target="_blank">
                                    {{ $s->name }}
                                    
                                    @if ($s->status)
                                        <span class="badge green white-text">Aktif</span>
                                    @else
                                        <span class="badge red white-text">Pasif</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="collection-item">Şu an için aktif kaynak bulunmuyor.</li>
                        @endforelse
                    </ul>
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
